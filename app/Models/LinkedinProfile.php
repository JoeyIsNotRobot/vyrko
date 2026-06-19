<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LinkedinProfile extends Model
{
    use BelongsToUser, HasFactory;

    protected $fillable = [
        'user_id',
        'headline',
        'about',
        'experiences_text',
        'skills_text',
        'raw_text',
    ];

    public function analysisReports(): HasMany
    {
        return $this->hasMany(LinkedinAnalysisReport::class);
    }
}
