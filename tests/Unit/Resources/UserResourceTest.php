<?php

namespace Tests\Unit\Resources;

use App\Http\Resources\UserResource;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_serializa_avatar_url_resuelta_a_url_publica(): void
    {
        Storage::fake('public');
        $user = User::factory()->create([
            'avatar_url' => 'avatars/42/yo.jpg',
            'fecha_registro' => Carbon::parse('2026-01-01'),
        ]);

        $payload = (new UserResource($user))->toArray(new Request());

        $this->assertNotNull($payload['avatar_url']);
        $this->assertStringContainsString('avatars/42/yo.jpg', $payload['avatar_url']);
    }

    public function test_avatar_url_es_null_si_usuario_no_tiene_avatar(): void
    {
        $user = User::factory()->create(['avatar_url' => null]);

        $payload = (new UserResource($user))->toArray(new Request());

        $this->assertNull($payload['avatar_url']);
    }

    public function test_avatar_url_externa_se_pasa_tal_cual_sin_anteponer_storage(): void
    {
        $user = User::factory()->create([
            'avatar_url' => 'https://cdn.example.com/yo.jpg',
        ]);

        $payload = (new UserResource($user))->toArray(new Request());

        $this->assertSame('https://cdn.example.com/yo.jpg', $payload['avatar_url']);
    }

    public function test_no_expone_contrasena_hash_en_payload(): void
    {
        $user = User::factory()->create();

        $payload = (new UserResource($user))->toArray(new Request());

        $this->assertArrayNotHasKey('contrasena_hash', $payload);
    }
}
