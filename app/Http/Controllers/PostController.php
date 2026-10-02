<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;

class PostController extends Controller
{
    // overzichtspagina met alle posts
    public function index()
    {
        $posts = Post::latest('published_at')
            ->with('category', 'author')
            ->withCount('comments')
            ->filter(request(['search', 'category']))
            ->paginate(6)
            ->withQueryString();

        return view('posts.index', [
            'posts' => $posts,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    // detailpagina van een post
    public function show(Post $post)
    {
        return view('posts.show', [
            'post' => $post,
            'comments' => $post->comments()->latest()->get(),
        ]);
    }
}
