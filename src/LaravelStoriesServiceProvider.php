<?php

namespace Mnaimjons\LaravelStories;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Mnaimjons\LaravelStories\Models\Story;
use Mnaimjons\LaravelStories\Policies\StoryPolicy;

class LaravelStoriesServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/stories.php' => config_path('stories.php'),
        ], 'config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'migrations');

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/routes/api.php');

        // Register policies
        $this->registerPolicies();
    }


    public function register()
    {
        // Регистрация команд
        $this->commands([
            Console\Commands\DeleteExpiredStories::class,
        ]);

        // Объединение конфигураций
        $this->mergeConfigFrom(__DIR__ . '/../config/stories.php', 'stories');
    }

    protected function registerPolicies()
    {
        Gate::policy(Story::class, StoryPolicy::class);
    }
}
