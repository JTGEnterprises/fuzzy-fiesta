<?php

namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Answer;
use Illuminate\Http\Request;
use App\Models\Activity;

class VoteController extends Controller
{
    public function store(Request $request, string $type, int $id)
    {
        $user = auth()->user();

        // Check if user has enough reputation to vote
        $voteType = $request->input('vote_type', 1); // 1 for upvote, -1 for downvote
        
        if ($voteType < 0 && !$user->hasEnoughReputationTo('downvote')) {
            return response()->json([
                'error' => 'You need at least 125 reputation to downvote'
            ], 403);
        }
        
        if ($voteType > 0 && !$user->hasEnoughReputationTo('upvote')) {
            return response()->json([
                'error' => 'You need at least 15 reputation to upvote'
            ], 403);
        }

        if ($type === 'question') {
            $model = Question::findOrFail($id);
        } else if ($type === 'answer') {
            $model = Answer::findOrFail($id);
        } else {
            return response()->json(['error' => 'Invalid vote type'], 400);
        }

        // Prevent users from voting on their own content
        if ($model->user_id === $user->id) {
            return response()->json(['error' => 'You cannot vote on your own content'], 403);
        }

        // Check if user has already voted
        $existingVote = $model->votes()
            ->where('user_id', $user->id)
            ->first();

        // Get the content owner
        $contentOwner = $model->user;

        if ($existingVote) {
            if ($existingVote->vote_type === $voteType) {
                // Remove vote if clicking the same button
                $existingVote->delete();
                $model->decrement('score', $voteType);
                
                // Update reputation of the content owner
                if ($voteType > 0) {
                    // Removing upvote, remove 10 reputation
                    $contentOwner->decrement('reputation', 10);
                } else {
                    // Removing downvote, add 2 reputation back
                    $contentOwner->increment('reputation', 2);
                    // Return 1 reputation to the voter
                    $user->increment('reputation', 1);
                }
                
                // Record activity
                Activity::log(
                    $user->id,
                    'removed ' . ($voteType > 0 ? 'an upvote' : 'a downvote') . ' from',
                    $type === 'question' ? $model->title : 'an answer to ' . $model->question->title,
                    $type === 'question' ? route('questions.show', $model) : route('questions.show', $model->question)
                );
            } else {
                // Change vote if clicking different button
                $existingVote->update(['vote_type' => $voteType]);
                
                // This means changing from -1 to 1 or 1 to -1, so we need to apply double the score change
                $model->increment('score', $voteType * 2); 
                
                // Update reputation - first reverting the old vote, then applying the new one
                if ($existingVote->vote_type < 0) {
                    // Was downvote, now upvote
                    $contentOwner->increment('reputation', 12); // +2 for removing downvote, +10 for upvote
                    $user->increment('reputation', 1); // Refund rep for downvoting
                } else {
                    // Was upvote, now downvote
                    $contentOwner->decrement('reputation', 12); // -10 for removing upvote, -2 for downvote
                    $user->decrement('reputation', 1); // Cost for downvoting
                }
                
                // Record activity
                Activity::log(
                    $user->id,
                    'changed vote on',
                    $type === 'question' ? $model->title : 'an answer to ' . $model->question->title,
                    $type === 'question' ? route('questions.show', $model) : route('questions.show', $model->question)
                );
            }
        } else {
            // Create new vote
            $model->votes()->create([
                'user_id' => $user->id,
                'vote_type' => $voteType
            ]);
            
            $model->increment('score', $voteType);
            
            // Update reputation
            if ($voteType > 0) {
                // Upvote gives +10 reputation to post owner
                $contentOwner->increment('reputation', 10);
            } else {
                // Downvote reduces reputation of post owner by 2
                $contentOwner->decrement('reputation', 2);
                // Downvoting costs 1 reputation to the voter
                $user->decrement('reputation', 1);
            }
            
            // Record activity
            Activity::log(
                $user->id,
                $voteType > 0 ? 'upvoted' : 'downvoted',
                $type === 'question' ? $model->title : 'an answer to ' . $model->question->title,
                $type === 'question' ? route('questions.show', $model) : route('questions.show', $model->question)
            );
        }

        return response()->json([
            'score' => $model->fresh()->score
        ]);
    }
}
