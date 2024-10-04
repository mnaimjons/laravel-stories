<?php

use Illuminate\Support\Facades\Route;
use Mnaimjons\LaravelStories\Http\Controllers\StoryController;

Route::middleware('auth:api')->group(function () {
    Route::post('/stories', [StoryController::class, 'store'])->name('stories.store');
    Route::get('/stories', [StoryController::class, 'index'])->name('stories.index');
    Route::get('/stories/{id}', [StoryController::class, 'viewStory'])->name('stories.view');
    Route::post('/stories/{id}/comment', [StoryController::class, 'comment'])->name('stories.comment');
    Route::post('/stories/{id}/react', [StoryController::class, 'react'])->name('stories.react');
});
