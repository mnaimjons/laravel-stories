<?php

namespace Mnaimjons\LaravelStories\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Mnaimjons\LaravelStories\Models\Story;
use Mnaimjons\LaravelStories\Models\StoryComment;

class StoryController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'storyable_type' => 'required|string',
            'storyable_id' => 'required|integer',
            'title' => 'nullable|string',
            'content' => 'nullable|string',
            'media' => 'nullable|file|mimes:jpg,jpeg,png,mp4,mov,avi',
            'expires_at' => 'nullable|date',
        ]);

        $storyableModel = $request->input('storyable_type');
        $storyable = $storyableModel::findOrFail($request->input('storyable_id'));

        if (!Gate::allows('create', [Story::class, $storyable])) {
            abort(403);
        }

        $data = $request->only('title', 'content', 'expires_at');

        if (empty($data['expires_at'])) {
            $data['expires_at'] = now()->addHours(config('stories.default_expiration'));
        }

        $story = $storyable->stories()->create($data);

        if ($request->hasFile('media')) {
            $story
                ->addMediaFromRequest('media')
                ->toMediaCollection('stories');
        }

        return response()->json($story, 201);
    }

    public function index(Request $request)
    {
        $request->validate([
            'storyable_type' => 'required|string',
            'storyable_id' => 'required|integer',
        ]);

        $storyableModel = $request->input('storyable_type');
        $storyable = $storyableModel::findOrFail($request->input('storyable_id'));

        $stories = $storyable->stories()
            ->where('expires_at', '>', now())
            ->with(['media', 'views', 'comments', 'reactions'])
            ->get();

        return response()->json($stories);
    }

    public function viewStory(Request $request, $id)
    {
        $story = Story::with('storyable')->findOrFail($id);

        // Запись просмотра
        if ($request->user()) {
            $story->views()->firstOrCreate([
                'user_id' => $request->user()->id,
            ]);
        }

        return response()->json($story);
    }

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

    public function react(Request $request, $id)
    {
        $request->validate([
            'reaction' => 'required|string',
        ]);

        $story = Story::findOrFail($id);

        // Проверяем, не реагировал ли пользователь ранее
        $existingReaction = $story->reactions()->where('user_id', $request->user()->id)->first();

        if ($existingReaction) {
            // Обновляем существующую реакцию
            $existingReaction->update([
                'reaction' => $request->input('reaction'),
            ]);

            return response()->json($existingReaction, 200);
        } else {
            // Создаем новую реакцию
            $reaction = $story->reactions()->create([
                'user_id' => $request->user()->id,
                'reaction' => $request->input('reaction'),
            ]);

            return response()->json($reaction, 201);
        }
    }

}
