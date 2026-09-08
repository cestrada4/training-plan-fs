<?php

namespace App\Models;

use Database\Factories\TimeCardFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeCard extends Model
{
    /** @use HasFactory<TimeCardFactory> */
    use HasFactory;

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

    /**
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
