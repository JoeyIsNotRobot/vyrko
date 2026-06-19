<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidateLanguage extends Model
{
    use BelongsToUser, HasFactory;

    protected $fillable = [
        'user_id',
        'language',
        'proficiency',
        'notes',
    ];
}
