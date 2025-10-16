<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'content' => 'Test comment content',
            'status' => Comment::STATUS_ACTIVE,
            'user_id' => User::factory(),
            'post_id' => Post::factory(),
            'parent_id' => null,
        ];
    }

    /**
     * Indicate that the comment is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Comment::STATUS_PENDING,
        ]);
    }

    /**
     * Indicate that the comment is a reply.
     */
    public function reply(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => Comment::factory(),
        ]);
    }
}