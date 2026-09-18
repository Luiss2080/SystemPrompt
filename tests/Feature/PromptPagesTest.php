<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Prompt;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromptPagesTest extends TestCase
{
    use RefreshDatabase;

    private function prompt(User $owner, array $attrs = []): Prompt
    {
        return Prompt::create($attrs + [
            'user_id' => $owner->id,
            'titulo' => 'Mi prompt',
            'contenido' => 'Contenido de prueba',
            'version_actual' => 1,
        ]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/prompts')->assertRedirect('/login');
    }

    public function test_index_renders_with_the_users_prompts(): void
    {
        $user = User::factory()->create();
        $this->prompt($user, ['titulo' => 'Visible para mi']);
        $this->prompt(User::factory()->create(), ['titulo' => 'Ajeno']);

        $this->actingAs($user)->get('/prompts')
            ->assertOk()
            ->assertSee('Visible para mi')
            ->assertDontSee('Ajeno');
    }

    public function test_index_renders_for_an_empty_account(): void
    {
        $this->actingAs(User::factory()->create())->get('/prompts')
            ->assertOk()
            ->assertSee('Todavia no tienes prompts');
    }

    public function test_create_form_renders(): void
    {
        Categoria::create(['nombre' => 'Escritura']);

        $this->actingAs(User::factory()->create())->get('/prompts/create')
            ->assertOk()
            ->assertSee('Nuevo prompt')
            ->assertSee('Escritura');
    }

    public function test_show_edit_and_historial_render_for_the_owner(): void
    {
        $user = User::factory()->create();
        $prompt = $this->prompt($user);

        $this->actingAs($user)->get("/prompts/{$prompt->id}")->assertOk()->assertSee('Mi prompt');
        $this->actingAs($user)->get("/prompts/{$prompt->id}/edit")->assertOk()->assertSee('Editar prompt');
        $this->actingAs($user)->get("/prompts/{$prompt->id}/historial")->assertOk()->assertSee('Historial');
    }

    public function test_private_prompt_of_another_user_is_forbidden(): void
    {
        $prompt = $this->prompt(User::factory()->create());

        $this->actingAs(User::factory()->create())->get("/prompts/{$prompt->id}")->assertForbidden();
    }

    public function test_registration_lands_on_a_working_prompts_page(): void
    {
        Role::create(['nombre' => 'user', 'descripcion' => 'Usuario', 'nivel_acceso' => 10]);

        $response = $this->post('/register', [
            'name' => 'Nueva Persona',
            'email' => 'nueva@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'acepta_terminos' => '1',
        ]);

        $response->assertRedirect('/prompts');
        $this->get('/prompts')->assertOk();
    }
}
