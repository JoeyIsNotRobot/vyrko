<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobPost extends Model
{
    use BelongsToUser, HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'company_name',
        'job_description',
        'target_language',
        'resume_type',
        'notes',
        'linkedin_url',
        'parsed_requirements',
        'parsed_keywords',
        'parsed_responsibilities',
        'parsed_seniority',
    ];

    protected function casts(): array
    {
        return [
            'parsed_requirements' => 'array',
            'parsed_keywords' => 'array',
            'parsed_responsibilities' => 'array',
        ];
    }

    public function matchReports(): HasMany
    {
        return $this->hasMany(JobMatchReport::class);
    }

    public function resumeVersions(): HasMany
    {
        return $this->hasMany(ResumeVersion::class);
    }
}
