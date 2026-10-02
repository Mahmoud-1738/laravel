<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'category_id', 'slug', 'thumbnail', 'title', 'excerpt', 'body', 'published_at'];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    // url van de afbeelding, zonder upload een plaatje uit de template
    public function thumbnailUrl()
    {
        if ($this->thumbnail) {
            return asset('storage/' . $this->thumbnail);
        }

        return '/images/illustration-' . ($this->id % 5 + 1) . '.png';
    }

    // filter voor zoeken en categorie (Post::filter(request(['search', 'category'])))
    public function scopeFilter($query, array $filters)
    {
        // zoeken op titel of tekst
        if (! empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                    ->orWhere('body', 'like', '%' . $search . '%');
            });
        }

        // filteren op categorie (slug)
        if (! empty($filters['category'])) {
            $category = $filters['category'];

            $query->whereHas('category', function ($query) use ($category) {
                $query->where('slug', $category);
            });
        }
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
