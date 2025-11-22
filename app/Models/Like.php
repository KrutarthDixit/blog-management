<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'likeable_id',
        'likeable_type',
    ];

    /**
     * Get the owning likeable model (blog, comment, etc.).
     */
    public function likeable()
    {
        return $this->morphTo();
    }

    /**
     * The user who created the like.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
