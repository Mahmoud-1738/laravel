<?php

namespace Tests\Feature;

use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsletterTest extends TestCase
{
    use RefreshDatabase;

    public function test_inschrijven_voor_nieuwsbrief(): void
    {
        $this->post('/newsletter', ['email' => 'test@example.com'])->assertRedirect();

        $this->assertDatabaseHas('subscribers', ['email' => 'test@example.com']);
    }

    public function test_ongeldig_emailadres_wordt_geweigerd(): void
    {
        $this->post('/newsletter', ['email' => 'geen-email'])->assertSessionHasErrorsIn('newsletter', 'email');
        $this->post('/newsletter', ['email' => ''])->assertSessionHasErrorsIn('newsletter', 'email');

        $this->assertDatabaseCount('subscribers', 0);
    }

    public function test_dubbel_inschrijven_kan_niet(): void
    {
        Subscriber::create(['email' => 'test@example.com']);

        $this->post('/newsletter', ['email' => 'test@example.com'])->assertSessionHasErrorsIn('newsletter', 'email');

        $this->assertDatabaseCount('subscribers', 1);
    }
}
