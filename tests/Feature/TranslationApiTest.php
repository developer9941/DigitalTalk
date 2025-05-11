<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Translation;
class TranslationApiTest extends TestCase
{
    use RefreshDatabase;
    public function test_user_can_create_translation()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/translations', [
            'key' => 'greeting',
            'content' => 'Hello',
            'locale' => 'en',
            'tags' => ['web']
        ]);

        $response->assertStatus(201)->assertJsonFragment(['key' => 'greeting']);
    }

    public function test_user_can_view_translations()
    {
        $user = User::factory()->create();

        Translation::factory()->count(5)->create();

        $response = $this->actingAs($user)->getJson('/api/translations');

        $response->assertStatus(200)->assertJsonStructure(['data']);
    }

    public function test_user_can_search_by_tag()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/api/translations', [
            'key' => 'welcome',
            'content' => 'Welcome',
            'locale' => 'en',
            'tags' => ['mobile']
        ]);

        $response = $this->actingAs($user)->getJson('/api/translations/search?tag=mobile');

        $response->assertStatus(200)->assertJsonFragment(['key' => 'welcome']);
    }

    public function test_json_export_works()
    {
        $user = User::factory()->create();

        Translation::factory()->create(['key' => 'bye', 'content' => 'Goodbye', 'locale' => 'en']);

        $response = $this->actingAs($user)->getJson('/api/translations/export/en');

        $response->assertStatus(200)->assertJsonFragment(['bye' => 'Goodbye']);
    }

}
