<?php

namespace Tests\Feature;

use App\Models\Prompt;
use App\Models\User;
use App\Models\Version;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromptVersionTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_prompt_stores_version_number_one(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/prompts', [
            'titulo' => 'Resumen',
            'contenido' => 'Resume este texto',
        ]);

        $prompt = Prompt::firstOrFail();
        $response->assertRedirect(route('prompts.show', $prompt));
        $this->assertDatabaseHas('versiones', ['prompt_id' => $prompt->id, 'numero' => 1]);
        $this->assertSame(1, Version::where('prompt_id', $prompt->id)->count());
    }

    public function test_editing_content_creates_a_new_version(): void
    {
        $user = User::factory()->create();
        $prompt = Prompt::create([
            'user_id' => $user->id, 'titulo' => 'T', 'contenido' => 'v1', 'version_actual' => 1,
        ]);

        $this->actingAs($user)->put("/prompts/{$prompt->id}", [
            'titulo' => 'T', 'contenido' => 'v2', 'motivo_cambio' => 'mejora',
        ])->assertRedirect(route('prompts.show', $prompt));

        $this->assertDatabaseHas('versiones', ['prompt_id' => $prompt->id, 'numero' => 2, 'contenido' => 'v2']);
        $this->assertSame(2, $prompt->fresh()->version_actual);
    }

    public function test_restoring_a_version_creates_a_new_numbered_version(): void
    {
        $user = User::factory()->create();
        $prompt = Prompt::create([
            'user_id' => $user->id, 'titulo' => 'T', 'contenido' => 'v2', 'version_actual' => 2,
        ]);
        $v1 = Version::create(['prompt_id' => $prompt->id, 'numero' => 1, 'contenido' => 'v1']);

        $this->actingAs($user)
            ->post("/prompts/{$prompt->id}/versiones/{$v1->id}/restaurar")
            ->assertRedirect(route('prompts.show', $prompt));

        $this->assertDatabaseHas('versiones', ['prompt_id' => $prompt->id, 'numero' => 3, 'contenido' => 'v1']);
        $this->assertSame('v1', $prompt->fresh()->contenido);
    }
}
