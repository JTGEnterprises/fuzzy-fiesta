<?php

namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Question;
use App\Models\Answer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        $users = User::withCount(['questions', 'answers'])
            ->orderBy('reputation', 'desc')
            ->paginate(20);

        return Inertia::render('Forum/Users', [
            'users' => $users
        ]);
    }

    public function questions(User $user = null)
    {
        if (!$user) {
            $user = auth()->user();
        }

        $questions = Question::with(['tags', 'user'])
            ->withCount('answers')
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        return Inertia::render('Forum/UserQuestions', [
            'questions' => $questions,
            'profileUser' => $user
        ]);
    }

    public function answers(User $user = null)
    {
        if (!$user) {
            $user = auth()->user();
        }

        $answers = Answer::with(['question', 'user'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        return Inertia::render('Forum/UserAnswers', [
            'answers' => $answers,
            'profileUser' => $user
        ]);
    }

    public function show(User $user)
    {
        $questions = Question::with(['tags', 'user'])
            ->withCount('answers')
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(5);

        $answers = Answer::with(['question', 'user'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(5);

        $stats = [
            'questions_count' => Question::where('user_id', $user->id)->count(),
            'answers_count' => Answer::where('user_id', $user->id)->count(),
            'reputation' => $user->reputation,
        ];

        return Inertia::render('Forum/UserProfile', [
            'user' => $user,
            'questions' => $questions,
            'answers' => $answers,
            'stats' => $stats
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'location' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
        ]);

        auth()->user()->update($validated);

        return redirect()->route('profile.edit')
            ->with('success', 'Profile updated successfully.');
    }
} 