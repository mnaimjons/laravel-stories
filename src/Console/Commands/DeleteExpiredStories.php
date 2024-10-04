<?php

namespace Mnaimjons\LaravelStories\Console\Commands;

use Illuminate\Console\Command;
use Mnaimjons\LaravelStories\Models\Story;

class DeleteExpiredStories extends Command
{
    protected $signature = 'stories:cleanup';
    protected $description = 'Delete expired stories and associated media';

    public function handle()
    {
        $stories = Story::where('expires_at', '<', now())->get();
        $deletedCount = 0;

        foreach ($stories as $story) {
            // Удаляем связанные медиа-файлы
            $story->clearMediaCollection('stories');

            // Удаляем сторис
            $story->delete();

            $deletedCount++;
        }

        $this->info("Deleted {$deletedCount} expired stories.");
    }
}
