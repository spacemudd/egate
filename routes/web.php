<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EGateController;
use App\Http\Controllers\LogsController;
use App\Http\Controllers\ZKTecoController;

Route::get('/', function () {
    return view('welcome');
});

// eGate API endpoints (these will receive requests from the eGate device)
Route::prefix('data')->group(function () {
    Route::any('Acs.aspx', [EGateController::class, 'handleRequest']);
});

// Logs page for viewing eGate requests
Route::prefix('logs')->group(function () {
    Route::get('/', [LogsController::class, 'index'])->name('logs.index');
    Route::get('/export', [LogsController::class, 'export'])->name('logs.export');
    Route::get('/{egateRequest}', [LogsController::class, 'show'])->name('logs.show');
});

// Door control routes
Route::prefix('door')->group(function () {
    Route::post('/open', [EGateController::class, 'remoteDoorControl'])->name('door.open');
    Route::get('/status', [EGateController::class, 'doorStatus'])->name('door.status');
});

// Alternative route for logs (as requested)
Route::get('/logs', [LogsController::class, 'index'])->name('logs.index');

// ZkTeco webhook endpoint
Route::any('/webhooks/fp', [EGateController::class, 'handleZkTecoWebhook']);

// ZKTeco iClock endpoints
Route::match(['GET', 'POST'], '/iclock/cdata', function (Illuminate\Http\Request $request) {
    $rawBody = $request->getContent();
    $table = $request->input('table', $request->query('table'));
    $serial = $request->input('SN', $request->query('SN'));

    $lines = [];
    $parsedRecords = [];
    $processedCount = 0;

    if (!empty($rawBody)) {
        $lines = preg_split("/(\r\n|\n|\r)/", trim($rawBody));
        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            $processedCount++;

            // Try to parse ATTLOG/FACELOG style records
            $parts = explode("\t", $line);
            if (count($parts) < 2) {
                $parts = preg_split('/\s+/', $line);
            }

            $record = [
                'raw' => $line,
                'fields' => $parts,
            ];

            // Heuristic mapping for ATTLOG
            if (strtoupper((string) $table) === 'ATTLOG') {
                $record['mapped'] = [
                    'pin' => $parts[0] ?? null,
                    'datetime' => $parts[1] ?? null,
                    'status' => $parts[2] ?? null,
                    'verify' => $parts[3] ?? null,
                    'workcode' => $parts[4] ?? null,
                ];
            }

            $parsedRecords[] = $record;
        }
    }

    // Update device status in cache
    if ($serial) {
        $devices = \Cache::store('file')->get('zkteco_devices', []);
        
        $deviceData = [
            'serial' => $serial,
            'ip_address' => $request->ip(),
            'last_seen' => now()->toISOString(),
            'device_type' => $request->input('DeviceType', $request->query('DeviceType', 'att')),
            'language' => $request->input('language', $request->query('language', '69')),
            'push_version' => $request->input('pushver', $request->query('pushver', '2.4.1')),
            'options' => $request->input('options', $request->query('options', 'all')),
            'push_options_flag' => $request->input('PushOptionsFlag', $request->query('PushOptionsFlag', '1')),
            'user_agent' => $request->header('User-Agent', 'Unknown'),
            'status' => 'online'
        ];
        
        // Update or add device
        $devices[$serial] = $deviceData;
        
        // Store in cache for 1 hour
        \Cache::store('file')->put('zkteco_devices', $devices, 3600);
    }

    \Log::info('[ZKTeco] /iclock/cdata', [
        'method' => $request->method(),
        'url' => $request->fullUrl(),
        'ip' => $request->ip(),
        'query' => $request->query(),
        'body' => $request->all(),
        'raw_body' => $rawBody,
        'table' => $table,
        'serial' => $serial,
        'record_count' => $processedCount,
        'records' => $parsedRecords,
        'headers' => $request->headers->all(),
        'timestamp' => now()->toISOString(),
    ]);

    // Process ATTLOG data if it's attendance data
    if ($table === 'ATTLOG' && $serial) {
        app(\App\Http\Controllers\ZKTecoController::class)->processAttlogData($request);
    }

    $xml = '<?xml version="1.0" encoding="UTF-8"?><Response><Status>OK</Status></Response>';
    return response($xml, 200)->header('Content-Type', 'application/xml');
});

Route::match(['GET', 'POST'], '/iclock/verify', function (Illuminate\Http\Request $request) {
    \Log::info('[ZKTeco] /iclock/verify', [
        'method' => $request->method(),
        'url' => $request->fullUrl(),
        'ip' => $request->ip(),
        'query' => $request->query(),
        'body' => $request->all(),
        'raw_body' => $request->getContent(),
        'headers' => $request->headers->all(),
        'timestamp' => now()->toISOString(),
    ]);
    return response('OK', 200)->header('Content-Type', 'text/plain');
});

Route::match(['GET', 'POST'], '/iclock/getrequest', function (Illuminate\Http\Request $request) {
    $responseText = 'GET OPTIONS Stamp=0';
    $serial = $request->input('SN', $request->query('SN'));

    // If a per-device queued command exists, return and clear it
    if (!empty($serial)) {
        $cacheKey = 'zkteco_cmd_' . $serial;
        $queued = \Cache::store('file')->pull($cacheKey); // get and delete
        if (!empty($queued)) {
            $responseText = $queued;
        }
    }

    // Allow manual fetch via query for testing
    $fetch = $request->input('fetch', $request->query('fetch'));
    if ($fetch === 'users') {
        $responseText = 'GET USERINFO';
    }

    \Log::info('[ZKTeco] /iclock/getrequest', [
        'method' => $request->method(),
        'url' => $request->fullUrl(),
        'ip' => $request->ip(),
        'query' => $request->query(),
        'body' => $request->all(),
        'raw_body' => $request->getContent(),
        'headers' => $request->headers->all(),
        'timestamp' => now()->toISOString(),
        'response_preview' => substr($responseText, 0, 120),
    ]);

    return response($responseText, 200)->header('Content-Type', 'text/plain');
});

// Catch-all logger for any other iClock endpoints the device may hit
Route::any('/iclock/{path}', function (Illuminate\Http\Request $request, $path) {
    \Log::info('[ZKTeco] /iclock/* (catch-all)', [
        'path' => $path,
        'method' => $request->method(),
        'url' => $request->fullUrl(),
        'ip' => $request->ip(),
        'query' => $request->query(),
        'body' => $request->all(),
        'raw_body' => $request->getContent(),
        'headers' => $request->headers->all(),
        'timestamp' => now()->toISOString(),
    ]);
    return response('OK', 200)->header('Content-Type', 'text/plain');
})->where('path', '.*');

// ZKTeco device management routes
Route::prefix('zkteco')->group(function () {
    Route::get('/devices', [ZKTecoController::class, 'index'])->name('zkteco.devices');
    Route::get('/devices/{serial}', [ZKTecoController::class, 'getDeviceDetails'])->name('zkteco.device.details');
    Route::post('/devices/clear-cache', [ZKTecoController::class, 'clearCache'])->name('zkteco.clear-cache');
    Route::post('/devices/{serial}/sync', [ZKTecoController::class, 'syncDevice'])->name('zkteco.device.sync');
    Route::post('/devices/{serial}/command', [ZKTecoController::class, 'queueCommand'])->name('zkteco.device.command');
    Route::post('/devices/{serial}/attlog', [ZKTecoController::class, 'requestAttlog'])->name('zkteco.device.attlog');
});

// Biometric system routes
Route::prefix('biometric')->name('biometric.')->group(function () {
    // Device management
    Route::resource('devices', App\Http\Controllers\BiometricDeviceController::class);
    Route::post('devices/sync-from-cache', [App\Http\Controllers\BiometricDeviceController::class, 'syncFromCache'])
        ->name('devices.sync-from-cache');

    // User management
    Route::resource('users', App\Http\Controllers\BiometricUserController::class);
    Route::post('users/bulk-import', [App\Http\Controllers\BiometricUserController::class, 'bulkImport'])
        ->name('users.bulk-import');

    // Attendance management
    Route::get('attendance', [App\Http\Controllers\AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('attendance/export', [App\Http\Controllers\AttendanceController::class, 'export'])->name('attendance.export');
    Route::get('attendance/summary', [App\Http\Controllers\AttendanceController::class, 'summary'])->name('attendance.summary');
    Route::post('attendance/process', [App\Http\Controllers\AttendanceController::class, 'process'])->name('attendance.process');

    // Log import
    Route::get('import/logs', [App\Http\Controllers\LogDataImportController::class, 'showImportForm'])->name('import.logs');
    Route::post('import/logs', [App\Http\Controllers\LogDataImportController::class, 'importFromLogs'])->name('import.logs');
    Route::post('import/preview', [App\Http\Controllers\LogDataImportController::class, 'previewLogData'])->name('import.preview');
});

// Test routes for simulating eGate requests (remove in production)
Route::prefix('test')->group(function () {
    Route::get('/heartbeat', function () {
        $url = url('/data/Acs.aspx?method=GetStatus&Now=2025061612000001&Crc=65535&T1=25&H1=60&T2=26&H2=58&NextNum=0&Key=12345&Index=67890&Serial=TEST001&Status=03&Input=0000&Ver=135&ID=TEST001&MAC=0004A33CCCB2');
        return view('test', ['url' => $url, 'type' => 'heartbeat']);
    });
    
    Route::get('/access-control', function () {
        $url = url('/data/Acs.aspx?method=SearchCardAcs&type=0&Reader=0&DataLen=24&Index=12345&Serial=TEST001&Status=03&Input=0000&Ver=135&ID=TEST001&MAC=0004A33CCCB2&Card=123456');
        return view('test', ['url' => $url, 'type' => 'access-control']);
    });
});
