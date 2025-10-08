<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostAccessRequest;
use Illuminate\Http\Request;

class PostAccessController extends Controller
{
    public function requestAccess(Request $request, Post $post)
    {
        $this->authorize('view', $post);

        $request->user()->postAccessRequests()->create([
            'post_id' => $post->id,
        ]);

        return response()->json(['status' => 'request sent']);
    }

    public function approveAccess(Request $request, PostAccessRequest $accessRequest)
    {
        $this->authorize('update', $accessRequest->post);

        $accessRequest->update(['status' => 'approved']);

        return response()->json(['status' => 'approved']);
    }

    public function denyAccess(Request $request, PostAccessRequest $accessRequest)
    {
        $this->authorize('update', $accessRequest->post);

        $accessRequest->update(['status' => 'denied']);

        return response()->json(['status' => 'denied']);
    }

    public function listRequests(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        return response()->json($post->accessRequests()->with('user')->get());
    }
}
