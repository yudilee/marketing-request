<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarEventApproval extends Model
{
    protected $fillable = [
        'calendar_event_id',
        'approver_id',
        'status',
        'comment',
        'acted_at',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

    public function calendarEvent()
    {
        return $this->belongsTo(CalendarEvent::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
