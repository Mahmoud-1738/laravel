<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminPostController extends Controller
{
    // overzicht van alle posts in het cms
    public function index()
    {
        return view('admin.posts.index', [
            'posts' => Post::latest('published_at')->with('category')->paginate(10),
        ]);
    }

    public function create()
    {
        return view('admin.posts.create', [
            'post' => new Post(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $attributes = $this->validatePost($request);

        $attributes['user_id'] = auth()->id();
        $attributes['published_at'] = now();

        Post::create($attributes);

        return redirect('/admin/posts')->with('success', 'Post is toegevoegd!');
    }

    public function edit(Post $post)
    {
        return view('admin.posts.edit', [
            'post' => $post,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Post $post)
    {
        $attributes = $this->validatePost($request, $post);

        $post->update($attributes);

        return redirect('/admin/posts')->with('success', 'Post is aangepast!');
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return redirect('/admin/posts')->with('success', 'Post is verwijderd!');
    }

    // dezelfde validatie voor toevoegen en aanpassen
    protected function validatePost(Request $request, ?Post $post = null)
    {
        $attributes = $request->validate([
            'title' => 'required|max:255',
            'slug' => ['required', 'max:255', 'alpha_dash', Rule::unique('posts', 'slug')->ignore($post?->id)],
            'thumbnail' => 'nullable|image|max:2048',
            'excerpt' => 'required|max:500',
            'body' => 'required',
            'category_id' => ['required', Rule::exists('categories', 'id')],
        ], [
            'title.required' => 'Vul een titel in.',
            'slug.required' => 'Vul een slug in.',
            'slug.alpha_dash' => 'De slug mag alleen letters, cijfers, - en _ bevatten.',
            'slug.unique' => 'Deze slug bestaat al, kies een andere.',
            'thumbnail.image' => 'Het bestand moet een afbeelding zijn (jpg, png, gif of webp).',
            'thumbnail.max' => 'De afbeelding mag maximaal 2 MB zijn.',
            'excerpt.required' => 'Vul een korte samenvatting in.',
            'excerpt.max' => 'De samenvatting mag maximaal 500 tekens zijn.',
            'body.required' => 'Vul de tekst van de post in.',
            'category_id.required' => 'Kies een categorie.',
            'category_id.exists' => 'Deze categorie bestaat niet.',
        ]);

        // afbeelding opslaan in storage/app/public/thumbnails (alleen als er een bestand gekozen is)
        if ($request->hasFile('thumbnail')) {
            $attributes['thumbnail'] = $request->file('thumbnail')->store('thumbnails', 'public');
        } else {
            unset($attributes['thumbnail']);
        }

        return $attributes;
    }
}
