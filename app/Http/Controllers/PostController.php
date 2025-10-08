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
        $query = Post::where('is_public', true);

        if ($request->boolean('following') && auth()->check()) {
            $followedUserIds = auth()->user()->following()->pluck('users.id');
            $query->whereIn('user_id', $followedUserIds);
        }

        $posts = $query->orderBy('published_at', 'desc')->paginate(5);

        return response()->json($posts);
    }

    public function show(Post $post)
    {
        if (!$post->is_public) {
            abort(404);
        }

        $post->load('tags', 'user');
        $post->user->is_following = auth()->user()?->isFollowing($post->user);

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
                'tags.*'    => 'string',
            ]
        );

        $validated['slug'] = Str::slug($validated['title']);

        $post = auth()->user()->posts()->create($validated);

        if ($request->has('tags')) {
            $tags = collect($request->input('tags'))->map(function ($tagName) {
                return Tag::firstOrCreate(['name' => $tagName, 'slug' => Str::slug($tagName)])->id;
            });
            $post->tags()->sync($tags);
        }

        return response()->json($post->load('tags'));
    }

    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        $validated = $request->validate([
                                            'title'     => 'sometimes|string|max:255',
                                            'content'   => 'sometimes|string',
                                            'is_public' => 'sometimes|boolean',
                                            'tags'      => 'sometimes|array',
                                            'tags.*'    => 'string',
                                        ]);

        $post->update($validated);

        if ($request->has('tags')) {
            $tags = collect($request->input('tags'))->map(function ($tagName) {
                return Tag::firstOrCreate(['name' => $tagName, 'slug' => Str::slug($tagName)])->id;
            });
            $post->tags()->sync($tags);
        }

        return response()->json($post->load('tags'));
    }
}
