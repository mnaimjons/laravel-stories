# Laravel Stories Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mnaimjons/laravel-stories.svg?style=flat-square)](https://packagist.org/packages/mnaimjons/laravel-stories)
[![Total Downloads](https://img.shields.io/packagist/dt/mnaimjons/laravel-stories.svg?style=flat-square)](https://packagist.org/packages/mnaimjons/laravel-stories)
[![License](https://img.shields.io/packagist/l/mnaimjons/laravel-stories.svg?style=flat-square)](https://packagist.org/packages/mnaimjons/laravel-stories)

The Laravel Stories Package provides "stories" functionality for any model in your Laravel application, similar to how it's implemented in social media platforms. The package supports:

- **Media uploads** (images and videos) using [Spatie Media Library](https://github.com/spatie/laravel-medialibrary).
- **Comments** on stories.
- **Reactions** to stories.
- **Tracking views** of stories.
- **Automatic deletion** of expired stories.

> **Note:** In the current version, all stories are visible to all users. Future releases will include privacy settings.

## Table of Contents

- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
    - [Adding the Trait to Your Model](#adding-the-trait-to-your-model)
    - [Creating Stories](#creating-stories)
    - [Viewing Stories](#viewing-stories)
    - [Adding Comments](#adding-comments)
    - [Adding Reactions](#adding-reactions)
    - [Tracking Views](#tracking-views)
- [API Routes](#api-routes)
- [Scheduling Tasks](#scheduling-tasks)
- [Customization](#customization)
- [Testing](#testing)
- [License](#license)
- [Contact](#contact)

## Installation

1. Install the package via Composer:

   ```bash
   composer require mnaimjons/laravel-stories
   ```

2. Publish and run the migrations:

   ```bash
   php artisan vendor:publish --provider="Mnaimjons\LaravelStories\LaravelStoriesServiceProvider" --tag="migrations"
   php artisan migrate
   ```

3. Publish the configuration:

   ```bash
   php artisan vendor:publish --provider="Mnaimjons\LaravelStories\LaravelStoriesServiceProvider" --tag="config"
   ```

4. Install Spatie Media Library (if not already installed):

   ```bash
   composer require spatie/laravel-medialibrary
   php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="migrations"
   php artisan migrate
   ```

## Configuration

The configuration file `config/stories.php` contains the package settings:

```php
return [
    'default_expiration' => 24, // Story lifetime in hours
];
```

You can change the default story expiration time by modifying the `default_expiration` value.

## Usage

### Adding the Trait to Your Model

Add the `HasStoriesTrait` trait and implement the `HasStories` interface in the model you want to associate stories with (e.g., the `User` model):

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Mnaimjons\LaravelStories\Contracts\HasStories;
use Mnaimjons\LaravelStories\Traits\HasStoriesTrait;

class User extends Authenticatable implements HasStories
{
    use HasStoriesTrait;

    // ...
}
```

### Creating Stories

You can create stories for any model that uses the `HasStoriesTrait`. Here's an example of creating a story for the currently authenticated user:

```php
$user = auth()->user();

$story = $user->stories()->create([
    'title' => 'My Story',
    'content' => 'This is my first story.',
    'expires_at' => now()->addHours(24), // Optional, defaults to 24 hours
]);

// Adding a media file (image or video)
$story
    ->addMediaFromRequest('media') // 'media' is the name of the file input field
    ->toMediaCollection('stories');
```

**Note:** Ensure your form includes a file input field named `media` and has the `multipart/form-data` encoding type.

### Viewing Stories

Retrieve active stories for a user:

```php
$stories = $user->stories()
    ->where('expires_at', '>', now())
    ->with(['media', 'views', 'comments', 'reactions'])
    ->get();
```

### Adding Comments

Users can leave comments on stories:

```php
$story = Story::find($storyId);

$story->comments()->create([
    'user_id' => auth()->id(),
    'comment' => 'Great story!',
]);
```

Or through a controller:

```php
public function comment(Request $request, $id)
{
    $request->validate([
        'comment' => 'required|string',
    ]);

    $story = Story::findOrFail($id);

    $comment = $story->comments()->create([
        'user_id' => $request->user()->id,
        'comment' => $request->input('comment'),
    ]);

    return response()->json($comment, 201);
}
```

### Adding Reactions

Users can react to stories (e.g., send emojis):

```php
$story = Story::find($storyId);

$story->reactions()->create([
    'user_id' => auth()->id(),
    'reaction' => '👍', // Or any other reaction type
]);
```

If a user has already reacted to this story, their reaction will be updated.

### Tracking Views

Each time a user views a story, you can record the view:

```php
$story = Story::find($storyId);

$story->views()->firstOrCreate([
    'user_id' => auth()->id(),
]);
```

Or automatically when displaying stories via a controller:

```php
public function viewStory(Request $request, $id)
{
    $story = Story::with('storyable')->findOrFail($id);

    // Record the view
    if ($request->user()) {
        $story->views()->firstOrCreate([
            'user_id' => $request->user()->id,
        ]);
    }

    return response()->json($story);
}
```

## API Routes

The package provides a set of API routes for working with stories. The routes are defined in `routes/api.php` and are automatically loaded by the service provider.

### List of Routes

| Method | Route                       | Action                                 |
|--------|-----------------------------|----------------------------------------|
| POST   | `/stories`                  | Create a new story                     |
| GET    | `/stories`                  | Get a list of active stories           |
| GET    | `/stories/{id}`             | View a specific story                  |
| POST   | `/stories/{id}/comment`     | Add a comment to a story               |
| POST   | `/stories/{id}/react`       | Add or update a reaction to a story    |

### API Usage Examples

#### Creating a Story

```http
POST /stories
Authorization: Bearer {token}

Body:
{
    "storyable_type": "App\\Models\\User",
    "storyable_id": 1,
    "title": "My Story",
    "content": "This is my first story.",
    "expires_at": "2023-10-31 12:00:00", // Optional
    "media": {file} // Multipart/Form-Data
}
```

#### Getting Active Stories

```http
GET /stories?storyable_type=App\\Models\\User&storyable_id=1
Authorization: Bearer {token}
```

#### Adding a Comment

```http
POST /stories/{id}/comment
Authorization: Bearer {token}

Body:
{
    "comment": "Great story!"
}
```

#### Adding a Reaction

```http
POST /stories/{id}/react
Authorization: Bearer {token}

Body:
{
    "reaction": "👍"
}
```

## Scheduling Tasks

To automatically delete expired stories and associated media files, add the cleanup command to your task scheduler in `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('stories:cleanup')->hourly();
}
```

The `stories:cleanup` command deletes all stories that have expired and cleans up associated media collections.

## Customization

### Adjusting Story Expiration Time

You can change the default story expiration time by modifying the `default_expiration` value in `config/stories.php`:

```php
'default_expiration' => 48, // Story lifetime in hours
```

### Extending Models

If you need to extend the functionality of the package's models (e.g., `Story`, `StoryComment`, `StoryReaction`), you can create your own models and configure the package to use them.

### Adding Privacy Settings

In the current version, all stories are visible to all users. You can extend the package by adding privacy settings to the `Story` model and updating access policies.

## Testing

It is recommended to cover your code with tests to ensure the package works correctly in your application.

### Example Test

```php
public function test_user_can_create_story()
{
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/stories', [
            'storyable_type' => 'App\\Models\\User',
            'storyable_id' => $user->id,
            'title' => 'Test Story',
            'content' => 'Test story content',
        ])
        ->assertStatus(201)
        ->assertJson([
            'title' => 'Test Story',
            'content' => 'Test story content',
        ]);
}
```

## License

The package is open-sourced software licensed under the [MIT license](LICENSE).

## Contact

- **Author**: Mnaimjons
- **Email**: [mnaimjons@gmail.com](mailto:mnaimjons@gmail.com)
- **GitHub**: [github.com/mnaimjons](https://github.com/mnaimjons)

## Thank You

- **Spatie** for their excellent [Laravel Media Library](https://github.com/spatie/laravel-medialibrary) package.
- **Laravel Community** for their contributions to the framework.

## Feedback

If you have any questions or suggestions, please create an [issue on GitHub](https://github.com/mnaimjons/laravel-stories/issues) or submit a Pull Request.

## Conclusion

The Laravel Stories Package provides a powerful and flexible way to add stories functionality to your application. It integrates easily with existing models and allows users to interact through stories, comments, and reactions.

Start using it today to enhance user engagement in your application!

# Key Features

- **Easy Integration**: Easily attach to any model in your application.
- **Flexible Configuration**: Customize story lifetime and extend functionality.
- **Media Support**: Uses Spatie Media Library for handling images and videos.
- **User Interaction**: Supports comments and reactions on stories.
- **Automatic Maintenance**: Provides a command to clean up expired stories.

# Requirements

- **PHP**: >=7.4
- **Laravel**: ^8.0|^9.0|^10.0
- **Spatie Media Library**: ^10.0

# Development Installation

1. Clone the repository:

   ```bash
   git clone https://github.com/mnaimjons/laravel-stories.git
   ```

2. Install dependencies:

   ```bash
   composer install
   ```

3. Link the package in your application:

   In your application's `composer.json`, add:

   ```json
   "repositories": [
       {
           "type": "path",
           "url": "../laravel-stories"
       }
   ]
   ```

   Then require the package:

   ```bash
   composer require mnaimjons/laravel-stories:@dev
   ```

# Contribution

We welcome contributions from the community! If you'd like to contribute to the development of the package, please:

- Fork the repository.
- Create a new branch for your feature (`git checkout -b feature/my-feature`).
- Make your changes and commit them (`git commit -am 'Add new feature'`).
- Push your changes to the repository (`git push origin feature/my-feature`).
- Create a Pull Request.

---

**Thank you for using the Laravel Stories Package!**

---