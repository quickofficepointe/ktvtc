<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseUnit;
use App\Models\User;
use App\Services\Academics\EvaluationService;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Academics office: every trainer (instructor), their units and progress; add trainer
 * accounts, assign units, and text login details.
 */
class AcademicTrainersController extends Controller
{
    public function index(Request $request)
    {
        $trainers = User::where('role', User::ROLE_TRAINERS)
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%{$request->q}%")
                ->orWhere('email', 'like', "%{$request->q}%")->orWhere('phone_number', 'like', "%{$request->q}%")))
            ->orderByRaw('COALESCE(is_active, 1) DESC')->orderBy('name')->get();

        $units = DB::table('course_units')->where('is_active', true)->whereNotNull('trainer_id')
            ->selectRaw('trainer_id, COUNT(*) as n')->groupBy('trainer_id')->pluck('n', 'trainer_id');
        $marks = DB::table('unit_results')->join('course_units', 'course_units.id', '=', 'unit_results.course_unit_id')
            ->selectRaw("course_units.trainer_id, SUM(unit_results.status = 'draft') as drafts, SUM(unit_results.status = 'submitted') as submitted, SUM(unit_results.status = 'published') as published")
            ->groupBy('course_units.trainer_id')->get()->keyBy('trainer_id');

        $ratings = [];
        foreach ($trainers as $t) {
            $responses = DB::table('trainer_evaluations')->where('trainer_id', $t->id)->count();
            $ratings[$t->id] = $responses >= EvaluationService::MIN_RESPONSES
                ? round((float) DB::table('trainer_evaluation_answers')->join('trainer_evaluations', 'trainer_evaluations.id', '=', 'trainer_evaluation_answers.trainer_evaluation_id')
                    ->where('trainer_evaluations.trainer_id', $t->id)->whereNotNull('trainer_evaluation_answers.rating')->avg('trainer_evaluation_answers.rating'), 1)
                : null;
        }

        $unassigned = CourseUnit::where('is_active', true)->whereNull('trainer_id')->count();

        return view('ktvtc.academics.trainers.index', compact('trainers', 'units', 'marks', 'ratings', 'unassigned'));
    }

    public function show(User $trainer)
    {
        abort_unless((int) $trainer->role === User::ROLE_TRAINERS, 404);

        $courses = Course::whereHas('units', fn ($q) => $q->where('is_active', true))
            ->with(['units' => fn ($q) => $q->where('is_active', true)->with('trainer:id,name')->orderBy('term_number')->orderBy('sort_order')])
            ->orderBy('name')->get();
        $mine = $courses->flatMap->units->where('trainer_id', $trainer->id);

        return view('ktvtc.academics.trainers.show', compact('trainer', 'courses', 'mine'));
    }

    /** Add a trainer account and text the login details. */
    public function store(Request $request, SmsService $sms)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone_number' => 'required|string|max:20',
            'campus_id' => 'nullable|exists:campuses,id',
        ]);

        $password = $this->newPassword();
        $trainer = User::create(collect($data)->except('campus_id')->all() + [
            'password' => Hash::make($password),
            'role' => User::ROLE_TRAINERS,
            'is_active' => true,
            'is_approved' => true,
            'bio' => 'Trainer',
        ]);
        // campus_id is not mass-assignable on users
        $trainer->forceFill(['campus_id' => $data['campus_id'] ?? null])->save();
        $trainer->markEmailAsVerified();

        $texted = $this->textLogin($sms, $trainer, $password);

        return redirect()->route('academics.trainers.show', $trainer)->with('success', "{$trainer->name} added. "
            . ($texted ? "Login details texted to {$trainer->phone_number}." : "The SMS failed: give them this password yourself: {$password}"));
    }

    public function update(Request $request, User $trainer)
    {
        abort_unless((int) $trainer->role === User::ROLE_TRAINERS, 404);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($trainer->id)],
            'phone_number' => 'nullable|string|max:20',
            'is_active' => 'required|boolean',
        ]);
        $trainer->update($data);

        $message = 'Trainer updated.';
        if (!$data['is_active'] && ($n = CourseUnit::where('trainer_id', $trainer->id)->where('is_active', true)->count())) {
            $message .= " They still teach {$n} unit(s): assign those to someone else.";
        }

        return back()->with('success', $message);
    }

    /** The ticked units are this trainer's; units unticked here lose this trainer. */
    public function assignUnits(Request $request, User $trainer)
    {
        abort_unless((int) $trainer->role === User::ROLE_TRAINERS, 404);
        $ids = array_map('intval', (array) $request->input('unit_ids', []));

        DB::transaction(function () use ($trainer, $ids) {
            CourseUnit::where('trainer_id', $trainer->id)->whereNotIn('id', $ids ?: [0])->update(['trainer_id' => null]);
            if ($ids) {
                CourseUnit::whereIn('id', $ids)->update(['trainer_id' => $trainer->id]);
            }
        });

        return back()->with('success', "{$trainer->name} now teaches " . count($ids) . ' unit(s).');
    }

    /** New password, texted to the trainer (they can change it in their profile). */
    public function sendLogin(User $trainer, SmsService $sms)
    {
        abort_unless((int) $trainer->role === User::ROLE_TRAINERS, 404);
        if (!$trainer->phone_number) {
            return back()->with('error', "{$trainer->name} has no phone number.");
        }

        $password = $this->newPassword();
        $trainer->forceFill(['password' => Hash::make($password)])->save();

        return $this->textLogin($sms, $trainer, $password)
            ? back()->with('success', "New login details texted to {$trainer->phone_number}.")
            : back()->with('error', "The SMS failed. Their new password is {$password}; give it to them yourself.");
    }

    private function newPassword(): string
    {
        return 'Ktvtc-' . Str::upper(Str::random(5));
    }

    private function textLogin(SmsService $sms, User $trainer, string $password): bool
    {
        if (!$trainer->phone_number) {
            return false;
        }
        $message = "Dear {$trainer->name}, your KTVTC trainer account is ready.\nLogin: " . url('/login')
            . "\nEmail: {$trainer->email}\nPassword: {$password}\nPlease change your password after signing in.";
        $result = rescue(fn () => $sms->sendSingleSms($trainer->phone_number, $message), ['success' => false], false);

        return (bool) ($result['success'] ?? false);
    }
}
