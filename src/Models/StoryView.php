<?php

namespace Mnaimjons\LaravelStories\Models;

use Illuminate\Database\Eloquent\Model;

class StoryView extends Model
{
    protected $fillable = [
        'story_id',
        'user_id',
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
