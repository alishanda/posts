<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function activePosts(): HasMany
    {
        return $this->hasMany(Post::class)->where('status', Post::STATUS_PUBLISHED);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function activeComments(): HasMany
    {
        return $this->hasMany(Comment::class)->where('status', Comment::STATUS_ACTIVE);
    }

    public function activeCommentsToActivePosts(): HasMany
    {
        return $this->hasMany(Comment::class)
            ->where('status', Comment::STATUS_ACTIVE)
            ->whereHas('post', function ($query) {
                $query->where('status', Post::STATUS_PUBLISHED);
            });
    }
}
