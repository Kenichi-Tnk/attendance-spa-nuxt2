<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'check_in',
        'check_out',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in' => 'datetime:H:i:s',
        'check_out' => 'datetime:H:i:s',
    ];

    /**
     * Get the user that owns the attendance.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the rests for the attendance.
     */
    public function rests()
    {
        return $this->hasMany(Rest::class);
    }

    /**
     * Calculate total work time excluding rests.
     */
    public function getWorkTimeAttribute()
    {
        if (!$this->check_out) {
            return null;
        }

        $workTime = strtotime($this->check_out) - strtotime($this->check_in);

        // Subtract rest time
        $restTime = $this->rests->sum(function ($rest) {
            if (!$rest->rest_end) {
                return 0;
            }
            return strtotime($rest->rest_end) - strtotime($rest->rest_start);
        });

        return $workTime - $restTime;
    }
}
