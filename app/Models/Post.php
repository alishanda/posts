<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'status',
        'user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_ARCHIVED = 'archived';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_PUBLISHED,
            self::STATUS_ARCHIVED,
        ];
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function activeComments(): HasMany
    {
        return $this->hasMany(Comment::class)->where('status', Comment::STATUS_ACTIVE);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
