<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\CalendarEvent;
use App\Models\MarketingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CalendarController extends Controller
{
    private function authorizeAccess(): void
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        if (!in_array($user->role, [Role::Admin, Role::Marcom])) {
            abort(403);
        }
    }

    /** Main calendar page */
    public function index()
    {
        $this->authorizeAccess();

        $requests = MarketingRequest::where('status', 'approved')
            ->orderBy('purpose')
            ->get(['id', 'purpose']);

        $categories = CalendarEvent::categoryLabels();
        $colors     = CalendarEvent::categoryColors();

        return view('calendar.index', compact('requests', 'categories', 'colors'));
    }

    /** JSON feed for FullCalendar */
    public function events(Request $request)
    {
        $this->authorizeAccess();

        $events = CalendarEvent::where('status', 'approved')
            ->with('creator')
            ->get();

        return response()->json($events->map(function (CalendarEvent $e) {
            return [
                'id'                  => $e->id,
                'title'               => $e->title,
                'start'               => $e->all_day
                    ? $e->start_datetime->toDateString()
                    : $e->start_datetime->toIso8601String(),
                'end'                 => $e->end_datetime
                    ? ($e->all_day
                        ? $e->end_datetime->addDay()->toDateString() // FullCalendar end is exclusive
                        : $e->end_datetime->toIso8601String())
                    : null,
                'allDay'              => $e->all_day,
                'color'               => $e->color,
                'extendedProps'       => [
                    'description'           => $e->description,
                    'category'              => $e->category,
                    'created_by'            => $e->creator?->name,
                    'marketing_request_id'  => $e->marketing_request_id,
                    'google_start'          => $e->all_day
                        ? $e->start_datetime->format('Ymd')
                        : $e->start_datetime->utc()->format('Ymd\THis\Z'),
                    'google_end'            => $e->end_datetime
                        ? ($e->all_day
                            ? $e->end_datetime->copy()->addDay()->format('Ymd')
                            : $e->end_datetime->utc()->format('Ymd\THis\Z'))
                        : null,
                ],
            ];
        }));
    }

    /** Download an iCal (.ics) file for a single approved event */
    public function ical(CalendarEvent $calendarEvent)
    {
        $this->authorizeAccess();

        $e   = $calendarEvent;
        $uid = 'event-' . $e->id . '@marcom.hartonogroup.com';
        $now = now()->utc()->format('Ymd\THis\Z');

        if ($e->all_day) {
            $dtstart = 'DTSTART;VALUE=DATE:' . $e->start_datetime->format('Ymd');
            $dtend   = 'DTEND;VALUE=DATE:' . ($e->end_datetime
                ? $e->end_datetime->copy()->addDay()->format('Ymd')
                : $e->start_datetime->copy()->addDay()->format('Ymd'));
        } else {
            $dtstart = 'DTSTART:' . $e->start_datetime->utc()->format('Ymd\THis\Z');
            $dtend   = 'DTEND:' . ($e->end_datetime
                ? $e->end_datetime->utc()->format('Ymd\THis\Z')
                : $e->start_datetime->utc()->copy()->addHour()->format('Ymd\THis\Z'));
        }

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Hartono Group Marcom//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:' . $uid,
            'DTSTAMP:' . $now,
            $dtstart,
            $dtend,
            'SUMMARY:' . $this->icalEscape($e->title),
        ];

        if ($e->description) {
            $lines[] = 'DESCRIPTION:' . $this->icalEscape($e->description);
        }

        $lines[] = 'END:VEVENT';
        $lines[] = 'END:VCALENDAR';

        $ics      = implode("\r\n", $lines) . "\r\n";
        $filename = Str::slug($e->title ?: 'event') . '.ics';

        return response($ics, 200, [
            'Content-Type'        => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function icalEscape(string $text): string
    {
        return str_replace(
            ['\\', "\r\n", "\n", "\r", ',', ';'],
            ['\\\\', '\\n',  '\\n', '\\n', '\\,', '\\;'],
            $text
        );
    }

    /** Store a new event (Marcom/Admin) */
    public function store(Request $request)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'title'                => ['required', 'string', 'max:200'],
            'description'          => ['nullable', 'string', 'max:2000'],
            'category'             => ['required', 'in:campaign,design,deadline,meeting,other'],
            'start_date'           => ['required', 'date_format:Y-m-d'],
            'start_time'           => ['nullable', 'date_format:H:i'],
            'end_date'             => ['nullable', 'date_format:Y-m-d'],
            'end_time'             => ['nullable', 'date_format:H:i'],
            'all_day'              => ['boolean'],
            'marketing_request_id' => ['nullable', 'exists:marketing_requests,id'],
        ]);

        $allDay        = (bool) ($validated['all_day'] ?? false);
        $startDatetime = $allDay
            ? $validated['start_date'] . ' 00:00:00'
            : $validated['start_date'] . ' ' . ($validated['start_time'] ?? '00:00') . ':00';
        $endDatetime   = null;
        if (!empty($validated['end_date'])) {
            $endDatetime = $allDay
                ? $validated['end_date'] . ' 00:00:00'
                : $validated['end_date'] . ' ' . ($validated['end_time'] ?? '00:00') . ':00';
        }

        $colors = CalendarEvent::categoryColors();

        CalendarEvent::create([
            'title'                => $validated['title'],
            'description'          => $validated['description'] ?? null,
            'category'             => $validated['category'],
            'start_datetime'       => $startDatetime,
            'end_datetime'         => $endDatetime,
            'all_day'              => $allDay,
            'marketing_request_id' => $validated['marketing_request_id'] ?? null,
            'color'                => $colors[$validated['category']],
            'created_by'           => auth()->id(),
            'status'               => 'pending_manager',
        ]);

        return redirect()->route('calendar.index')
            ->with('success', 'Event submitted for approval. A Manager needs to approve it first.');
    }

    /** Update an existing event (only if rejected — allows resubmit) */
    public function update(Request $request, CalendarEvent $calendarEvent)
    {
        $this->authorizeAccess();

        if ($calendarEvent->created_by !== auth()->id()) {
            abort(403);
        }

        if ($calendarEvent->status !== 'rejected') {
            return back()->with('error', 'Only rejected events can be edited and resubmitted.');
        }

        $validated = $request->validate([
            'title'                => ['required', 'string', 'max:200'],
            'description'          => ['nullable', 'string', 'max:2000'],
            'category'             => ['required', 'in:campaign,design,deadline,meeting,other'],
            'start_date'           => ['required', 'date_format:Y-m-d'],
            'start_time'           => ['nullable', 'date_format:H:i'],
            'end_date'             => ['nullable', 'date_format:Y-m-d'],
            'end_time'             => ['nullable', 'date_format:H:i'],
            'all_day'              => ['boolean'],
            'marketing_request_id' => ['nullable', 'exists:marketing_requests,id'],
        ]);

        $allDay        = (bool) ($validated['all_day'] ?? false);
        $startDatetime = $allDay
            ? $validated['start_date'] . ' 00:00:00'
            : $validated['start_date'] . ' ' . ($validated['start_time'] ?? '00:00') . ':00';
        $endDatetime   = null;
        if (!empty($validated['end_date'])) {
            $endDatetime = $allDay
                ? $validated['end_date'] . ' 00:00:00'
                : $validated['end_date'] . ' ' . ($validated['end_time'] ?? '00:00') . ':00';
        }

        $colors = CalendarEvent::categoryColors();

        $calendarEvent->update([
            'title'                => $validated['title'],
            'description'          => $validated['description'] ?? null,
            'category'             => $validated['category'],
            'start_datetime'       => $startDatetime,
            'end_datetime'         => $endDatetime,
            'all_day'              => $allDay,
            'marketing_request_id' => $validated['marketing_request_id'] ?? null,
            'color'                => $colors[$validated['category']],
            'status'               => 'pending_manager',
            'rejection_reason'     => null,
        ]);

        // Clear previous approvals so the flow restarts
        $calendarEvent->approvals()->delete();

        return redirect()->route('calendar.index')
            ->with('success', 'Event resubmitted for approval.');
    }

    /** Delete an event */
    public function destroy(CalendarEvent $calendarEvent)
    {
        $this->authorizeAccess();

        if ($calendarEvent->created_by !== auth()->id() && !in_array(auth()->user()->role, [Role::Admin])) {
            abort(403);
        }

        $calendarEvent->delete();

        return back()->with('success', 'Event deleted.');
    }

    /** Pending events list — for Marcom to see status of their submissions */
    public function pending()
    {
        $this->authorizeAccess();

        $events = CalendarEvent::where('created_by', auth()->id())
            ->whereIn('status', ['pending_manager', 'pending_gm_director', 'rejected'])
            ->with('approvals.approver')
            ->latest()
            ->get();

        return view('calendar.pending', compact('events'));
    }
}
