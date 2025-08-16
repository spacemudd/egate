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

// Alternative route for logs (as requested)
Route::get('/logs', [LogsController::class, 'index'])->name('logs.index');

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
