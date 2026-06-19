<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidateProject extends Model
{
    use BelongsToUser, HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'role',
        'technologies',
        'url',
        'repository_url',
        'start_date',
        'end_date',
        'is_current',
        'highlights',
    ];

    protected function casts(): array
    {
        return [
            'technologies' => 'array',
            'highlights' => 'array',
            'start_date' => 'date',
            'end_date' => 'date',
            'is_current' => 'boolean',
        ];
    }
}
