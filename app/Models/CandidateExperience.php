<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CandidateExperience extends Model
{
    use BelongsToUser, HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'role_title',
        'employment_type',
        'location',
        'start_date',
        'end_date',
        'is_current',
        'description',
        'responsibilities',
        'technologies',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_current' => 'boolean',
            'responsibilities' => 'array',
            'technologies' => 'array',
            'sort_order' => 'integer',
        ];
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(CandidateAchievement::class);
    }
}
