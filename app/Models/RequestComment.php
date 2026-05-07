<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestComment extends Model
{
    protected $fillable = ['marketing_request_id', 'user_id', 'body', 'image_path'];

    public function marketingRequest()
    {
        return $this->belongsTo(MarketingRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
