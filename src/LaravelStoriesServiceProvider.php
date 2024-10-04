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
        // Публикация конфигурации
        $this->publishes([
            __DIR__ . '/../config/stories.php' => config_path('stories.php'),
        ], 'config');

        // Загрузка миграций
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Загрузка маршрутов
        $this->loadRoutesFrom(__DIR__ . '/routes/api.php');

        // Регистрация политик
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
