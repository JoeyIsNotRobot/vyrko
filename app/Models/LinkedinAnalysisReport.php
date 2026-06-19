<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LinkedinAnalysisReport extends Model
{
    use BelongsToUser, HasFactory;

    protected $fillable = [
        'user_id',
        'linkedin_profile_id',
        'target_role',
        'target_language',
        'score',
        'strengths',
        'weaknesses',
        'recommendations',
        'rewritten_headline',
        'rewritten_about',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'strengths' => 'array',
            'weaknesses' => 'array',
            'recommendations' => 'array',
        ];
    }

    public function linkedinProfile(): BelongsTo
    {
        return $this->belongsTo(LinkedinProfile::class);
    }
}
