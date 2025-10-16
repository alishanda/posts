<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
        $this->post = new Post([
            'title' => 'Test Post',
            'content' => 'Test content',
            'status' => Post::STATUS_PUBLISHED,
            'user_id' => $this->user->id,
        ]);
        $this->post->save();
    }

    public function test_user_can_create_comment(): void
    {
        $commentData = [
            'content' => 'This is a test comment.',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/comments/' . $this->post->id, $commentData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'content',
                    'status',
                    'user',
                    'post',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('comments', [
            'content' => 'This is a test comment.',
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
            'status' => Comment::STATUS_PENDING,
        ]);
    }

    public function test_user_can_reply_to_comment(): void
    {
        $parentComment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
        ]);

        $replyData = [
            'content' => 'This is a reply.',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/comments/' . $parentComment->id . '/reply', $replyData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('comments', [
            'content' => 'This is a reply.',
            'post_id' => $this->post->id,
            'parent_id' => $parentComment->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_user_cannot_comment_on_inactive_post(): void
    {
        $draftPost = Post::factory()->create([
            'status' => Post::STATUS_DRAFT,
        ]);

        $commentData = [
            'content' => 'This should not work.',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/comments/' . $draftPost->id, $commentData);

        $response->assertStatus(403)
            ->assertJson(['message' => 'Нельзя комментировать неактивные посты']);
    }

    public function test_user_can_update_own_comment(): void
    {
        $comment = Comment::factory()->create([
            'user_id' => $this->user->id,
            'post_id' => $this->post->id,
            'content' => 'Original comment',
        ]);

        $updateData = [
            'content' => 'Updated comment',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/comments/{$comment->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Комментарий успешно обновлен']);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Updated comment',
        ]);
    }

    public function test_user_cannot_update_another_users_comment(): void
    {
        $anotherUser = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $anotherUser->id,
            'post_id' => $this->post->id,
        ]);

        $updateData = [
            'content' => 'Hacked comment',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/comments/{$comment->id}", $updateData);

        $response->assertStatus(403)
            ->assertJson(['message' => 'Недостаточно прав для редактирования этого комментария']);
    }

    public function test_user_can_delete_own_comment(): void
    {
        $comment = Comment::factory()->create([
            'user_id' => $this->user->id,
            'post_id' => $this->post->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Комментарий успешно удален']);

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_user_cannot_delete_another_users_comment(): void
    {
        $anotherUser = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $anotherUser->id,
            'post_id' => $this->post->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(403)
            ->assertJson(['message' => 'Недостаточно прав для удаления этого комментария']);
    }

    public function test_guest_can_view_comments(): void
    {
        Comment::factory()->count(3)->create([
            'post_id' => $this->post->id,
            'status' => Comment::STATUS_ACTIVE,
        ]);

        $response = $this->getJson('/api/comments');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'content',
                        'status',
                        'user',
                        'post',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    }

    public function test_get_comments_by_post(): void
    {
        Comment::factory()->count(2)->create([
            'post_id' => $this->post->id,
            'status' => Comment::STATUS_ACTIVE,
        ]);
        Comment::factory()->count(1)->create([
            'status' => Comment::STATUS_ACTIVE,
        ]); // Comment for another post

        $response = $this->getJson("/api/posts/{$this->post->id}/comments");

        $response->assertStatus(200);
        $this->assertEquals(2, count($response->json('data')));
    }

    public function test_get_user_own_comments(): void
    {
        Comment::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'post_id' => $this->post->id,
        ]);
        Comment::factory()->count(1)->create([
            'post_id' => $this->post->id,
        ]); // Another user's comment

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/my/comments');

        $response->assertStatus(200);
        $this->assertEquals(2, count($response->json('data')));
    }

    public function test_get_active_comments_by_user(): void
    {
        $user = User::factory()->create();
        Comment::factory()->create([
            'user_id' => $user->id,
            'post_id' => $this->post->id,
            'status' => Comment::STATUS_ACTIVE,
        ]);
        Comment::factory()->create([
            'user_id' => $user->id,
            'post_id' => $this->post->id,
            'status' => Comment::STATUS_PENDING,
        ]);

        $response = $this->getJson("/api/users/{$user->id}/comments/active");

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_get_replies(): void
    {
        $parentComment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
        ]);

        Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
            'parent_id' => $parentComment->id,
            'status' => Comment::STATUS_ACTIVE,
        ]);

        Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
            'status' => Comment::STATUS_ACTIVE,
        ]); // Regular comment

        $response = $this->getJson('/api/comments/replies');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_comment_creation_validation(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/comments/' . $this->post->id, []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }
}
