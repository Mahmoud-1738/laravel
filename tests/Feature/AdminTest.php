<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_gast_kan_niet_in_het_cms(): void
    {
        $this->get('/admin/posts')->assertRedirect('/login');
        $this->post('/admin/posts', [])->assertRedirect('/login');
    }

    public function test_inloggen_met_goed_wachtwoord(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com', 'password' => 'geheim123']);

        $this->post('/login', ['email' => 'test@example.com', 'password' => 'geheim123'])
            ->assertRedirect('/admin/posts');

        $this->assertAuthenticatedAs($user);
    }

    public function test_inloggen_met_fout_wachtwoord(): void
    {
        User::factory()->create(['email' => 'test@example.com', 'password' => 'geheim123']);

        $this->post('/login', ['email' => 'test@example.com', 'password' => 'fout'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_uitloggen(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/logout')
            ->assertRedirect('/');

        $this->assertGuest();
    }

    public function test_admin_kan_post_toevoegen(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $this->actingAs($user)->post('/admin/posts', [
            'title' => 'Nieuwe post',
            'slug' => 'nieuwe-post',
            'excerpt' => 'Korte tekst',
            'body' => 'Lange tekst',
            'category_id' => $category->id,
        ])->assertRedirect('/admin/posts');

        $this->assertDatabaseHas('posts', ['slug' => 'nieuwe-post', 'user_id' => $user->id]);
        $this->get('/posts/nieuwe-post')->assertSee('Nieuwe post');
    }

    public function test_post_toevoegen_met_foute_invoer(): void
    {
        Post::factory()->create(['slug' => 'bestaat-al']);

        $this->actingAs(User::factory()->create())->post('/admin/posts', [
            'title' => '',
            'slug' => 'bestaat-al',
            'excerpt' => '',
            'body' => '',
            'category_id' => 999,
        ])->assertSessionHasErrors(['title', 'slug', 'excerpt', 'body', 'category_id']);

        $this->actingAs(User::factory()->create())->post('/admin/posts', [
            'slug' => 'met spaties en <tags>',
        ])->assertSessionHasErrors('slug');
    }

    public function test_admin_kan_post_aanpassen(): void
    {
        $post = Post::factory()->create();

        $this->actingAs(User::factory()->create())->patch('/admin/posts/' . $post->id, [
            'title' => 'Aangepaste titel',
            'slug' => $post->slug,
            'excerpt' => 'Nieuw',
            'body' => 'Nieuw',
            'category_id' => $post->category_id,
        ])->assertRedirect('/admin/posts');

        $this->assertEquals('Aangepaste titel', $post->fresh()->title);
    }

    // file upload
    public function test_afbeelding_uploaden(): void
    {
        Storage::fake('public');
        $category = Category::factory()->create();

        $this->actingAs(User::factory()->create())->post('/admin/posts', [
            'title' => 'Post met foto',
            'slug' => 'post-met-foto',
            'thumbnail' => UploadedFile::fake()->create('foto.jpg', 100, 'image/jpeg'),
            'excerpt' => 'Kort',
            'body' => 'Lang',
            'category_id' => $category->id,
        ])->assertRedirect('/admin/posts');

        $post = Post::where('slug', 'post-met-foto')->first();

        $this->assertNotNull($post->thumbnail);
        Storage::disk('public')->assertExists($post->thumbnail);
    }

    public function test_geen_afbeelding_wordt_geweigerd(): void
    {
        Storage::fake('public');
        $category = Category::factory()->create();

        $this->actingAs(User::factory()->create())->post('/admin/posts', [
            'title' => 'Hack',
            'slug' => 'hack',
            'thumbnail' => UploadedFile::fake()->create('hack.php', 10, 'text/x-php'),
            'excerpt' => 'Kort',
            'body' => 'Lang',
            'category_id' => $category->id,
        ])->assertSessionHasErrors('thumbnail');

        $this->assertDatabaseMissing('posts', ['slug' => 'hack']);
    }

    public function test_te_grote_afbeelding_wordt_geweigerd(): void
    {
        Storage::fake('public');
        $category = Category::factory()->create();

        $this->actingAs(User::factory()->create())->post('/admin/posts', [
            'title' => 'Groot',
            'slug' => 'groot',
            'thumbnail' => UploadedFile::fake()->create('groot.jpg', 3000, 'image/jpeg'),
            'excerpt' => 'Kort',
            'body' => 'Lang',
            'category_id' => $category->id,
        ])->assertSessionHasErrors('thumbnail');
    }

    public function test_bewerken_zonder_nieuwe_afbeelding_houdt_oude(): void
    {
        $post = Post::factory()->create(['thumbnail' => 'thumbnails/oud.jpg']);

        $this->actingAs(User::factory()->create())->patch('/admin/posts/' . $post->id, [
            'title' => 'Nieuwe titel',
            'slug' => $post->slug,
            'excerpt' => 'Nieuw',
            'body' => 'Nieuw',
            'category_id' => $post->category_id,
        ]);

        $this->assertEquals('thumbnails/oud.jpg', $post->fresh()->thumbnail);
    }

    public function test_admin_kan_post_verwijderen(): void
    {
        $post = Post::factory()->create();

        $this->actingAs(User::factory()->create())
            ->delete('/admin/posts/' . $post->id)
            ->assertRedirect('/admin/posts');

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }
}
