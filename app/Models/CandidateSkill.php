<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidateSkill extends Model
{
    use BelongsToUser, HasFactory;

    public const CATEGORIES = [
        'backend',
        'frontend',
        'database',
        'devops',
        'cloud',
        'testing',
        'soft_skill',
        'language',
        'tool',
        'other',
    ];

    protected $fillable = [
        'user_id',
        'name',
        'category',
        'proficiency_level',
        'years_of_experience',
        'evidence_notes',
    ];

    protected function casts(): array
    {
        return [
            'years_of_experience' => 'integer',
        ];
    }
}
