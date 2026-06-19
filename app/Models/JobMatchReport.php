<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobMatchReport extends Model
{
    use BelongsToUser, HasFactory;

    protected $fillable = [
        'user_id',
        'job_post_id',
        'overall_score',
        'technical_score',
        'experience_score',
        'seniority_score',
        'keyword_score',
        'ats_format_score',
        'human_readability_score',
        'strengths',
        'gaps',
        'warnings',
        'recommendations',
        'evidence_map',
    ];

    protected function casts(): array
    {
        return [
            'overall_score' => 'integer',
            'technical_score' => 'integer',
            'experience_score' => 'integer',
            'seniority_score' => 'integer',
            'keyword_score' => 'integer',
            'ats_format_score' => 'integer',
            'human_readability_score' => 'integer',
            'strengths' => 'array',
            'gaps' => 'array',
            'warnings' => 'array',
            'recommendations' => 'array',
            'evidence_map' => 'array',
        ];
    }

    public function jobPost(): BelongsTo
    {
        return $this->belongsTo(JobPost::class);
    }

    public function resumeVersions(): HasMany
    {
        return $this->hasMany(ResumeVersion::class);
    }
}
