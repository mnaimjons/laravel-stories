<?php

namespace Mnaimjons\LaravelStories\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Story extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'title',
        'content',
        'expires_at',
    ];

    protected $dates = [
        'expires_at',
    ];

    public function storyable(): MorphTo
    {
        return $this->morphTo();
    }

    public function views(): HasMany
    {
        return $this->hasMany(StoryView::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(StoryComment::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(StoryReaction::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
