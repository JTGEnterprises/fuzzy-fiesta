<?php

namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::withCount('questions')
            ->orderBy('questions_count', 'desc')
            ->paginate(20);

        return Inertia::render('Forum/Tags', [
            'tags' => $tags
        ]);
    }

    public function show(Tag $tag)
    {
        $questions = $tag->questions()
            ->with(['user', 'tags'])
            ->withCount('answers')
            ->latest()
            ->paginate(10);

        return Inertia::render('Forum/TagDetail', [
            'tag' => $tag,
            'questions' => $questions
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:tags,name',
            'description' => 'nullable|string|max:255'
        ]);

        $tag = Tag::create($validated);

        return redirect()->route('tags.show', $tag)
            ->with('success', 'Tag created successfully.');
    }

    public function update(Request $request, Tag $tag)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:tags,name,' . $tag->id,
            'description' => 'nullable|string|max:255'
        ]);

        $tag->update($validated);

        return redirect()->route('tags.show', $tag)
            ->with('success', 'Tag updated successfully.');
    }

    public function destroy(Tag $tag)
    {
        $tag->delete();

        return redirect()->route('tags.index')
            ->with('success', 'Tag deleted successfully.');
    }
} 