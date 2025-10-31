<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rest extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'rest_start',
        'rest_end',
    ];

    protected $casts = [
        'rest_start' => 'datetime:H:i:s',
        'rest_end' => 'datetime:H:i:s',
    ];

    /**
     * Get the attendance that owns the rest.
     */
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
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
