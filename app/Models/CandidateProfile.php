<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidateProfile extends Model
{
    use BelongsToUser, HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'headline',
        'location_city',
        'location_state',
        'location_country',
        'email',
        'phone',
        'linkedin_url',
        'github_url',
        'portfolio_url',
        'summary',
        'target_role',
        'target_seniority',
        'professional_area',
        'onboarding_source',
        'preferred_language',
    ];
}
