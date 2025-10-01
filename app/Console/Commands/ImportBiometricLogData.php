<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BiometricDevice;
use App\Models\BiometricUser;
use App\Models\AttendanceRecord;
use Carbon\Carbon;

class ImportBiometricLogData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'biometric:import-logs 
                            {--device=SYZ8252100929 : Device serial number to import}
                            {--log-file= : Path to log file (defaults to storage/logs/laravel.log)}
                            {--preview : Preview data without importing}
                            {--force : Force import even if device exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import biometric attendance data from Laravel logs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $deviceSerial = $this->option('device');
        $logFile = $this->option('log-file') ?: storage_path('logs/laravel.log');
        $preview = $this->option('preview');
        $force = $this->option('force');

        $this->info("🔍 Scanning Laravel logs for device: {$deviceSerial}");
        $this->info("📁 Log file: {$logFile}");

        if (!file_exists($logFile)) {
            $this->error("❌ Log file not found: {$logFile}");
            return 1;
        }

        // Parse the log file
        $records = $this->parseLogFile($logFile, $deviceSerial);

        if (empty($records)) {
            $this->warn("⚠️  No attendance records found for device {$deviceSerial} in the log file.");
            return 0;
        }

        $this->info("✅ Found " . count($records) . " attendance records");

        if ($preview) {
            return $this->previewData($records, $deviceSerial);
        }

        // Check if device already exists
        $device = BiometricDevice::where('serial_number', $deviceSerial)->first();
        if ($device && !$force) {
            $this->warn("⚠️  Device {$deviceSerial} already exists. Use --force to overwrite or add to existing data.");
            return 0;
        }

        return $this->importData($records, $deviceSerial);
    }

    /**
     * Parse Laravel log file for attendance data
     */
    private function parseLogFile($logPath, $targetSerial)
    {
        $records = [];
        
        // First, try to parse the markdown attendance report if it exists
        $markdownReport = base_path("ATTENDANCE_REPORT_{$targetSerial}.md");
        if (file_exists($markdownReport)) {
            $this->info("📋 Found attendance report: " . basename($markdownReport));
            $records = array_merge($records, $this->parseMarkdownReport($markdownReport));
        }

        // Then parse the log file
        $lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $this->info("📖 Reading " . count($lines) . " lines from log file...");

        $progressBar = $this->output->createProgressBar(count($lines));
        $progressBar->start();

        foreach ($lines as $lineNumber => $line) {
            $progressBar->advance();
            
            // Look for lines containing the target serial number and ATTLOG data
            if (strpos($line, $targetSerial) !== false && strpos($line, 'ATTLOG') !== false) {
                $parsedData = $this->extractAttlogData($line);
                if ($parsedData) {
                    $records[] = $parsedData;
                }
            }

            // Also look for attendance report format
            if (strpos($line, $targetSerial) !== false && preg_match('/(\d+)\s+(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+(\d+)/', $line, $matches)) {
                $records[] = [
                    'device_user_id' => $matches[1],
                    'datetime' => $matches[2],
                    'verify_mode' => $matches[3],
                    'source' => 'attendance_report'
                ];
            }
        }

        $progressBar->finish();
        $this->newLine();

        return $records;
    }

    /**
     * Parse the markdown attendance report
     */
    private function parseMarkdownReport($markdownPath)
    {
        $records = [];
        $content = file_get_contents($markdownPath);
        
        // Parse User ID sections and their timestamps
        preg_match_all('/### User ID: (\d+)(.*?)(?=### User ID:|\Z)/s', $content, $userMatches, PREG_SET_ORDER);
        
        foreach ($userMatches as $userMatch) {
            $userId = $userMatch[1];
            $userContent = $userMatch[2];
            
            // Extract timestamps and verification methods
            preg_match_all('/- (\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}) \((.*?)\)/', $userContent, $timestampMatches, PREG_SET_ORDER);
            
            foreach ($timestampMatches as $timestampMatch) {
                $datetime = $timestampMatch[1];
                $verifyMethod = $timestampMatch[2];
                
                // Convert verification method to numeric code
                $verifyMode = 1; // Default to fingerprint
                if (stripos($verifyMethod, 'fingerprint') !== false) {
                    $verifyMode = 1;
                } elseif (stripos($verifyMethod, 'card') !== false) {
                    $verifyMode = 4;
                } elseif (stripos($verifyMethod, 'face') !== false) {
                    $verifyMode = 15;
                } elseif (stripos($verifyMethod, 'password') !== false) {
                    $verifyMode = 3;
                } elseif (stripos($verifyMethod, 'other') !== false) {
                    $verifyMode = 0;
                }
                
                $records[] = [
                    'device_user_id' => $userId,
                    'datetime' => $datetime,
                    'verify_mode' => $verifyMode,
                    'status' => '255', // Normal punch
                    'source' => 'markdown_report'
                ];
            }
        }
        
        return $records;
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
                
                if (is_array($records) && !empty($records)) {
                    return $records[0]; // Return first record
                }
            }

            // Alternative parsing for tab-separated data
            if (preg_match('/\[(.*?)\]/', $logLine, $matches)) {
                $data = $matches[1];
                $parts = explode("\t", $data);
                
                if (count($parts) >= 2) {
                    return [
                        'device_user_id' => $parts[0],
                        'datetime' => $parts[1],
                        'status' => $parts[2] ?? null,
                        'verify_mode' => $parts[3] ?? null,
                        'work_code' => $parts[4] ?? null,
                        'source' => 'tab_separated'
                    ];
                }
            }

        } catch (\Exception $e) {
            // Silently continue if parsing fails
        }

        return null;
    }

    /**
     * Preview data before importing
     */
    private function previewData($records, $deviceSerial)
    {
        $this->info("📋 Preview of data to be imported:");
        $this->newLine();

        $headers = ['User ID', 'Date/Time', 'Punch Type', 'Verify Mode', 'Status', 'Source'];
        $rows = [];

        foreach (array_slice($records, 0, 20) as $record) { // Show first 20 records
            $punchTime = null;
            $punchType = 'Unknown';
            
            try {
                $punchTime = Carbon::parse($record['datetime']);
                $punchType = $punchTime->format('H') < 16 ? 'Punch In' : 'Punch Out';
            } catch (\Exception $e) {
                // Handle parsing errors
            }

            $rows[] = [
                $record['device_user_id'] ?? 'N/A',
                $record['datetime'] ?? 'N/A',
                $punchType,
                $record['verify_mode'] ?? 'N/A',
                $record['status'] ?? 'N/A',
                $record['source'] ?? 'unknown'
            ];
        }

        $this->table($headers, $rows);

        if (count($records) > 20) {
            $this->info("... and " . (count($records) - 20) . " more records");
        }

        $this->newLine();
        $this->info("Total records to import: " . count($records));
        $this->info("Run without --preview to import the data.");

        return 0;
    }

    /**
     * Import the data
     */
    private function importData($records, $deviceSerial)
    {
        $this->info("🚀 Starting import process...");

        // Create or find the device
        $device = BiometricDevice::firstOrCreate(
            ['serial_number' => $deviceSerial],
            [
                'device_name' => $deviceSerial . ' Device',
                'device_type' => 'zkteco',
                'status' => 'offline',
                'is_active' => true,
            ]
        );

        $this->info("📱 Device: " . $device->device_name);

        $processedRecords = 0;
        $processedUsers = 0;
        $errors = 0;

        $progressBar = $this->output->createProgressBar(count($records));
        $progressBar->start();

        foreach ($records as $record) {
            $progressBar->advance();
            
            try {
                // Find or create biometric user
                $biometricUser = BiometricUser::firstOrCreate(
                    [
                        'device_id' => $device->id,
                        'device_user_id' => $record['device_user_id'],
                    ],
                    [
                        'name' => "User {$record['device_user_id']}",
                        'employee_id' => null,
                        'department' => null,
                        'is_active' => true,
                        'last_sync' => now(),
                    ]
                );

                if ($biometricUser->wasRecentlyCreated) {
                    $processedUsers++;
                }

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

                if ($attendanceRecord->wasRecentlyCreated) {
                    $processedRecords++;
                }

            } catch (\Exception $e) {
                $errors++;
                if ($this->getOutput()->isVerbose()) {
                    $this->error("Error processing record: " . $e->getMessage());
                }
            }
        }

        $progressBar->finish();
        $this->newLine();

        // Update device last seen
        $device->update(['last_seen' => now()]);

        // Show results
        $this->info("✅ Import completed successfully!");
        $this->newLine();
        
        $this->table(['Metric', 'Count'], [
            ['Total Records Processed', count($records)],
            ['New Attendance Records', $processedRecords],
            ['New Users Created', $processedUsers],
            ['Errors', $errors],
        ]);

        if ($processedUsers > 0) {
            $this->warn("💡 Tip: You can now edit the users to add their real names and employee IDs:");
            $this->info("   Visit: http://localhost:8000/biometric/users");
        }

        return 0;
    }
}