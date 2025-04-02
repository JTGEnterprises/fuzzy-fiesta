<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    use HasFactory;

    protected $fillable = [
        'body',
        'user_id',
        'question_id',
        'is_accepted',
        'score'
    ];

    protected $casts = [
        'is_accepted' => 'boolean',
    ];

    /**
     * Get the question that this answer belongs to.
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get the user who wrote this answer.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the votes for this answer.
     */
    public function votes()
    {
        return $this->morphMany(Vote::class, 'votable');
    }

    /**
     * Accept this answer.
     */
    public function accept()
    {
        $this->update(['is_accepted' => true]);
        $this->question->update(['is_answered' => true]);
        
        // Add reputation to the answerer (like Stack Overflow)
        $this->user->increment('reputation', 15);
        
        // Add reputation to the question asker (for accepting an answer)
        $this->question->user->increment('reputation', 2);
        
        return $this;
    }

    /**
     * Unaccept this answer.
     */
    public function unaccept()
    {
        $this->update(['is_accepted' => false]);
        
        // If this was the only accepted answer, mark question as unanswered
        if (!$this->question->answers()->where('is_accepted', true)->exists()) {
            $this->question->update(['is_answered' => false]);
        }
        
        // Remove reputation from the answerer
        $this->user->decrement('reputation', 15);
        
        // Remove reputation from the question asker
        $this->question->user->decrement('reputation', 2);
        
        return $this;
    }
}
