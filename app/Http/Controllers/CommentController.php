<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    // reactie opslaan bij een post
    public function store(Request $request, Post $post)
    {
        $attributes = $request->validate([
            'name' => 'required|max:50',
            'body' => 'required|min:3|max:1000',
        ], [
            'name.required' => 'Vul je naam in.',
            'name.max' => 'Je naam mag maximaal 50 tekens zijn.',
            'body.required' => 'Vul een reactie in.',
            'body.min' => 'Je reactie moet minimaal 3 tekens zijn.',
            'body.max' => 'Je reactie mag maximaal 1000 tekens zijn.',
        ]);

        $post->comments()->create($attributes);

        return redirect('/posts/' . $post->slug . '#comments')->with('success', 'Bedankt! Je reactie is geplaatst.');
    }
}
