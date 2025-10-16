<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    public function test_user_can_create_post(): void
    {
        $postData = [
            'title' => 'Test Post',
            'content' => 'This is a test post content.',
            'status' => Post::STATUS_PUBLISHED,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/posts', $postData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'title',
                    'content',
                    'status',
                    'user',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post',
            'content' => 'This is a test post content.',
            'status' => Post::STATUS_PUBLISHED,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_user_can_update_own_post(): void
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Original Title',
            'content' => 'Original content',
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'content' => 'Updated content',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/posts/{$post->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Пост успешно обновлен']);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Title',
            'content' => 'Updated content',
        ]);
    }

    public function test_user_cannot_update_another_users_post(): void
    {
        $anotherUser = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $anotherUser->id]);

        $updateData = [
            'title' => 'Hacked Title',
            'content' => 'Hacked content',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/posts/{$post->id}", $updateData);

        $response->assertStatus(403)
            ->assertJson(['message' => 'Недостаточно прав для редактирования этого поста']);
    }

    public function test_user_can_delete_own_post(): void
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/posts/{$post->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Пост успешно удален']);

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_user_cannot_delete_another_users_post(): void
    {
        $anotherUser = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $anotherUser->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/posts/{$post->id}");

        $response->assertStatus(403)
            ->assertJson(['message' => 'Недостаточно прав для удаления этого поста']);
    }

    public function test_guest_can_view_posts(): void
    {
        Post::factory()->count(3)->create();

        $response = $this->getJson('/api/posts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'content',
                        'status',
                        'user',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    }

    public function test_user_can_get_own_posts(): void
    {
        Post::factory()->count(2)->create(['user_id' => $this->user->id]);
        Post::factory()->count(1)->create(); // Another user's post

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/my/posts');

        $response->assertStatus(200);
        $this->assertEquals(2, count($response->json('data')));
    }

    public function test_get_active_posts_by_user(): void
    {
        $user = User::factory()->create();
        Post::factory()->create([
            'user_id' => $user->id,
            'status' => Post::STATUS_PUBLISHED,
        ]);
        Post::factory()->create([
            'user_id' => $user->id,
            'status' => Post::STATUS_DRAFT,
        ]);

        $response = $this->getJson("/api/users/{$user->id}/posts/active");

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_post_creation_validation(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/posts', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'content']);
    }
}
