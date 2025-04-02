<?php

namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Tag;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $questions = Question::with(['user', 'tags'])
            ->withCount('answers')
            ->latest()
            ->paginate(10);

        return Inertia::render('Forum/Questions', [
            'questions' => $questions
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tags = Tag::all();
        return Inertia::render('Forum/CreateQuestion', [
            'tags' => $tags
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'tags' => 'required|array|min:1|max:5',
            'tags.*' => 'exists:tags,id'
        ]);

        $question = Question::create([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'user_id' => auth()->id(),
            'slug' => Str::slug($validated['title']),
            'views' => 0,
            'score' => 0,
            'is_answered' => false,
            'answers_count' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $question->tags()->attach($validated['tags']);

        // The activity recording is handled in the Question model's booted method

        return redirect()->route('questions.show', $question)
            ->with('success', 'Question created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Question $question)
    {
        // Load relationships
        $question->load(['user', 'tags', 'answers.user']);
        
        // Increment view count
        $question->increment('views');
        
        // Get related questions based on tags
        $relatedQuestions = Question::with(['user', 'tags'])
            ->withCount('answers')
            ->whereHas('tags', function ($query) use ($question) {
                $query->whereIn('tags.id', $question->tags->pluck('id'));
            })
            ->where('id', '!=', $question->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return Inertia::render('Forum/QuestionDetail', [
            'question' => $question,
            'relatedQuestions' => $relatedQuestions,
            'user' => auth()->user()
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Question $question)
    {
        $this->authorize('update', $question);
        
        $tags = Tag::all();
        $question->load('tags');

        return Inertia::render('Forum/EditQuestion', [
            'question' => $question,
            'tags' => $tags
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Question $question)
    {
        $this->authorize('update', $question);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'tags' => 'required|array',
            'tags.*' => 'exists:tags,id'
        ]);

        $question->update([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'slug' => Str::slug($validated['title'])
        ]);

        $question->tags()->sync($validated['tags']);

        return redirect()->route('questions.show', $question)
            ->with('success', 'Question updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Question $question)
    {
        $this->authorize('delete', $question);

        $question->delete();

        return redirect()->route('questions.index')
            ->with('success', 'Question deleted successfully.');
    }
}
