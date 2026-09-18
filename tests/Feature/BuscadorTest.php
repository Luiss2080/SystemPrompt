<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Prompt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuscadorTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/buscador')->assertRedirect('/login');
        $this->getJson('/buscador/search?q=ab')->assertUnauthorized();
    }

    public function test_search_page_renders_without_a_term(): void
    {
        $this->actingAs(User::factory()->create())->get('/buscador')->assertOk()->assertSee('Buscador global');
    }

    public function test_search_page_lists_matches_from_the_header_form(): void
    {
        $user = User::factory()->create();
        Prompt::create(['user_id' => $user->id, 'titulo' => 'Resumen ejecutivo', 'contenido' => 'x']);

        $this->actingAs($user)->get('/buscador?query=Resumen')
            ->assertOk()
            ->assertSee('Resumen ejecutivo');
    }

    public function test_search_json_returns_matches_and_hides_other_users_private_prompts(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Prompt::create(['user_id' => $user->id, 'titulo' => 'Correo formal', 'contenido' => 'x']);
        Prompt::create(['user_id' => $other->id, 'titulo' => 'Correo secreto', 'contenido' => 'x', 'es_publico' => false]);
        Prompt::create(['user_id' => $other->id, 'titulo' => 'Correo publico', 'contenido' => 'x', 'es_publico' => true]);
        Categoria::create(['nombre' => 'Correos']);

        $response = $this->actingAs($user)->getJson('/buscador/search?q=Correo')->assertOk();

        $titulos = collect($response->json('resultados'))->pluck('titulo')->all();
        $this->assertContains('Correo formal', $titulos);
        $this->assertContains('Correo publico', $titulos);
        $this->assertContains('Correos', $titulos);
        $this->assertNotContains('Correo secreto', $titulos);
    }

    public function test_search_json_ignores_terms_shorter_than_two_characters(): void
    {
        $this->actingAs(User::factory()->create())
            ->getJson('/buscador/search?q=a')
            ->assertOk()
            ->assertExactJson(['resultados' => []]);
    }
}
