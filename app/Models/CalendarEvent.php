<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    protected $fillable = [
        'title',
        'description',
        'category',
        'color',
        'start_datetime',
        'end_datetime',
        'all_day',
        'created_by',
        'marketing_request_id',
        'status',
        'rejection_reason',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime'   => 'datetime',
        'all_day'        => 'boolean',
    ];

    public static function categoryColors(): array
    {
        return [
            'campaign' => '#3B82F6', // blue
            'design'   => '#6366F1', // indigo
            'deadline' => '#EF4444', // red
            'meeting'  => '#10B981', // green
            'other'    => '#6B7280', // gray
        ];
    }

    public static function categoryLabels(): array
    {
        return [
            'campaign' => 'Campaign',
            'design'   => 'Design',
            'deadline' => 'Deadline',
            'meeting'  => 'Meeting',
            'other'    => 'Other',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function marketingRequest()
    {
        return $this->belongsTo(MarketingRequest::class);
    }

    public function approvals()
    {
        return $this->hasMany(CalendarEventApproval::class);
    }
}
