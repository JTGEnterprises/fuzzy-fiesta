<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'title',
        'link',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function log($userId, $action, $title, $link)
    {
        return self::create([
            'user_id' => $userId,
            'action' => $action,
            'title' => $title,
            'link' => $link,
        ]);
    }
} 