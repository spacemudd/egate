<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ZKTecoController extends Controller
{
    /**
     * Show the ZKTeco devices debug page
     */
    public function index()
    {
        // Get connected devices from cache (updated by the /iclock/cdata endpoint)
        $devices = Cache::store('file')->get('zkteco_devices', []);
        
        // Sort by last seen (most recent first)
        usort($devices, function($a, $b) {
            return strtotime($b['last_seen']) - strtotime($a['last_seen']);
        });

        // Get recent activity from logs
        $recentActivity = $this->getRecentActivity();
        
        return view('zkteco.devices', compact('devices', 'recentActivity'));
    }

    /**
     * Get recent ZKTeco activity from logs
     */
    private function getRecentActivity()
    {
        $logFile = storage_path('logs/laravel.log');
        $activity = [];
        
        if (file_exists($logFile)) {
            $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $recentLines = array_slice($lines, -1000); // Last 1000 lines
            
            foreach ($recentLines as $line) {
                if (strpos($line, '[ZKTeco]') !== false) {
                    // Extract timestamp and basic info
                    if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\].*\[ZKTeco\] (.*)/', $line, $matches)) {
                        $activity[] = [
                            'timestamp' => $matches[1],
                            'message' => $matches[2],
                            'raw_line' => $line
                        ];
                    }
                }
            }
            
            // Keep only last 50 activities
            $activity = array_slice($activity, -50);
        }
        
        return array_reverse($activity); // Most recent first
    }

    /**
     * Update device status (called by the /iclock/cdata endpoint)
     */
    public function updateDeviceStatus(Request $request)
    {
        $serial = $request->query('SN');
        $ip = $request->ip();
        
        if ($serial) {
            $devices = Cache::store('file')->get('zkteco_devices', []);
            
            $deviceData = [
                'serial' => $serial,
                'ip_address' => $ip,
                'last_seen' => now()->toISOString(),
                'device_type' => $request->query('DeviceType', 'att'),
                'language' => $request->query('language', '69'),
                'push_version' => $request->query('pushver', '2.4.1'),
                'options' => $request->query('options', 'all'),
                'push_options_flag' => $request->query('PushOptionsFlag', '1'),
                'user_agent' => $request->header('User-Agent', 'Unknown'),
                'status' => 'online'
            ];
            
            // Update or add device
            $devices[$serial] = $deviceData;
            
            // Store in cache for 1 hour
            Cache::store('file')->put('zkteco_devices', $devices, 3600);
            
            Log::info('[ZKTeco] Device status updated', [
                'serial' => $serial,
                'ip' => $ip,
                'action' => 'device_status_updated'
            ]);
        }
        
        return response('OK', 200)->header('Content-Type', 'text/plain');
    }

    /**
     * Clear device cache
     */
    public function clearCache()
    {
        Cache::store('file')->forget('zkteco_devices');
        
        return redirect()->route('zkteco.devices')
            ->with('success', 'Device cache cleared successfully.');
    }

    /**
     * Get device details as JSON
     */
    public function getDeviceDetails($serial)
    {
        $devices = Cache::store('file')->get('zkteco_devices', []);
        
        if (isset($devices[$serial])) {
            return response()->json($devices[$serial]);
        }
        
        return response()->json(['error' => 'Device not found'], 404);
    }

    /**
     * Queue a sync command (GET USERINFO) for a specific device
     */
    public function syncDevice(string $serial)
    {
        // Store a one-time command for the device; consumed by /iclock/getrequest
        Cache::store('file')->put('zkteco_cmd_' . $serial, 'GET USERINFO', 300);

        return redirect()->route('zkteco.devices')
            ->with('success', 'Sync command queued for device ' . $serial);
    }

    /**
     * Queue a custom command for a specific device
     */
    public function queueCommand(Request $request, string $serial)
    {
        $command = $request->input('command', 'GET OPTIONS Stamp=0');
        
        // Store the command for the device; consumed by /iclock/getrequest
        Cache::store('file')->put('zkteco_cmd_' . $serial, $command, 300);

        Log::info('[ZKTeco] Command queued', [
            'serial' => $serial,
            'command' => $command,
            'expires_at' => now()->addSeconds(300)->toISOString()
        ]);

        return redirect()->route('zkteco.devices')
            ->with('success', 'Command queued for device ' . $serial . ': ' . $command);
    }

    /**
     * Request ATTLOG data for a date range
     */
    public function requestAttlog(string $serial, Request $request)
    {
        $startDate = $request->input('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        
        // Try different command formats that ZKTeco devices might understand
        // Format 1: DATA QUERY with dates
        $command = "DATA QUERY ATTLOG StartDate={$startDate} EndDate={$endDate}";
        
        Cache::store('file')->put('zkteco_cmd_' . $serial, $command, 300);

        Log::info('[ZKTeco] ATTLOG query queued', [
            'serial' => $serial,
            'command' => $command,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'expires_at' => now()->addSeconds(300)->toISOString()
        ]);

        return redirect()->route('zkteco.devices')
            ->with('success', 'ATTLOG query queued for device ' . $serial . ' (Date range: ' . $startDate . ' to ' . $endDate . ')');
    }
}
