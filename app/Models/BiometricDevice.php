<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class BiometricDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'serial_number',
        'device_name',
        'device_type',
        'ip_address',
        'mac_address',
        'firmware_version',
        'language',
        'push_version',
        'device_options',
        'status',
        'last_seen',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'device_options' => 'array',
        'last_seen' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the biometric users for this device
     */
    public function biometricUsers(): HasMany
    {
        return $this->hasMany(BiometricUser::class, 'device_id');
    }

    /**
     * Get the attendance records for this device
     */
    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class, 'device_id');
    }

    /**
     * Get the device status label
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'online' => 'Online',
            'offline' => 'Offline',
            'error' => 'Error',
            default => 'Unknown'
        };
    }

    /**
     * Get the status color for display
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'online' => 'green',
            'offline' => 'red',
            'error' => 'yellow',
            default => 'gray'
        };
    }

    /**
     * Check if device is currently online (seen within last 5 minutes)
     */
    public function getIsOnlineAttribute()
    {
        return $this->last_seen && $this->last_seen->diffInMinutes(now()) <= 5;
    }

    /**
     * Get formatted last seen time
     */
    public function getFormattedLastSeenAttribute()
    {
        return $this->last_seen ? $this->last_seen->diffForHumans() : 'Never';
    }
}
