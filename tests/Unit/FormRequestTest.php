<?php

namespace Tests\Unit;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class FormRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_request_validation(): void
    {
        $rules = (new RegisterRequest())->rules();
        $validator = Validator::make([], $rules);

        // Test required fields
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());

        // Test valid data
        $validData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertFalse($validator->fails());

        // Test duplicate email
        User::factory()->create(['email' => 'test@example.com']);
        $validator = Validator::make($validData, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());

        // Test password confirmation mismatch
        $invalidData = $validData;
        $invalidData['password_confirmation'] = 'different';
        $validator = Validator::make($invalidData, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    public function test_login_request_validation(): void
    {
        $rules = (new LoginRequest())->rules();
        $validator = Validator::make([], $rules);

        // Test required fields
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());

        // Test valid data
        $validData = [
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertFalse($validator->fails());

        // Test invalid email format
        $invalidData = $validData;
        $invalidData['email'] = 'invalid-email';
        $validator = Validator::make($invalidData, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    public function test_store_post_request_validation(): void
    {
        $rules = (new StorePostRequest())->rules();
        $validator = Validator::make([], $rules);

        // Test required fields
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('title', $validator->errors()->toArray());
        $this->assertArrayHasKey('content', $validator->errors()->toArray());

        // Test valid data
        $validData = [
            'title' => 'Test Post',
            'content' => 'This is test content',
            'status' => Post::STATUS_PUBLISHED,
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertFalse($validator->fails());

        // Test invalid status
        $invalidData = $validData;
        $invalidData['status'] = 'invalid-status';
        $validator = Validator::make($invalidData, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());

        // Test title too long
        $invalidData = $validData;
        $invalidData['title'] = str_repeat('a', 256);
        $validator = Validator::make($invalidData, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('title', $validator->errors()->toArray());
    }

    public function test_update_post_request_validation(): void
    {
        $rules = (new UpdatePostRequest())->rules();

        // Test that all fields are optional
        $validator = Validator::make([], $rules);
        $this->assertFalse($validator->fails());

        // Test valid data
        $validData = [
            'title' => 'Updated Post',
            'content' => 'This is updated content',
            'status' => Post::STATUS_DRAFT,
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertFalse($validator->fails());

        // Test invalid status
        $invalidData = $validData;
        $invalidData['status'] = 'invalid-status';
        $validator = Validator::make($invalidData, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    public function test_store_comment_request_validation(): void
    {
        $rules = (new StoreCommentRequest())->rules();
        $validator = Validator::make([], $rules);

        // Test required fields
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('content', $validator->errors()->toArray());

        // Test valid data
        $validData = [
            'content' => 'This is a test comment',
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertFalse($validator->fails());

        // Test with parent comment
        $user = User::factory()->create();
        $post = new Post([
            'title' => 'Test Post',
            'content' => 'Test content',
            'status' => Post::STATUS_PUBLISHED,
            'user_id' => $user->id,
        ]);
        $post->save();
        $parentComment = new Comment([
            'content' => 'Parent comment',
            'status' => Comment::STATUS_ACTIVE,
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);
        $parentComment->save();
        $validData['parent_id'] = $parentComment->id;
        $validator = Validator::make($validData, $rules);
        $this->assertFalse($validator->fails());

        // Test invalid parent_id
        $invalidData = $validData;
        $invalidData['parent_id'] = 99999;
        $validator = Validator::make($invalidData, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('parent_id', $validator->errors()->toArray());

        // Test content too long
        $invalidData = $validData;
        $invalidData['content'] = str_repeat('a', 1001);
        $validator = Validator::make($invalidData, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('content', $validator->errors()->toArray());
    }

    public function test_update_comment_request_validation(): void
    {
        $rules = (new UpdateCommentRequest())->rules();
        $validator = Validator::make([], $rules);

        // Test required content field
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('content', $validator->errors()->toArray());

        // Test valid data
        $validData = [
            'content' => 'This is updated comment content',
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertFalse($validator->fails());

        // Test content too long
        $invalidData = $validData;
        $invalidData['content'] = str_repeat('a', 1001);
        $validator = Validator::make($invalidData, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('content', $validator->errors()->toArray());
    }
}
