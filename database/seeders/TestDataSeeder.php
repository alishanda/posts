<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Post;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $post = Post::create([
            'title' => 'Test Post',
            'content' => 'This is a test post content.',
            'status' => Post::STATUS_PUBLISHED,
            'user_id' => $user->id,
        ]);

        $this->command->info('Test user created with ID: ' . $user->id);
        $this->command->info('Test post created with ID: ' . $post->id);
    }
}