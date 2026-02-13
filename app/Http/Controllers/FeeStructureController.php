<?php

namespace App\Http\Controllers;

use App\Models\FeeStructure;
use App\Models\Course;
use App\Models\Campus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FeeStructureController extends Controller
{
    /* =========================================================
     | INDEX
     ========================================================= */
    public function index(Request $request)
    {
        $query = FeeStructure::with(['course', 'campus', 'approver'])
            ->latest();

        foreach ([
            'course_id', 'campus_id', 'academic_year', 'intake_month'
        ] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->$field);
            }
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('is_approved')) {
            $query->where('is_approved', filter_var($request->is_approved, FILTER_VALIDATE_BOOLEAN));
        }

        return view('ktvtc.admin.structures.index', [
            'feeStructures'       => $query->paginate(20),
            'courses'             => Course::orderBy('name')->get(),
            'campuses'            => Campus::orderBy('name')->get(),
            'academicYears'       => FeeStructure::distinct()->pluck('academic_year')->sort(),
            'intakeMonths'        => [
                'january','february','march','april','may','june',
                'july','august','september','october','november','december'
            ],
            'totalFeeStructures'  => FeeStructure::count(),
            'activeFeeStructures' => FeeStructure::where('is_active', true)->count(),
            'pendingApproval'     => FeeStructure::where('is_approved', false)->count(),
            'averageCourseFee'    => FeeStructure::where('is_active', true)->avg('total_course_fee') ?? 0,
            'averageDuration'     => round(FeeStructure::where('is_active', true)->avg('total_course_months') ?? 0, 1),
        ]);
    }

    /* =========================================================
     | STORE & UPDATE (SHARED LOGIC)
     ========================================================= */
    private function validatePayload(Request $request)
    {
        return Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'campus_id' => 'required|exists:campuses,id',
            'academic_year' => 'required|integer|min:2000|max:2100',
            'intake_month' => 'required|in:january,february,march,april,may,june,july,august,september,october,november,december',
            'total_course_months' => 'required|integer|min:1|max:48',
            'course_duration_type' => 'required|in:weeks,months,years',

            'registration_fee' => 'required|numeric|min:0',
            'tuition_per_month' => 'required|numeric|min:0',

            'valid_from' => 'required|date',
            'valid_to' => 'required|date|after:valid_from',

            'has_government_sponsorship' => 'boolean',
            'government_subsidy_amount' => 'nullable|numeric|min:0',

            'grace_period_days' => 'required|integer|min:0|max:30',
            'late_fee_percentage' => 'required|numeric|min:0|max:100',
            'suspension_days' => 'required|integer|min:0|max:90',
        ]);
    }

    public function store(Request $request)
    {
        $validator = $this->validatePayload($request);
        if ($validator->fails()) {
            return response()->json(['success'=>false,'errors'=>$validator->errors()], 422);
        }

        DB::transaction(function () use ($request, &$feeStructure) {
            $feeStructure = FeeStructure::create(
                array_merge(
                    $request->all(),
                    [
                        'is_active' => false,
                        'is_approved' => false,
                        'created_by' => Auth::id(),
                    ]
                )
            );

            $feeStructure->updateCalculatedFields();
        });

        return response()->json(['success'=>true,'data'=>$feeStructure]);
    }

    public function update(Request $request, FeeStructure $feeStructure)
    {
        if ($feeStructure->is_approved && !Auth::user()->can('approve-fee-structures')) {
            return response()->json(['success'=>false,'message'=>'Cannot edit approved fee structure'],403);
        }

        $validator = $this->validatePayload($request);
        if ($validator->fails()) {
            return response()->json(['success'=>false,'errors'=>$validator->errors()],422);
        }

        DB::transaction(function () use ($request, $feeStructure) {
            $feeStructure->update(
                array_merge(
                    $request->all(),
                    [
                        'updated_by' => Auth::id(),
                        'last_updated_at' => now(),
                    ]
                )
            );

            $feeStructure->updateCalculatedFields();
        });

        return response()->json(['success'=>true,'data'=>$feeStructure]);
    }

    /* =========================================================
     | ACTIVATE / DEACTIVATE / APPROVE / REJECT
     ========================================================= */
    public function activate(FeeStructure $feeStructure)
    {
        if (!$feeStructure->is_approved || now()->gt($feeStructure->valid_to)) {
            return response()->json(['success'=>false,'message'=>'Invalid activation'],403);
        }

        FeeStructure::where('course_id',$feeStructure->course_id)
            ->where('campus_id',$feeStructure->campus_id)
            ->update(['is_active'=>false]);

        $feeStructure->update(['is_active'=>true,'updated_by'=>Auth::id()]);

        return response()->json(['success'=>true]);
    }

    public function deactivate(FeeStructure $feeStructure)
    {
        $feeStructure->update(['is_active'=>false,'updated_by'=>Auth::id()]);
        return response()->json(['success'=>true]);
    }

    public function approve(FeeStructure $feeStructure)
    {
        abort_unless(Auth::user()->can('approve-fee-structures'),403);

        $feeStructure->update([
            'is_approved'=>true,
            'approved_by'=>Auth::id(),
            'approved_at'=>now(),
        ]);

        return response()->json(['success'=>true]);
    }

    public function reject(FeeStructure $feeStructure)
    {
        abort_unless(Auth::user()->can('approve-fee-structures'),403);

        $feeStructure->update([
            'is_approved'=>false,
            'is_active'=>false,
            'approved_by'=>null,
            'approved_at'=>null,
        ]);

        return response()->json(['success'=>true]);
    }

    /* =========================================================
     | BULK ACTIONS (PHP 8.2 SAFE)
     ========================================================= */
    public function bulkActions(Request $request)
    {
        $request->validate([
            'action'=>'required|in:activate,deactivate,approve,reject,delete',
            'ids'=>'required|array',
        ]);

        DB::beginTransaction();

        $processed = 0;
        $errors = [];

        foreach (FeeStructure::whereIn('id',$request->ids)->get() as $feeStructure) {
            try {
                switch ($request->action) {
                    case 'activate':
                        if (!$feeStructure->is_approved) {
                            $errors[] = "ID {$feeStructure->id} not approved";
                            continue 2;
                        }
                        $feeStructure->update(['is_active'=>true]);
                        break;

                    case 'delete':
                        if ($feeStructure->studentFees()->exists()) {
                            $errors[] = "ID {$feeStructure->id} has student fees";
                            continue 2;
                        }
                        $feeStructure->delete();
                        break;

                    case 'approve':
                        $feeStructure->update(['is_approved'=>true]);
                        break;

                    case 'reject':
                        $feeStructure->update(['is_approved'=>false,'is_active'=>false]);
                        break;

                    case 'deactivate':
                        $feeStructure->update(['is_active'=>false]);
                        break;
                }

                $processed++;

            } catch (\Throwable $e) {
                $errors[] = "ID {$feeStructure->id}: ".$e->getMessage();
            }
        }

        DB::commit();

        return response()->json([
            'success'=>true,
            'processed'=>$processed,
            'errors'=>$errors
        ]);
    }
}
