<?php

namespace Mnaimjons\LaravelStories\Models;

use Illuminate\Database\Eloquent\Model;

class StoryReaction extends Model
{
    protected $fillable = [
        'story_id',
        'user_id',
        'reaction', // Тип реакции, например, emoji или предопределенные реакции
    ];

    public function story()
    {
        return $this->belongsTo(Story::class);
    }

    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }
}
