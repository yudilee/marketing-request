<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingRequest extends Model
{
    protected $fillable = [
        'user_id',
        'pic_name',
        'department_id',
        'date_request',
        'deadline',
        'is_local_campaign',
        'is_group_campaign',
        'for_sales',
        'sales_vehicle_type',
        'for_aftersales',
        'aftersales_vehicle_type',
        'for_others',
        'others_description',
        'purpose',
        'detail_concept',
        'further_information',
        'reference_visual',
        'output_media',
        'status',
        'manager_comment',
        'reviewed_by',
        'reviewed_at',
        'production_status',
        'production_milestone',
        'milestone_timestamps',
        'production_timeline',
        'final_file',
        'production_notes',
        'production_updated_at',
    ];

    protected $casts = [
        'output_media'      => 'array',
        'is_local_campaign' => 'boolean',
        'is_group_campaign' => 'boolean',
        'for_sales'         => 'boolean',
        'for_aftersales'    => 'boolean',
        'for_others'        => 'boolean',
        'date_request'      => 'date',
        'deadline'          => 'date',
        'reviewed_at'            => 'datetime',
        'production_updated_at'  => 'datetime',
        'milestone_timestamps'   => 'array',
        'production_timeline'    => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approvals()
    {
        return $this->hasMany(Approval::class);
    }

    public function comments()
    {
        return $this->hasMany(RequestComment::class)->with('user')->latest();
    }

    public static function productionMilestoneLabels(): array
    {
        return [
            1 => 'Brief Received',
            2 => 'In Design',
            3 => 'Draft Ready',
            4 => 'Finalizing',
        ];
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'submitted'    => 'badge-yellow',
            'under_review' => 'badge-blue',
            'approved'     => 'badge-green',
            'rejected'     => 'badge-red',
            default        => 'badge-gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'submitted'    => 'Submitted',
            'under_review' => 'Under Review',
            'approved'     => 'Approved',
            'rejected'     => 'Rejected',
            default        => 'Unknown',
        };
    }

    public function getProductionStatusLabelAttribute(): string
    {
        return match ($this->production_status) {
            'pending'    => 'Pending',
            'on_process' => 'On Process',
            'revision'   => 'Revision',
            'completed'  => 'Completed',
            default      => '—',
        };
    }

    public function getProductionStatusBadgeAttribute(): string
    {
        return match ($this->production_status) {
            'pending'    => 'badge-yellow',
            'on_process' => 'badge-blue',
            'revision'   => 'badge-red',
            'completed'  => 'badge-green',
            default      => 'badge-gray',
        };
    }

    /** Returns the ordered list of production stages with their state. */
    public function getProductionStagesAttribute(): array
    {
        $stages = ['pending', 'on_process', 'revision', 'completed'];
        $current = $this->production_status;
        $order   = array_flip($stages);

        return array_map(function ($stage) use ($current, $order) {
            $currentIdx = isset($order[$current]) ? $order[$current] : -1;
            $stageIdx   = $order[$stage];
            return [
                'key'    => $stage,
                'label'  => match ($stage) {
                    'pending'    => 'Pending',
                    'on_process' => 'On Process',
                    'revision'   => 'Revision',
                    'completed'  => 'Completed',
                },
                'done'   => $stageIdx < $currentIdx,
                'active' => $stage === $current,
            ];
        }, $stages);
    }
}
