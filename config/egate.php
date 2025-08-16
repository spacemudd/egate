<?php

return [
    /*
    |--------------------------------------------------------------------------
    | EGate Controller Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration settings for the EGate access control system integration.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | AES128 Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used to decrypt AES128 encrypted payloads from the EGate
    | controller. It must match the password configured in the gate controller
    | dashboard under AES128 encryption settings.
    |
    */
    'aes128_key' => env('EGATE_AES128_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Additional security configuration options for the EGate system.
    |
    */
    'enable_encryption' => env('EGATE_ENABLE_ENCRYPTION', false),
    'log_encrypted_requests' => env('EGATE_LOG_ENCRYPTED_REQUESTS', true),
    'max_payload_size' => 2048, // Maximum encrypted payload size in bytes
];
