<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EGateController;
use App\Http\Controllers\LogsController;

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
    \Log::info('[ZKTeco] /iclock/cdata', [
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
    \Log::info('[ZKTeco] /iclock/getrequest', [
        'method' => $request->method(),
        'url' => $request->fullUrl(),
        'ip' => $request->ip(),
        'query' => $request->query(),
        'body' => $request->all(),
        'raw_body' => $request->getContent(),
        'headers' => $request->headers->all(),
        'timestamp' => now()->toISOString(),
    ]);
    // Force upload of all logs
    return response('GET OPTIONS Stamp=0', 200)->header('Content-Type', 'text/plain');
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
