<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // admin account voor het cms (zie README)
        $user = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $laravel = Category::create(['name' => 'Laravel', 'slug' => 'laravel']);
        $php = Category::create(['name' => 'PHP', 'slug' => 'php']);
        $frontend = Category::create(['name' => 'Frontend', 'slug' => 'frontend']);

        $posts = [
            [$laravel, 'Mijn eerste Laravel project', 'Hoe ik begonnen ben met Laravel en wat ik de eerste week geleerd heb.'],
            [$laravel, 'Routes en controllers', 'Een route stuurt een request naar een controller. Zo werkt dat in Laravel.'],
            [$laravel, 'Eloquent relaties uitgelegd', 'belongsTo, hasMany en hoe je ze gebruikt in je models.'],
            [$laravel, 'Blade components', 'Met Blade components hoef je je layout maar een keer te schrijven.'],
            [$php, 'OOP in PHP: classes en objecten', 'Wat is een class, wat is een object en waarom gebruiken we OOP?'],
            [$php, 'Veilig programmeren in PHP', 'SQL injection en XSS voorkomen, en waarom je nooit user input moet vertrouwen.'],
            [$php, 'PHP en MySQL', 'Van losse mysqli queries naar een nette database laag.'],
            [$frontend, 'Tailwind CSS voor beginners', 'Utility classes gebruiken in plaats van een eigen CSS bestand.'],
            [$frontend, 'Responsive design', 'Je website goed laten werken op mobiel, tablet en desktop.'],
            [$frontend, 'Mijn portfolio website', 'Een overzicht van de projecten die ik dit jaar gemaakt heb.'],
        ];

        foreach ($posts as $i => $item) {
            [$category, $title, $excerpt] = $item;

            $post = Post::create([
                'user_id' => $user->id,
                'category_id' => $category->id,
                'slug' => Str::slug($title),
                'title' => $title,
                'excerpt' => $excerpt,
                'body' => $excerpt . "\n\n" . fake()->paragraph(6) . "\n\n" . fake()->paragraph(8) . "\n\n" . fake()->paragraph(5),
                'published_at' => now()->subDays(count($posts) - $i),
            ]);

            Comment::factory(rand(0, 3))->create(['post_id' => $post->id]);
        }
    }
}
