<?php

namespace App\Http\Controllers;

use App\Models\MarketingRequest;
use App\Models\RequestComment;
use App\Models\User;
use App\Notifications\MentionedInComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RequestCommentController extends Controller
{
    public function store(Request $request, MarketingRequest $marketingRequest): RedirectResponse
    {
        $request->validate([
            'body'  => 'nullable|string|max:2000',
            'image' => 'nullable|image|mimes:jpeg,png,gif,webp|max:5120',
        ]);

        if (empty($request->body) && !$request->hasFile('image')) {
            return back()->withErrors(['body' => 'Please add a comment or attach an image.'])->withInput();
        }

        // Only the request owner or users who can view all requests can comment
        $user = auth()->user();
        if ($marketingRequest->user_id !== $user->id && !$user->canViewAllRequests()) {
            abort(403);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('comment-images', 'public');
        }

        $comment = RequestComment::create([
            'marketing_request_id' => $marketingRequest->id,
            'user_id'              => $user->id,
            'body'                 => $request->body ?? '',
            'image_path'           => $imagePath,
        ]);

        // Notify mentioned users
        preg_match_all('/@(\w+)/', $request->body, $matches);
        if (!empty($matches[1])) {
            $mentionedUsers = User::whereIn('username', array_unique($matches[1]))
                ->where('id', '!=', $user->id) // don't notify yourself
                ->get();

            foreach ($mentionedUsers as $mentioned) {
                $mentioned->notify(new MentionedInComment($comment, $marketingRequest, $user));
            }
        }

        return back()->with('success', 'Comment added.');
    }
}
