<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\CalendarEvent;
use App\Models\CalendarEventApproval;
use Illuminate\Http\Request;

class CalendarEventApprovalController extends Controller
{
    /** List of calendar events awaiting this user's approval */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $role = $user->role instanceof Role ? $user->role : Role::from($user->role);

        // Only the Marcom Manager sees step-1 pending events
        if ($user->isMarcomManager()) {
            $events = CalendarEvent::where('status', 'pending_manager')
                ->with(['creator', 'approvals.approver'])
                ->latest()
                ->get();
        }
        // GM or Director see step-2 pending events
        elseif (in_array($role, [Role::Gm, Role::Director])) {
            $events = CalendarEvent::where('status', 'pending_gm_director')
                ->with(['creator', 'approvals.approver'])
                ->latest()
                ->get();
        } else {
            abort(403);
        }

        return view('calendar.approvals', compact('events'));
    }

    /** Record an approval or rejection */
    public function decide(Request $request, CalendarEvent $calendarEvent)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $role = $user->role instanceof Role ? $user->role : Role::from($user->role);

        $validated = $request->validate([
            'status'  => ['required', 'in:approved,rejected'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        // Gate: only the right role can act at each stage
        if ($calendarEvent->status === 'pending_manager' && !$user->isMarcomManager()) {
            return back()->with('error', 'Only the Marcom Manager can approve at this stage.');
        }
        if ($calendarEvent->status === 'pending_gm_director' && !in_array($role, [Role::Gm, Role::Director])) {
            return back()->with('error', 'Only a GM or Director can approve at this stage.');
        }

        // Prevent double-acting
        if ($calendarEvent->approvals()->where('approver_id', $user->id)->exists()) {
            return back()->with('error', 'You have already acted on this event.');
        }

        CalendarEventApproval::create([
            'calendar_event_id' => $calendarEvent->id,
            'approver_id'       => $user->id,
            'status'            => $validated['status'],
            'comment'           => $validated['comment'] ?? null,
            'acted_at'          => now(),
        ]);

        if ($validated['status'] === 'rejected') {
            $calendarEvent->status           = 'rejected';
            $calendarEvent->rejection_reason = $validated['comment'] ?? null;
            $calendarEvent->save();

            return back()->with('success', 'Event rejected. The creator has been notified.');
        }

        // Approved — advance the workflow
        if ($calendarEvent->status === 'pending_manager') {
            $calendarEvent->status = 'pending_gm_director';
            $calendarEvent->save();
            return back()->with('success', 'Approved at Step 1. Forwarded to GM / Director for final sign-off.');
        }

        if ($calendarEvent->status === 'pending_gm_director') {
            $calendarEvent->status = 'approved';
            $calendarEvent->save();
            return back()->with('success', 'Event fully approved and is now visible on the calendar.');
        }

        return back()->with('error', 'Unexpected state.');
    }
}
