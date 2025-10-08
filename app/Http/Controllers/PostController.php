<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::query();

        if ($request->boolean('following') && auth()->check()) {
            $followedUserIds = auth()->user()->following()->pluck('users.id');
            $query->whereIn('user_id', $followedUserIds);
        }

        $posts = $query->orderBy('published_at', 'desc')->paginate(5);

        $posts->getCollection()->transform(function ($post) use ($request) {
            $canViewContent = false;
            $accessRequestStatus = 'none';

            if ($post->is_public) {
                $canViewContent = true;
            } elseif ($request->user()) {
                if ($request->user()->id === $post->user_id) {
                    $canViewContent = true;
                } else {
                    $accessRequest = $post->accessRequests()
                        ->where('user_id', $request->user()->id)
                        ->first();

                    if ($accessRequest) {
                        $accessRequestStatus = $accessRequest->status;
                        if ($accessRequest->status === 'approved') {
                            $canViewContent = true;
                        }
                    }
                }
            }

            $post->can_view_content = $canViewContent;
            $post->access_request_status = $accessRequestStatus;

            return $post;
        });

        return response()->json($posts);
    }

    public function show(Request $request, Post $post)
    {
        $post->load('tags', 'user');
        $post->user->is_following = $request->user()?->isFollowing($post->user);

        $canViewContent = false;
        $accessRequestStatus = 'none';

        if ($post->is_public) {
            $canViewContent = true;
        } elseif ($request->user()) {
            if ($request->user()->id === $post->user_id) {
                $canViewContent = true;
            } else {
                $accessRequest = $post->accessRequests()
                    ->where('user_id', $request->user()->id)
                    ->first();

                if ($accessRequest) {
                    $accessRequestStatus = $accessRequest->status;
                    if ($accessRequest->status === 'approved') {
                        $canViewContent = true;
                    }
                }
            }
        }

        $post->can_view_content = $canViewContent;
        $post->access_request_status = $accessRequestStatus;

        return response()->json($post);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Post::class);

        $validated = $request->validate(
            [
                'title'     => 'required|string|max:255',
                'content'   => 'required|string',
                'is_public' => 'required|boolean',
                'tags'      => 'sometimes|array',
            ],
        );

        $validated['slug'] = Str::slug($validated['title']);

        $post = auth()->user()->posts()->create($validated);

        if ($request->has('tags')) {
            $tags = collect($request->input('tags'))
                ->filter(function ($tag) {
                    return $tag !== null;
                })
                ->map(function ($tagName) {
                    return Tag::firstOrCreate(
                        [
                            'name' => $tagName,
                            'slug' => Str::slug($tagName),
                        ])->id;
                });
            $post->tags()->sync($tags);
        }

        return response()->json($post->load('tags'));
    }

    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        $validated = $request->validate(
            [
                'title'     => 'sometimes|string|max:255',
                'content'   => 'sometimes|string',
                'is_public' => 'sometimes|boolean',
                'tags'      => 'sometimes|array',
            ]);

        $post->update($validated);

        if ($request->has('tags')) {
            $tags = collect($request->input('tags'))
                ->filter(function ($tag) {
                    return $tag !== null;
                })
                ->map(function ($tagName) {
                    return Tag::firstOrCreate(
                        [
                            'name' => $tagName,
                            'slug' => Str::slug($tagName),
                        ])->id;
                });
            $post->tags()->sync($tags);
        }

        return response()->json($post->load('tags'));
    }
}
