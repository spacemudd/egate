<?php

/**
 * Extract ATTLOG data for SYZ8243300597 from Laravel logs
 */

$logFiles = [
    __DIR__ . '/storage/logs/laravel.log.production',
    __DIR__ . '/storage/logs/laravel.log.1',
    __DIR__ . '/storage/logs/laravel.log.2',
];

$targetSerial = 'SYZ8243300597';
$records = [];

foreach ($logFiles as $logFile) {
    if (!file_exists($logFile)) {
        echo "Log file not found: $logFile\n";
        continue;
    }
    
    echo "Processing: $logFile\n";
    $handle = fopen($logFile, 'r');
    
    if (!$handle) {
        echo "Could not open: $logFile\n";
        continue;
    }
    
    $lineCount = 0;
    $matchCount = 0;
    while (($line = fgets($handle)) !== false) {
        $lineCount++;
        // Look for lines with SYZ8243300597, cdata, and ATTLOG
        if (strpos($line, $targetSerial) === false) continue;
        if (stripos($line, 'cdata') === false) continue;
        if (stripos($line, 'ATTLOG') === false) continue;
        
        // Extract raw_body value - it's at the end, after "raw_body":" and before the closing }
        // Find the position of "raw_body":"
        $rawBodyStart = strpos($line, '"raw_body":"');
        if ($rawBodyStart === false) {
            if ($matchCount <= 3) {
                echo "DEBUG: raw_body not found in line\n";
            }
            continue;
        }
        
        // Start after "raw_body":" (12 characters)
        $rawBodyStart += 12;
        
        // The raw_body appears to not be properly quoted - it goes to the end of the line
        // Extract everything from raw_body to the end, then trim tabs/newlines
        $rawBody = substr($line, $rawBodyStart);
        // Remove trailing newline
        $rawBody = rtrim($rawBody, "\n\r");
        // The raw_body ends before the closing } - find it
        $closingBrace = strrpos($rawBody, '}');
        if ($closingBrace !== false) {
            $rawBody = substr($rawBody, 0, $closingBrace);
        }
        // Trim any trailing tabs/spaces
        $rawBody = rtrim($rawBody, "\t ");
        // Replace escaped tabs with actual tabs
        $rawBody = str_replace('\\t', "\t", $rawBody);
        $rawBody = str_replace('\\n', "\n", $rawBody);
        $rawBody = str_replace('\\r', "\r", $rawBody);
        
        if ($matchCount <= 3) {
            echo "DEBUG: Extracted raw_body (first 100 chars): " . substr($rawBody, 0, 100) . "\n";
        }
        
        // Parse tab-separated ATTLOG data
        $parts = explode("\t", trim($rawBody));
            
            if (count($parts) >= 2) {
                $deviceUserId = $parts[0] ?? null;
                $datetimeStr = $parts[1] ?? null;
                $status = $parts[2] ?? null;
                $verifyMode = $parts[3] ?? null;
                $workCode = $parts[4] ?? null;
                
                if ($deviceUserId && $datetimeStr) {
                    // Create unique key to avoid duplicates
                    $key = $deviceUserId . '_' . $datetimeStr;
                    
                    if (!isset($records[$key])) {
                        $records[$key] = [
                            'device_user_id' => $deviceUserId,
                            'datetime' => $datetimeStr,
                            'status' => $status,
                            'verify_mode' => $verifyMode,
                            'work_code' => $workCode,
                            'raw_line' => $rawBody,
                        ];
                    }
                }
            }
    }
    
    fclose($handle);
    echo "  Processed $lineCount lines, found $matchCount matching lines\n";
}

// Sort by datetime
usort($records, function($a, $b) {
    return strcmp($a['datetime'], $b['datetime']);
});

echo "\n=== Summary ===\n";
echo "Total unique ATTLOG records found: " . count($records) . "\n\n";

if (count($records) > 0) {
    echo "Date range: " . $records[0]['datetime'] . " to " . end($records)['datetime'] . "\n\n";
    
    echo "=== Sample Records (first 10) ===\n";
    foreach (array_slice($records, 0, 10) as $record) {
        echo sprintf(
            "User: %s | Time: %s | Verify: %s | Status: %s\n",
            $record['device_user_id'],
            $record['datetime'],
            $record['verify_mode'] ?? 'N/A',
            $record['status'] ?? 'N/A'
        );
    }
    
    // Export to CSV
    $csvFile = __DIR__ . '/storage/logs/SYZ8243300597_attlog_export.csv';
    $fp = fopen($csvFile, 'w');
    
    // Write header
    fputcsv($fp, ['Device User ID', 'DateTime', 'Status', 'Verify Mode', 'Work Code', 'Raw Data']);
    
    // Write records
    foreach ($records as $record) {
        fputcsv($fp, [
            $record['device_user_id'],
            $record['datetime'],
            $record['status'] ?? '',
            $record['verify_mode'] ?? '',
            $record['work_code'] ?? '',
            $record['raw_line'],
        ]);
    }
    
    fclose($fp);
    echo "\n=== Export Complete ===\n";
    echo "CSV file saved to: $csvFile\n";
}

