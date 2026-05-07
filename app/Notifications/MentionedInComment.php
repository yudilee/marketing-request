<?php

namespace App\Notifications;

use App\Models\MarketingRequest;
use App\Models\RequestComment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MentionedInComment extends Notification
{
    use Queueable;

    public function __construct(
        public readonly RequestComment   $comment,
        public readonly MarketingRequest $marketingRequest,
        public readonly User             $mentionedBy,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'comment_id'           => $this->comment->id,
            'marketing_request_id' => $this->marketingRequest->id,
            'mentioned_by_name'    => $this->mentionedBy->name,
            'request_purpose'      => $this->marketingRequest->purpose,
            'body_preview'         => mb_substr($this->comment->body, 0, 120),
        ];
    }
}
