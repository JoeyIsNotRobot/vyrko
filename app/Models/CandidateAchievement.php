<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateAchievement extends Model
{
    use BelongsToUser, HasFactory;

    protected $fillable = [
        'user_id',
        'candidate_experience_id',
        'title',
        'description',
        'impact_metric',
        'evidence_tags',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'evidence_tags' => 'array',
            'sort_order' => 'integer',
        ];
    }

    public function experience(): BelongsTo
    {
        return $this->belongsTo(CandidateExperience::class, 'candidate_experience_id');
    }
}
