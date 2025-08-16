<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EGateRequest extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'egate_requests';

    protected $fillable = [
        'method',
        'type',
        'serial',
        'device_id',
        'mac_address',
        'ip_address',
        'reader',
        'source',
        'status',
        'input',
        'card',
        'data',
        'index',
        'key',
        'now',
        'crc',
        't1',
        'h1',
        't2',
        'h2',
        'next_num',
        'ver',
        'will_pass',
        'passed',
        'modbus',
        'order_code',
        'request_data',
        'response_data',
        'response_status',
    ];

    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
    ];

    /**
     * Get the method type label
     */
    public function getMethodLabelAttribute()
    {
        return match($this->method) {
            'GetStatus' => 'Heartbeat',
            'SearchCardAcs' => 'Access Control',
            default => $this->method
        };
    }

    /**
     * Get the data type label
     */
    public function getTypeLabelAttribute()
    {
        return match($this->type) {
            '0' => 'Card',
            '1' => 'QR Code',
            '2' => 'PIN',
            '3' => 'Button',
            '5' => 'Alarm',
            '9' => 'Base64 Data',
            '10' => 'Fingerprint',
            '11' => 'Finger Vein',
            '12' => 'RFID',
            '13', '23' => 'Face',
            '28' => 'JSON',
            '30' => 'WG66',
            '31' => 'Social Security Card',
            default => $this->type
        };
    }

    /**
     * Get the reader direction label
     */
    public function getReaderLabelAttribute()
    {
        return match($this->reader) {
            '0' => 'Entry',
            '1' => 'Exit',
            default => $this->reader
        };
    }

    /**
     * Get the response status label
     */
    public function getResponseStatusLabelAttribute()
    {
        return match($this->response_status) {
            '0' => 'Reject',
            '1' => 'Open',
            '2' => 'Alarm',
            '3' => 'Close',
            '4' => 'Ignore',
            default => $this->response_status
        };
    }

    /**
     * Scope for heartbeat requests
     */
    public function scopeHeartbeat($query)
    {
        return $query->where('method', 'GetStatus');
    }

    /**
     * Scope for access control requests
     */
    public function scopeAccessControl($query)
    {
        return $query->where('method', 'SearchCardAcs');
    }
}
