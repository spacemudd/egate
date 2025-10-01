<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BiometricUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'device_user_id',
        'name',
        'employee_id',
        'department',
        'position',
        'card_number',
        'fingerprint_template',
        'face_template',
        'biometric_data',
        'is_active',
        'last_sync',
        'notes',
    ];

    protected $casts = [
        'biometric_data' => 'array',
        'last_sync' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the device that owns this user
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(BiometricDevice::class, 'device_id');
    }

    /**
     * Get the attendance records for this user
     */
    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class, 'biometric_user_id');
    }

    /**
     * Get the active status label
     */
    public function getActiveStatusLabelAttribute()
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }

    /**
     * Get the active status color
     */
    public function getActiveStatusColorAttribute()
    {
        return $this->is_active ? 'green' : 'red';
    }

    /**
     * Get formatted last sync time
     */
    public function getFormattedLastSyncAttribute()
    {
        return $this->last_sync ? $this->last_sync->diffForHumans() : 'Never';
    }

    /**
     * Get the display name (name or employee_id)
     */
    public function getDisplayNameAttribute()
    {
        return $this->name ?: "User {$this->device_user_id}";
    }
}
