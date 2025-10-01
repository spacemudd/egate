<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'biometric_user_id',
        'device_user_id',
        'punch_time',
        'punch_type',
        'verify_mode',
        'status',
        'work_code',
        'device_data',
        'is_processed',
        'processed_at',
        'notes',
    ];

    protected $casts = [
        'punch_time' => 'datetime',
        'device_data' => 'array',
        'is_processed' => 'boolean',
        'processed_at' => 'datetime',
    ];

    /**
     * Get the device that recorded this attendance
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(BiometricDevice::class, 'device_id');
    }

    /**
     * Get the biometric user for this attendance record
     */
    public function biometricUser(): BelongsTo
    {
        return $this->belongsTo(BiometricUser::class, 'biometric_user_id');
    }

    /**
     * Get the punch type label
     */
    public function getPunchTypeLabelAttribute()
    {
        return match($this->punch_type) {
            'in' => 'Punch In',
            'out' => 'Punch Out',
            'break_in' => 'Break In',
            'break_out' => 'Break Out',
            default => ucfirst($this->punch_type)
        };
    }

    /**
     * Get the verify mode label
     */
    public function getVerifyModeLabelAttribute()
    {
        return match($this->verify_mode) {
            '1' => 'Fingerprint',
            '2' => 'Password',
            '3' => 'Card',
            '4' => 'Face',
            '15' => 'Fingerprint + Password',
            default => $this->verify_mode
        };
    }

    /**
     * Get the processed status label
     */
    public function getProcessedStatusLabelAttribute()
    {
        return $this->is_processed ? 'Processed' : 'Pending';
    }

    /**
     * Get the processed status color
     */
    public function getProcessedStatusColorAttribute()
    {
        return $this->is_processed ? 'green' : 'yellow';
    }

    /**
     * Check if this is a punch in (before 4 PM) or punch out (after 4 PM)
     */
    public function getIsPunchInAttribute()
    {
        return $this->punch_time->format('H') < 16; // Before 4 PM
    }

    /**
     * Get formatted punch time
     */
    public function getFormattedPunchTimeAttribute()
    {
        return $this->punch_time->format('Y-m-d H:i:s');
    }
}
