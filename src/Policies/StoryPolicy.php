<?php

namespace Mnaimjons\LaravelStories\Policies;

use Illuminate\Foundation\Auth\User;
use Mnaimjons\LaravelStories\Models\Story;

class StoryPolicy
{
    public function create(User $user, $storyable)
    {
        // Проверка прав на создание сторис
        return true;
    }

    public function view(User $user = null, Story $story)
    {
        // Все сторис доступны для просмотра
        return true;
    }
}
