<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\Like;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Blog extends Model implements HasMedia
{
    use InteractsWithMedia, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'status',
        'content',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'title' => 'string',
            'content' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Attributes to include when the model is converted to array / JSON.
     */
    protected $appends = ['likes_count', 'liked_by_auth_user', 'image_url'];

    /**
     * Get all of the model's likes.
     */
    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    /**
     * Check if the given user (model or id) has liked this blog.
     */
    public function isLikedBy($user): bool
    {
        $userId = is_int($user) ? $user : ($user->id ?? null);
        if (! $userId) {
            return false;
        }

        return $this->likes()->where('user_id', $userId)->exists();
    }

    /**
     * Accessor for `likes_count`.
     *
     * @return int
     */
    public function getLikesCountAttribute(): int
    {
        if (array_key_exists('likes_count', $this->attributes) && $this->attributes['likes_count'] !== null) {
            return (int) $this->attributes['likes_count'];
        }

        return $this->likes()->count();
    }

    /**
     * Accessor for `liked_by_auth_user`.
     *
     * @return bool
     */
    public function getLikedByAuthUserAttribute(): bool
    {
        $userId = auth()->id();
        if (! $userId) {
            return false;
        }

        if ($this->relationLoaded('likes')) {
            return $this->likes->contains('user_id', $userId);
        }

        return $this->likes()->where('user_id', $userId)->exists();
    }

    /**
     * Accessor for `image_url`.
     */
    public function getImageUrlAttribute(): ?string
    {
        $url = $this->getFirstMediaUrl('images');

        return $url === '' ? null : $url;
    }
}
