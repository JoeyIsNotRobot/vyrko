<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResumeVersion extends Model
{
    use BelongsToUser, HasFactory;

    protected $fillable = [
        'user_id',
        'job_post_id',
        'job_match_report_id',
        'title',
        'language',
        'resume_type',
        'content',
        'plain_text',
        'status',
        'ats_checklist',
    ];

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'ats_checklist' => 'array',
        ];
    }

    public function jobPost(): BelongsTo
    {
        return $this->belongsTo(JobPost::class);
    }

    public function jobMatchReport(): BelongsTo
    {
        return $this->belongsTo(JobMatchReport::class);
    }
}
