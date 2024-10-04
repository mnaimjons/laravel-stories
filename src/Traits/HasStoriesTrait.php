<?php

namespace Mnaimjons\LaravelStories\Traits;

use Mnaimjons\LaravelStories\Models\Story;

trait HasStoriesTrait
{
    public function stories()
    {
        return $this->morphMany(Story::class, 'storyable');
    }
}
