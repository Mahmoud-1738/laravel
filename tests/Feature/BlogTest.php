<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    public function test_overzichtspagina_toont_posts(): void
    {
        $post = Post::factory()->create(['title' => 'Hallo Laravel']);

        $this->get('/')->assertStatus(200)->assertSee('Hallo Laravel');
    }

    public function test_detailpagina_toont_post(): void
    {
        $post = Post::factory()->create(['title' => 'Detail test']);

        $this->get('/posts/' . $post->slug)->assertStatus(200)->assertSee('Detail test');
    }

    public function test_onbekende_post_geeft_404(): void
    {
        $this->get('/posts/bestaat-niet')->assertStatus(404);
    }

    // zoekfunctionaliteit
    public function test_zoeken_vindt_juiste_posts(): void
    {
        Post::factory()->create(['title' => 'Alles over Eloquent']);
        Post::factory()->create(['title' => 'Iets over Tailwind']);

        $this->get('/?search=Eloquent')
            ->assertSee('Alles over Eloquent')
            ->assertDontSee('Iets over Tailwind');
    }

    public function test_zoeken_met_rare_tekens_geeft_geen_error(): void
    {
        Post::factory()->create();

        $this->get('/?search=' . urlencode("' OR 1=1 --"))->assertStatus(200);
        $this->get('/?search=' . urlencode('<script>alert(1)</script>'))
            ->assertStatus(200)
            ->assertDontSee('<script>alert(1)</script>', false);
        $this->get('/?search=%25')->assertStatus(200);
    }

    // filteren op categorie
    public function test_filteren_op_categorie(): void
    {
        $php = Category::factory()->create(['name' => 'PHP', 'slug' => 'php']);
        $css = Category::factory()->create(['name' => 'CSS', 'slug' => 'css']);

        Post::factory()->create(['title' => 'Een PHP post', 'category_id' => $php->id]);
        Post::factory()->create(['title' => 'Een CSS post', 'category_id' => $css->id]);

        $this->get('/?category=php')
            ->assertSee('Een PHP post')
            ->assertDontSee('Een CSS post');
    }

    public function test_onbekende_categorie_toont_geen_posts(): void
    {
        Post::factory()->create(['title' => 'Zichtbare post']);

        $this->get('/?category=bestaat-niet')
            ->assertStatus(200)
            ->assertDontSee('Zichtbare post');
    }

    // paginatie
    public function test_paginatie_toont_6_posts_per_pagina(): void
    {
        Post::factory(8)->create();

        $response = $this->get('/');
        $this->assertCount(6, $response->viewData('posts'));

        $response = $this->get('/?page=2');
        $this->assertCount(2, $response->viewData('posts'));
    }

    // reacties
    public function test_reactie_plaatsen(): void
    {
        $post = Post::factory()->create();

        $this->post('/posts/' . $post->slug . '/comments', [
            'name' => 'Sam',
            'body' => 'Leuke post!',
        ])->assertRedirect();

        $this->assertDatabaseHas('comments', ['post_id' => $post->id, 'name' => 'Sam']);
    }

    public function test_lege_reactie_wordt_geweigerd(): void
    {
        $post = Post::factory()->create();

        $this->post('/posts/' . $post->slug . '/comments', [
            'name' => '',
            'body' => '',
        ])->assertSessionHasErrors(['name', 'body']);

        $this->assertDatabaseCount('comments', 0);
    }

    public function test_te_lange_reactie_wordt_geweigerd(): void
    {
        $post = Post::factory()->create();

        $this->post('/posts/' . $post->slug . '/comments', [
            'name' => str_repeat('a', 51),
            'body' => str_repeat('b', 1001),
        ])->assertSessionHasErrors(['name', 'body']);
    }

    public function test_html_in_reactie_wordt_niet_uitgevoerd(): void
    {
        $post = Post::factory()->create();

        $this->post('/posts/' . $post->slug . '/comments', [
            'name' => 'Hacker',
            'body' => '<script>alert("xss")</script>',
        ]);

        $this->get('/posts/' . $post->slug)
            ->assertDontSee('<script>alert("xss")</script>', false)
            ->assertSee('&lt;script&gt;', false);
    }

    public function test_te_veel_reacties_wordt_geblokkeerd(): void
    {
        $post = Post::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/posts/' . $post->slug . '/comments', ['name' => 'Spam', 'body' => 'spam spam']);
        }

        $this->post('/posts/' . $post->slug . '/comments', ['name' => 'Spam', 'body' => 'spam spam'])
            ->assertStatus(429);
    }

    public function test_paginatie_houdt_filter_vast(): void
    {
        $category = Category::factory()->create(['slug' => 'laravel']);
        Post::factory(8)->create(['category_id' => $category->id]);

        $this->get('/?category=laravel')->assertSee('category=laravel&amp;page=2', false);
        $this->get('/?page=999')->assertStatus(200)->assertSee('Er zijn geen posts gevonden');
    }
}
