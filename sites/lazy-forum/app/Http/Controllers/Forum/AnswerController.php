<?php

namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Question;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Activity;

class AnswerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage from question detail page.
     */
    public function storeForQuestion(Request $request, Question $question)
    {
        $validated = $request->validate([
            'body' => 'required|string'
        ]);

        $answer = $question->answers()->create([
            'body' => $validated['body'],
            'user_id' => auth()->id(),
            'score' => 0
        ]);

        // Record activity (similar to Stack Overflow)
        Activity::log(
            auth()->id(),
            'answered',
            $question->title,
            route('questions.show', $question)
        );

        // Give reputation for posting an answer (like Stack Overflow)
        auth()->user()->increment('reputation', 1);

        // Increment answer count on the question
        $question->increment('answers_count');

        return redirect()->route('questions.show', $question)
            ->with('success', 'Answer posted successfully.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'body' => 'required|string',
            'question_id' => 'required|exists:questions,id'
        ]);

        $question = Question::findOrFail($validated['question_id']);
        
        $answer = $question->answers()->create([
            'body' => $validated['body'],
            'user_id' => auth()->id(),
            'score' => 0
        ]);

        // Record activity (similar to Stack Overflow)
        Activity::log(
            auth()->id(),
            'answered',
            $question->title,
            route('questions.show', $question)
        );

        // Give reputation for posting an answer (like Stack Overflow)
        auth()->user()->increment('reputation', 1);

        // Increment answer count on the question
        $question->increment('answers_count');

        return redirect()->route('questions.show', $question)
            ->with('success', 'Answer posted successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Answer $answer)
    {
        $this->authorize('update', $answer);

        $validated = $request->validate([
            'body' => 'required|string'
        ]);

        $answer->update($validated);

        return redirect()->route('questions.show', $answer->question)
            ->with('success', 'Answer updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Answer $answer)
    {
        $this->authorize('delete', $answer);

        $question = $answer->question;
        $answer->delete();

        return redirect()->route('questions.show', $question)
            ->with('success', 'Answer deleted successfully.');
    }

    /**
     * Accept an answer as the solution to a question.
     */
    public function accept(Answer $answer)
    {
        $this->authorize('accept', $answer);

        // Unaccept any previously accepted answers
        $previouslyAccepted = $answer->question->answers()
            ->where('is_accepted', true)
            ->where('id', '!=', $answer->id)
            ->first();

        if ($previouslyAccepted) {
            $previouslyAccepted->unaccept();
        }

        // Accept this answer
        $answer->accept();

        // Record activity
        Activity::log(
            auth()->id(),
            'accepted an answer to',
            $answer->question->title,
            route('questions.show', $answer->question)
        );

        return redirect()->route('questions.show', $answer->question)
            ->with('success', 'Answer accepted successfully.');
    }
}
