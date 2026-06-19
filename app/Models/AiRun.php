<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiRun extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'feature',
        'provider',
        'model',
        'prompt_hash',
        'input_tokens',
        'output_tokens',
        'cost_estimate',
        'status',
        'error_message',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'input_tokens' => 'integer',
            'output_tokens' => 'integer',
            'cost_estimate' => 'decimal:4',
            'created_at' => 'datetime',
        ];
    }
}
