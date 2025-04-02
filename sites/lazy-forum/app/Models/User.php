<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'reputation',
        'bio',
        'location',
        'website'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'reputation' => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
        'reputation_level',
    ];

    /**
     * Get the questions created by the user.
     */
    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Get the answers created by the user.
     */
    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    /**
     * Get the bookmarks created by the user.
     */
    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    /**
     * Get all the votes cast by the user.
     */
    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * Get the user's activities.
     */
    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Get the user's reputation level based on their reputation points.
     */
    public function getReputationLevelAttribute()
    {
        $reputation = $this->reputation;
        
        if ($reputation < 10) return 'Newbie';
        if ($reputation < 100) return 'Beginner';
        if ($reputation < 300) return 'Intermediate';
        if ($reputation < 1000) return 'Advanced';
        if ($reputation < 3000) return 'Expert';
        return 'Master';
    }

    /**
     * Check if user has enough reputation to perform certain actions.
     */
    public function hasEnoughReputationTo($action)
    {
        $thresholds = [
            'upvote' => 15,
            'downvote' => 125,
            'comment' => 50,
            'create_tag' => 300,
            'edit_post' => 200,
        ];

        return $this->reputation >= ($thresholds[$action] ?? 0);
    }
}
