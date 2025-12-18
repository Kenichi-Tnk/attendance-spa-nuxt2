<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrectRest extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_correct_id',
        'rest_start',
        'rest_end',
    ];

    protected $casts = [
        'rest_start' => 'datetime:H:i:s',
        'rest_end' => 'datetime:H:i:s',
    ];

    /**
     * Get the attendance correction that owns the rest.
     */
    public function attendanceCorrect()
    {
        return $this->belongsTo(AttendanceCorrect::class);
    }

    /**
     * Calculate rest duration in seconds.
     */
    public function getDurationAttribute()
    {
        if (!$this->rest_end) {
            return null;
        }

        return strtotime($this->rest_end) - strtotime($this->rest_start);
    }
}
