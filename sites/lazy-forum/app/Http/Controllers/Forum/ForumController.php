<?php

namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Answer;
use App\Models\Tag;
use App\Models\Activity;
use App\Models\Bookmark;
use App\Models\Vote;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ForumController extends Controller
{
    public function home(Request $request)
    {
        $sort = $request->input('sort', 'newest');
        
        $questionsQuery = Question::with(['tags', 'user'])
            ->withCount('answers');
        
        // Apply sorting
        switch ($sort) {
            case 'active':
                $questionsQuery->orderBy('updated_at', 'desc');
                break;
            case 'votes':
                $questionsQuery->orderBy('score', 'desc');
                break;
            case 'answers':
                $questionsQuery->orderBy('answers_count', 'desc');
                break;
            case 'newest':
            default:
                $questionsQuery->orderBy('created_at', 'desc');
                break;
        }
        
        $questions = $questionsQuery->paginate(10);

        $featuredQuestions = Question::with(['tags', 'user'])
            ->withCount('answers')
            ->orderBy('score', 'desc')
            ->take(5)
            ->get();

        $topTags = Tag::withCount('questions')
            ->orderBy('questions_count', 'desc')
            ->take(10)
            ->get();

        $recentActivity = Activity::with('user')
            ->latest()
            ->take(10)
            ->get();

        return Inertia::render('Forum/Home', [
            'questions' => $questions,
            'featuredQuestions' => $featuredQuestions,
            'topTags' => $topTags,
            'recentActivity' => $recentActivity,
            'currentSort' => $sort
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->input('search');
        
        if (empty($query)) {
            return redirect()->route('questions.index');
        }
        
        $questions = Question::with(['tags', 'user'])
            ->withCount('answers')
            ->where(function($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('body', 'like', "%{$query}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->appends(['search' => $query]);

        return Inertia::render('Forum/Questions', [
            'questions' => $questions,
            'search' => $query,
        ]);
    }

    public function bookmark(Request $request, Question $question)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $bookmark = Bookmark::where('user_id', $user->id)
            ->where('question_id', $question->id)
            ->first();

        if ($bookmark) {
            $bookmark->delete();
            return response()->json(['bookmarked' => false]);
        }

        Bookmark::create([
            'user_id' => $user->id,
            'question_id' => $question->id,
        ]);

        return response()->json(['bookmarked' => true]);
    }

    public function bookmarks(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $bookmarks = Bookmark::with('question')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return response()->json($bookmarks);
    }

    public function vote(Request $request, $type, $id)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $voteType = $request->input('vote_type');
        
        if ($type === 'question') {
            $item = Question::findOrFail($id);
        } else {
            $item = Answer::findOrFail($id);
        }

        // Check if user has already voted
        $existingVote = Vote::where('user_id', $user->id)
            ->where('votable_type', get_class($item))
            ->where('votable_id', $item->id)
            ->first();

        if ($existingVote) {
            if ($existingVote->vote_type === $voteType) {
                // Remove vote if clicking the same button
                $existingVote->delete();
                $item->decrement('score', $voteType);
            } else {
                // Update vote if changing vote type
                $item->decrement('score', $existingVote->vote_type);
                $item->increment('score', $voteType);
                $existingVote->update(['vote_type' => $voteType]);
            }
        } else {
            // Create new vote
            Vote::create([
                'user_id' => $user->id,
                'votable_type' => get_class($item),
                'votable_id' => $item->id,
                'vote_type' => $voteType,
            ]);
            $item->increment('score', $voteType);
        }

        return response()->json([
            'score' => $item->fresh()->score,
        ]);
    }
} 