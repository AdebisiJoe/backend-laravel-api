<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    use HasFactory;

    private const PREFERENCE_TYPES = [
        'source' => 'source',
        'category' => 'category',
        'author' => 'author',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'name',
    ];

    public function user()
    {
        $this->belongsTo(User::class);
    }
}
