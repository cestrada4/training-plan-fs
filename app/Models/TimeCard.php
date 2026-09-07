<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeCard extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'total_hours',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'total_hours' => 'decimal:2',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
