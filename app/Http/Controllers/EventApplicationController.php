<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventApplication;
use App\Models\EventApplicationAttendee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EventApplicationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $applications = EventApplication::with(['event', 'attendees'])
            ->latest()
            ->paginate(20);

        return view('ktvtc.website.events.eventapplication', compact('applications'));
    }
  public function success(Request $request)
    {
        $applicationId = $request->query('application');

        if ($applicationId) {
            $application = EventApplication::with(['event', 'attendees', 'latestKcbTransaction'])
                ->find($applicationId);

            return view('ktvtc.website.events.application-success', compact('application'));
        }

        return view('ktvtc.website.events.application-success');
    }
    /**
 * Get applications by event (for API)
 */
public function getByEvent(Event $event)
{
    $applications = EventApplication::with(['attendees'])
        ->where('event_id', $event->id)
        ->latest()
        ->paginate(20);

    return response()->json([
        'success' => true,
        'data' => $applications
    ]);
}


    /**
     * Show the form for creating a new resource.
     */
    public function create(Event $event)
    {
        // Check if registration is open
        if (!$this->isRegistrationOpen($event)) {
            return redirect()->back()->with('error', 'Registration for this event is currently closed.');
        }

        // Check if event has capacity
        if ($event->max_attendees && $event->registered_attendees >= $event->max_attendees) {
            return redirect()->back()->with('error', 'This event has reached maximum capacity.');
        }

        return view('ktvtc.website.events.eventapplication', compact('event'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Event $event)
    {
        // Validate registration availability
        if (!$this->isRegistrationOpen($event)) {
            return redirect()->back()->with('error', 'Registration for this event is currently closed.');
        }

        // Validate capacity
        if ($event->max_attendees && $event->registered_attendees >= $event->max_attendees) {
            return redirect()->back()->with('error', 'This event has reached maximum capacity.');
        }

        $validator = Validator::make($request->all(), [
            'parent_name' => 'required|string|max:255',
            'parent_contact' => 'required|string|max:20',
            'parent_email' => 'required|email|max:255',
            'mpesa_reference_number' => 'required|string|max:255|unique:event_applications,mpesa_reference_number',
            'number_of_people' => 'required|integer|min:1|max:10',
            'attendees' => 'required|array|size:' . $request->number_of_people,
            'attendees.*.name' => 'required|string|max:255',
            'attendees.*.school' => 'required|string|max:255',
            'attendees.*.age' => 'required|integer|min:3|max:25',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Calculate total amount
            $totalAmount = $this->calculateTotalAmount($event, $request->number_of_people);

            // Create the event application
            $application = EventApplication::create([
                'event_id' => $event->id,
                'parent_name' => $request->parent_name,
                'parent_contact' => $request->parent_contact,
                'parent_email' => $request->parent_email,
                'mpesa_reference_number' => $request->mpesa_reference_number,
                'number_of_people' => $request->number_of_people,
                'total_amount' => $totalAmount,
                'application_status' => 'pending',
            ]);

            // Create attendees
            foreach ($request->attendees as $attendeeData) {
                EventApplicationAttendee::create([
                    'event_application_id' => $application->id,
                    'name' => $attendeeData['name'],
                    'school' => $attendeeData['school'],
                    'age' => $attendeeData['age'],
                ]);
            }

            // Update event registered count
            $event->increment('registered_attendees', $request->number_of_people);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Application submitted successfully! We will review your application and get back to you.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'An error occurred while submitting your application. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(EventApplication $application)
    {
        $application->load(['event', 'attendees']);

        return view('event-applications.show', compact('application'));
    }
/**
 * Send event application confirmation to parent
 */
public function generateEventApplicationConfirmation($application, $event)
{
    $message = "Dear {$application->parent_name}, thank you for registering for {$event->title}. Your application for {$application->number_of_people} attendee(s) has been received. Reference: {$application->mpesa_reference_number}. We will review and confirm your registration within 24 hours. For inquiries, contact us. Thank you for choosing Kenswed.";

    return $message;
}

/**
 * Generate event status update message
 */
public function generateEventStatusUpdateMessage($application, $event, $newStatus)
{
    $message = "Dear {$application->parent_name}, ";

    if ($newStatus === 'confirmed') {
        $message .= "your registration for {$event->title} has been confirmed. Please arrive at {$event->venue} on {$event->start_date->format('l, F j, Y')} at {$event->start_date->format('g:i A')}. Bring a copy of this confirmation for entry.";
    } elseif ($newStatus === 'cancelled') {
        $message .= "your registration for {$event->title} has been cancelled. If you did not request this, please contact us immediately.";
    } elseif ($newStatus === 'completed') {
        $message .= "thank you for attending {$event->title}. We hope you had a wonderful experience.";
    } else {
        $message .= "your registration for {$event->title} is now {$newStatus}. We will update you once a final decision is made.";
    }

    $message .= " Reference: {$application->mpesa_reference_number}. Kenswed Technical College.";

    return $message;
}

/**
 * Generate event payment confirmation message
 */
public function generateEventPaymentConfirmationMessage($application, $event)
{
    $totalAttendees = $application->number_of_people;
    $totalAmount = $application->total_amount;

    $message = "Dear {$application->parent_name}, payment of KES " . number_format($totalAmount, 2) . " for {$event->title} ({$totalAttendees} attendee(s)) has been confirmed. Receipt: {$application->mpesa_reference_number}. Your registration is now complete. Thank you for choosing Kenswed Technical College.";

    return $message;
}

/**
 * Generate event reminder message
 */
public function generateEventReminderMessage($application, $event)
{
    $eventDate = $event->start_date->format('l, F j, Y');
    $eventTime = $event->start_date->format('g:i A');

    $message = "Dear {$application->parent_name}, reminder: {$event->title} is tomorrow, {$eventDate} at {$eventTime}. Venue: {$event->venue}. Please bring your confirmation and arrive 30 minutes early. Contact us if you need assistance. Kenswed Technical College.";

    return $message;
}
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EventApplication $application)
    {
        $application->load(['event', 'attendees']);
        $event = $application->event;

        return view('event-applications.edit', compact('application', 'event'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EventApplication $application)
    {
        $validator = Validator::make($request->all(), [
            'parent_name' => 'required|string|max:255',
            'parent_contact' => 'required|string|max:20',
            'parent_email' => 'required|email|max:255',
            'mpesa_reference_number' => 'required|string|max:255|unique:event_applications,mpesa_reference_number,' . $application->id,
            'number_of_people' => 'required|integer|min:1|max:10',
            'attendees' => 'required|array|size:' . $request->number_of_people,
            'attendees.*.name' => 'required|string|max:255',
            'attendees.*.school' => 'required|string|max:255',
            'attendees.*.age' => 'required|integer|min:3|max:25',
            'application_status' => 'required|in:pending,confirmed,cancelled,completed',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $oldAttendeeCount = $application->number_of_people;
            $newAttendeeCount = $request->number_of_people;

            // Update the event application
            $application->update([
                'parent_name' => $request->parent_name,
                'parent_contact' => $request->parent_contact,
                'parent_email' => $request->parent_email,
                'mpesa_reference_number' => $request->mpesa_reference_number,
                'number_of_people' => $newAttendeeCount,
                'application_status' => $request->application_status,
                'notes' => $request->notes,
            ]);

            // Delete existing attendees and create new ones
            $application->attendees()->delete();

            foreach ($request->attendees as $attendeeData) {
                EventApplicationAttendee::create([
                    'event_application_id' => $application->id,
                    'name' => $attendeeData['name'],
                    'school' => $attendeeData['school'],
                    'age' => $attendeeData['age'],
                ]);
            }

            // Update event registered count if attendee count changed
            if ($oldAttendeeCount !== $newAttendeeCount) {
                $event = $application->event;
                $difference = $newAttendeeCount - $oldAttendeeCount;
                $event->increment('registered_attendees', $difference);
            }

            DB::commit();

            return redirect()->route('event-applications.show', $application)
                ->with('success', 'Application updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'An error occurred while updating the application. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EventApplication $application)
    {
        try {
            DB::beginTransaction();

            $event = $application->event;
            $attendeeCount = $application->number_of_people;

            // Delete the application and attendees (cascade will handle attendees)
            $application->delete();

            // Update event registered count
            $event->decrement('registered_attendees', $attendeeCount);

            DB::commit();

            return redirect()->route('event-applications.index')
                ->with('success', 'Application deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'An error occurred while deleting the application. Please try again.');
        }
    }

    /**
     * Update application status
     */
    public function updateStatus(Request $request, EventApplication $application)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,completed'
        ]);

        $application->update([
            'application_status' => $request->status
        ]);

        return redirect()->back()->with('success', 'Application status updated successfully!');
    }

    /**
     * Check if registration is open for an event
     */
    private function isRegistrationOpen(Event $event): bool
    {
        $now = now();

        if ($event->registration_start_date && $now->lt($event->registration_start_date)) {
            return false;
        }

        if ($event->registration_end_date && $now->gt($event->registration_end_date)) {
            return false;
        }

        return $event->is_active && $event->is_published;
    }

    /**
     * Calculate total amount for the application
     */
    private function calculateTotalAmount(Event $event, int $numberOfPeople): float
    {
        if (!$event->is_paid) {
            return 0;
        }

        $price = $event->price;

        // Check for early bird pricing
        if ($event->early_bird_price && $event->early_bird_end_date && now()->lte($event->early_bird_end_date)) {
            $price = $event->early_bird_price;
        }

        return $price * $numberOfPeople;
    }

     public function adminindex(Request $request)
    {
        $query = EventApplication::with(['event', 'attendees']);

        // Apply filters if needed
        if ($request->filled('status')) {
            $query->where('application_status', $request->status);
        }

        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        $applications = $query->latest()->paginate(20);

        return view('ktvtc.admin.Events.index', compact('applications'));
    }

    // ... your existing getByEvent(), create(), store() methods remain the same ...

    /**
     * Display the specified resource - UPDATED
     */
    public function adminshow(EventApplication $application)
    {
        $application->load(['event', 'attendees']);
        return view('ktvtc.admin.Events.show', compact('application'));
    }
}
