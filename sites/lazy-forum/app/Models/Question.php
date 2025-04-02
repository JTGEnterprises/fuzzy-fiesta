<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'body',
        'user_id',
        'views',
        'score',
        'is_answered',
        'answers_count',
        'slug'
    ];

    protected $casts = [
        'is_answered' => 'boolean',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($question) {
            // Generate a slug if one isn't provided
            if (empty($question->slug)) {
                $question->slug = Str::slug($question->title);
            }
        });

        static::created(function ($question) {
            // Record activity
            Activity::log(
                $question->user_id,
                'asked',
                $question->title,
                route('questions.show', $question)
            );

            // Give reputation for asking a question (like Stack Overflow)
            User::find($question->user_id)->increment('reputation', 2);
        });
    }

    /**
     * Get URL attribute
     */
    protected function url(): Attribute
    {
        return Attribute::make(
            get: fn () => route('questions.show', $this->id)
        );
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'id';
    }

    /**
     * Get the user that owns the question.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the answers for the question.
     */
    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    /**
     * Get the tags for the question.
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Get the votes for the question.
     */
    public function votes()
    {
        return $this->morphMany(Vote::class, 'votable');
    }

    /**
     * Get the accepted answer for the question.
     */
    public function acceptedAnswer()
    {
        return $this->hasOne(Answer::class)->where('is_accepted', true);
    }

    /**
     * Check if the question has a specific tag.
     */
    public function hasTag($tagId)
    {
        return $this->tags->contains('id', $tagId);
    }

    /**
     * Get bookmarks for this question.
     */
    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    /**
     * Check if the question is bookmarked by a specific user.
     */
    public function isBookmarkedBy($userId)
    {
        return $this->bookmarks()->where('user_id', $userId)->exists();
    }

    /**
     * Scope a query to include questions with a specific tag.
     */
    public function scopeWithTag($query, $tagId)
    {
        return $query->whereHas('tags', function ($q) use ($tagId) {
            $q->where('tags.id', $tagId);
        });
    }

    /**
     * Scope a query to include questions from a specific user.
     */
    public function scopeFromUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to search questions.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('body', 'like', "%{$search}%");
        });
    }
}
