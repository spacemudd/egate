<?php

namespace App\Http\Controllers;

use App\Models\BiometricDevice;
use App\Models\BiometricUser;
use App\Models\AttendanceRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class LogDataImportController extends Controller
{
    /**
     * Import SYZ8252100929 data from Laravel logs
     */
    public function importFromLogs(Request $request)
    {
        $request->validate([
            'log_file_path' => 'nullable|string',
            'device_serial' => 'nullable|string',
        ]);

        $logPath = $request->input('log_file_path', storage_path('logs/laravel.log'));
        $targetSerial = $request->input('device_serial', 'SYZ8252100929');

        if (!file_exists($logPath)) {
            return redirect()->back()->withErrors(['error' => 'Log file not found: ' . $logPath]);
        }

        $importedData = $this->parseLogFile($logPath, $targetSerial);

        if (empty($importedData['records'])) {
            return redirect()->back()->with('warning', 'No attendance records found for device ' . $targetSerial . ' in the log file.');
        }

        // Create or find the device
        $device = BiometricDevice::firstOrCreate(
            ['serial_number' => $targetSerial],
            [
                'device_name' => 'SYZ8252100929 Device',
                'device_type' => 'zkteco',
                'status' => 'offline',
                'is_active' => true,
            ]
        );

        $processedRecords = 0;
        $processedUsers = 0;

        foreach ($importedData['records'] as $record) {
            try {
                // Find or create biometric user
                $biometricUser = BiometricUser::firstOrCreate(
                    [
                        'device_id' => $device->id,
                        'device_user_id' => $record['device_user_id'],
                    ],
                    [
                        'name' => $record['name'] ?? "User {$record['device_user_id']}",
                        'employee_id' => $record['employee_id'] ?? null,
                        'department' => $record['department'] ?? null,
                        'is_active' => true,
                        'last_sync' => now(),
                    ]
                );

                // Parse datetime
                $punchTime = Carbon::parse($record['datetime']);

                // Determine punch type based on time (before 4 PM = punch in, after = punch out)
                $punchType = $punchTime->format('H') < 16 ? 'in' : 'out';

                // Create attendance record if it doesn't exist
                $attendanceRecord = AttendanceRecord::firstOrCreate(
                    [
                        'device_id' => $device->id,
                        'biometric_user_id' => $biometricUser->id,
                        'punch_time' => $punchTime,
                    ],
                    [
                        'device_user_id' => $record['device_user_id'],
                        'punch_type' => $punchType,
                        'verify_mode' => $record['verify_mode'] ?? '1',
                        'status' => $record['status'] ?? null,
                        'work_code' => $record['work_code'] ?? null,
                        'device_data' => [
                            'imported_from' => 'laravel_log',
                            'raw_data' => $record,
                        ],
                        'is_processed' => true,
                        'processed_at' => now(),
                    ]
                );

                $processedRecords++;
            } catch (\Exception $e) {
                Log::error('Error importing log record', [
                    'record' => $record,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Update device last seen
        $device->update(['last_seen' => now()]);

        $message = "Import completed successfully! Processed {$processedRecords} attendance records for device {$targetSerial}.";

        return redirect()->route('biometric.devices.show', $device)
            ->with('success', $message);
    }

    /**
     * Parse Laravel log file for attendance data
     */
    private function parseLogFile($logPath, $targetSerial)
    {
        $records = [];
        $lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Look for lines containing the target serial number and ATTLOG data
            if (strpos($line, $targetSerial) !== false && strpos($line, 'ATTLOG') !== false) {
                $parsedData = $this->extractAttlogData($line);
                if ($parsedData) {
                    $records[] = $parsedData;
                }
            }
        }

        return ['records' => $records];
    }

    /**
     * Extract ATTLOG data from log line
     */
    private function extractAttlogData($logLine)
    {
        try {
            // Look for ATTLOG data in various formats
            if (preg_match('/records.*?\[(.*?)\]/', $logLine, $matches)) {
                $recordsData = $matches[1];
                $records = json_decode('[' . $recordsData . ']', true);
                
                if (is_array($records)) {
                    return $records[0] ?? null; // Return first record
                }
            }

            // Alternative parsing for tab-separated data
            if (preg_match('/\[(.*?)\]/', $logLine, $matches)) {
                $data = $matches[1];
                $parts = explode("\t", $data);
                
                if (count($parts) >= 2) {
                    return [
                        'device_user_id' => $parts[0] ?? null,
                        'datetime' => $parts[1] ?? null,
                        'status' => $parts[2] ?? null,
                        'verify_mode' => $parts[3] ?? null,
                        'work_code' => $parts[4] ?? null,
                    ];
                }
            }

            // Parse from the attendance report format
            if (preg_match('/(\d+)\s+(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+(\d+)/', $logLine, $matches)) {
                return [
                    'device_user_id' => $matches[1] ?? null,
                    'datetime' => $matches[2] ?? null,
                    'verify_mode' => $matches[3] ?? null,
                ];
            }

        } catch (\Exception $e) {
            Log::error('Error parsing ATTLOG data from log line', [
                'line' => $logLine,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * Show import form
     */
    public function showImportForm()
    {
        $devices = BiometricDevice::all();
        return view('biometric.import.logs', compact('devices'));
    }

    /**
     * Preview log data before import
     */
    public function previewLogData(Request $request)
    {
        $request->validate([
            'log_file_path' => 'nullable|string',
            'device_serial' => 'nullable|string',
        ]);

        $logPath = $request->input('log_file_path', storage_path('logs/laravel.log'));
        $targetSerial = $request->input('device_serial', 'SYZ8252100929');

        if (!file_exists($logPath)) {
            return redirect()->back()->withErrors(['error' => 'Log file not found: ' . $logPath]);
        }

        $previewData = $this->parseLogFile($logPath, $targetSerial);

        return view('biometric.import.preview', [
            'records' => array_slice($previewData['records'], 0, 50), // Show first 50 records
            'total_count' => count($previewData['records']),
            'device_serial' => $targetSerial,
            'log_path' => $logPath,
        ]);
    }
}
