<?php

namespace App\Http\Controllers;

use App\Models\EGateRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class EGateController extends Controller
{
    /**
     * Handle eGate requests (both heartbeat and access control)
     * BULLETPROOF AUDIT: Logs EVERYTHING that comes in, no matter what
     */
    public function handleRequest(Request $request): JsonResponse
    {
        // STEP 1: IMMEDIATE AUDIT LOG - Capture EVERYTHING before ANY processing
        $auditData = $this->captureCompleteAuditData($request);
        
        // STEP 2: Store the raw request data immediately (audit trail)
        $egateRequest = $this->storeRawRequest($request, $auditData);
        
        try {
            // STEP 3: Now try to process the request
            $method = $request->query('method');
            
            if (!$method) {
                // Log the missing method error but STILL log everything
                $this->logValidationError($egateRequest, 'Method parameter is required', $auditData);
                return response()->json(['error' => 'Method parameter is required'], 400);
            }

            // STEP 4: Handle different request types
            switch ($method) {
                case 'GetStatus':
                    return $this->handleHeartbeat($request, $egateRequest, $auditData);
                case 'SearchCardAcs':
                    return $this->handleAccessControl($request, $egateRequest, $auditData);
                default:
                    // Log the unknown method error but STILL log everything
                    $this->logValidationError($egateRequest, 'Unknown method: ' . $method, $auditData);
                    return response()->json(['error' => 'Unknown method'], 400);
            }
            
        } catch (\Exception $e) {
            // STEP 5: Log ANY exception that occurs
            $this->logException($egateRequest, $e, $auditData);
            
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Capture COMPLETE audit data from the request - EVERYTHING
     */
    private function captureCompleteAuditData(Request $request): array
    {
        return [
            'timestamp' => now()->toISOString(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'path' => $request->path(),
            'query_string' => $request->getQueryString(),
            'query_params' => $request->query(),
            'post_data' => $request->post(),
            'raw_body' => $request->getContent(),
            'headers' => $request->headers->all(),
            'cookies' => $request->cookies->all(),
            'server_vars' => $request->server(),
            'request_size' => $request->header('Content-Length'),
            'referer' => $request->header('Referer'),
            'accept' => $request->header('Accept'),
            'content_type' => $request->header('Content-Type'),
            'connection' => $request->header('Connection'),
            'host' => $request->header('Host'),
            'x_forwarded_for' => $request->header('X-Forwarded-For'),
            'x_real_ip' => $request->header('X-Real-IP'),
            'cf_connecting_ip' => $request->header('CF-Connecting-IP'),
            'cf_ray' => $request->header('CF-Ray'),
            'cf_ipcountry' => $request->header('CF-IPCountry'),
            'request_id' => uniqid('req_', true),
            'microtime' => microtime(true),
        ];
    }

    /**
     * Store the raw request data immediately for audit purposes - NO VALIDATION
     */
    private function storeRawRequest(Request $request, array $auditData): EGateRequest
    {
        // Extract what we can from the request, even if incomplete or malformed
        $method = $request->query('method') ?? $request->input('method') ?? 'UNKNOWN';
        $type = $request->query('type') ?? $request->input('type') ?? null;
        $serial = $request->query('Serial') ?? $request->input('Serial') ?? null;
        $deviceId = $request->query('ID') ?? $request->input('ID') ?? null;
        $macAddress = $request->query('MAC') ?? $request->input('MAC') ?? null;
        $ipAddress = $request->query('IP') ?? $request->input('IP') ?? $request->ip();
        $reader = $request->query('Reader') ?? $request->input('Reader') ?? null;
        $source = $request->query('Source') ?? $request->input('Source') ?? null;
        $status = $request->query('Status') ?? $request->input('Status') ?? null;
        $input = $request->query('Input') ?? $request->input('Input') ?? null;
        $card = $request->query('Card') ?? $request->input('Card') ?? null;
        $data = $request->query('data') ?? $request->input('data') ?? null;
        $index = $request->query('Index') ?? $request->input('Index') ?? null;
        $key = $request->query('Key') ?? $request->input('Key') ?? null;
        $now = $request->query('Now') ?? $request->input('Now') ?? null;
        $crc = $request->query('Crc') ?? $request->input('Crc') ?? null;
        $t1 = $request->query('T1') ?? $request->input('T1') ?? null;
        $h1 = $request->query('H1') ?? $request->input('H1') ?? null;
        $t2 = $request->query('T2') ?? $request->input('T2') ?? null;
        $h2 = $request->query('H2') ?? $request->input('H2') ?? null;
        $nextNum = $request->query('NextNum') ?? $request->input('NextNum') ?? null;
        $ver = $request->query('Ver') ?? $request->input('Ver') ?? null;
        $willPass = $request->query('WillPass') ?? $request->input('WillPass') ?? null;
        $passed = $request->query('Passed') ?? $request->input('Passed') ?? null;
        $modbus = $request->query('Modbus') ?? $request->input('Modbus') ?? null;
        $orderCode = $request->query('OrderCode') ?? $request->input('OrderCode') ?? null;

        // Store EVERYTHING, even if it's garbage data
        return EGateRequest::create([
            'method' => $method,
            'type' => $type,
            'serial' => $serial,
            'device_id' => $deviceId,
            'mac_address' => $macAddress,
            'ip_address' => $ipAddress,
            'reader' => $reader,
            'source' => $source,
            'status' => $status,
            'input' => $input,
            'card' => $card,
            'data' => $data,
            'index' => $index,
            'key' => $key,
            'now' => $now,
            'crc' => $crc,
            't1' => $t1,
            'h1' => $h1,
            't2' => $t2,
            'h2' => $h2,
            'next_num' => $nextNum,
            'ver' => $ver,
            'will_pass' => $willPass,
            'passed' => $passed,
            'modbus' => $modbus,
            'order_code' => $orderCode,
            'request_data' => $auditData, // Store the complete audit data
            'response_status' => 'processing', // Mark as processing
        ]);
    }

    /**
     * Log validation errors with full audit context
     */
    private function logValidationError(EGateRequest $egateRequest, string $error, array $auditData): void
    {
        $egateRequest->update([
            'response_status' => 'validation_error',
            'response_data' => [
                'error' => $error,
                'error_type' => 'validation',
                'audit_context' => $auditData,
                'timestamp' => now()->toISOString()
            ]
        ]);

        Log::warning('eGate validation error: ' . $error, [
            'request_id' => $egateRequest->id,
            'audit_data' => $auditData,
            'error_type' => 'validation'
        ]);
    }

    /**
     * Log exceptions with full audit context
     */
    private function logException(EGateRequest $egateRequest, \Exception $e, array $auditData): void
    {
        $errorData = [
            'exception' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'audit_context' => $auditData,
            'timestamp' => now()->toISOString()
        ];

        $egateRequest->update([
            'response_status' => 'exception',
            'response_data' => $errorData
        ]);

        Log::error('eGate request exception: ' . $e->getMessage(), [
            'request_id' => $egateRequest->id,
            'audit_data' => $auditData,
            'exception' => $errorData
        ]);
    }

    /**
     * Handle heartbeat requests (GetStatus)
     */
    private function handleHeartbeat(Request $request, EGateRequest $egateRequest, array $auditData): JsonResponse
    {
        try {
            // Extract key from request
            $key = $request->input('Key') ?? $request->query('Key');
            
            if (!$key) {
                // Log the missing key error but STILL log everything
                $this->logValidationError($egateRequest, 'Key parameter missing in heartbeat', $auditData);
                return response()->json(['error' => 'Key parameter is required'], 400);
            }

            // Basic heartbeat response - just return the key
            $response = ['Key' => $key];
            
            // Store successful response
            $egateRequest->update([
                'response_status' => 'success',
                'response_data' => [
                    'response' => $response,
                    'audit_context' => $auditData,
                    'timestamp' => now()->toISOString()
                ]
            ]);

            return response()->json($response);
            
        } catch (\Exception $e) {
            $this->logException($egateRequest, $e, $auditData);
            return response()->json(['error' => 'Heartbeat processing error'], 500);
        }
    }

    /**
     * Handle access control requests (SearchCardAcs)
     */
    private function handleAccessControl(Request $request, EGateRequest $egateRequest, array $auditData): JsonResponse
    {
        try {
            // Extract key from request (optional for access control)
            $key = $request->input('Key') ?? $request->query('Key');

            // Get request parameters
            $type = $request->input('type') ?? $request->query('type');
            $reader = $request->input('Reader') ?? $request->query('Reader');
            $card = $request->input('Card') ?? $request->query('Card');
            
            // Determine access control response based on business logic
            $accessResult = $this->evaluateAccess($type, $card, $reader);
            
            // Build response
            $response = [
                'AcsRes' => $accessResult['granted'] ? '1' : '0', // 1 = open, 0 = reject
                'ActIndex' => $reader ?? '0', // 0 = entry, 1 = exit
                'Time' => $accessResult['granted'] ? '3' : '0', // Relay action time in seconds
            ];

            // Add Key if it was provided
            if ($key) {
                $response['Key'] = $key;
            }

            // Add optional display fields
            if ($accessResult['granted']) {
                $response['Name'] = $accessResult['name'] ?? 'Authorized User';
                $response['Note'] = 'Welcome';
                $response['Voice'] = $accessResult['voice'] ?? 'Welcome!';
                $response['Systime'] = now()->format('Y-m-d H:i:s');
            } else {
                $response['Name'] = 'Access Denied';
                $response['Note'] = 'Not Authorized';
                $response['Voice'] = 'Access denied';
            }

            // Store successful response
            $egateRequest->update([
                'response_status' => $accessResult['granted'] ? '1' : '0',
                'response_data' => [
                    'response' => $response,
                    'audit_context' => $auditData,
                    'access_result' => $accessResult,
                    'timestamp' => now()->toISOString()
                ]
            ]);

            return response()->json($response);
            
        } catch (\Exception $e) {
            $this->logException($egateRequest, $e, $auditData);
            return response()->json(['error' => 'Access control processing error'], 500);
        }
    }

    /**
     * Evaluate access based on business logic
     * This is where you implement your access control rules
     */
    private function evaluateAccess(?string $type, ?string $card, ?string $reader): array
    {
        // Initialize default result
        $result = [
            'granted' => false,
            'name' => null,
            'voice' => null,
            'reason' => 'Unknown'
        ];
        
        // If no card data, deny access
        if (empty($card)) {
            $result['reason'] = 'No card data provided';
            return $result;
        }

        // Handle QR codes (type 1 or 9)
        if (in_array($type, ['1', '9'])) {
            return $this->evaluateQRCode($card);
        }

        // Example: Allow access for card numbers starting with '123'
        if (str_starts_with($card, '123')) {
            $result['granted'] = true;
            $result['name'] = 'Card User';
            $result['voice'] = 'Welcome to the building';
            $result['reason'] = 'Valid card number';
            return $result;
        }

        // Example: Allow access for button requests (type 3)
        if ($type === '3') {
            $result['granted'] = true;
            $result['name'] = 'Button User';
            $result['voice'] = 'Door opened by button';
            $result['reason'] = 'Button request';
            return $result;
        }

        // Default: deny access
        $result['reason'] = 'No matching access rule';
        return $result;
    }

    /**
     * Evaluate QR code access patterns
     */
    private function evaluateQRCode(string $card): array
    {
        // Initialize default result
        $result = [
            'granted' => false,
            'name' => null,
            'voice' => null,
            'reason' => 'Invalid QR code'
        ];

        try {
            // Decode base64 QR code data
            $decodedCard = base64_decode($card, true);
            
            // If base64 decode fails, try using the card data directly
            if ($decodedCard === false) {
                $decodedCard = $card;
            }

            Log::info('QR Code decoded', ['original' => $card, 'decoded' => $decodedCard]);

            // Check for programmers-allow-{name} pattern
            if (preg_match('/^programmers-allow-(.+)$/i', $decodedCard, $matches)) {
                $name = trim($matches[1]);
                
                if (!empty($name)) {
                    $result['granted'] = true;
                    $result['name'] = ucfirst($name);
                    $result['voice'] = "Welcome {$name} to the building";
                    $result['reason'] = "Programmer access granted for {$name}";
                    
                    Log::info('Access granted for programmer', ['name' => $name, 'pattern' => 'allow']);
                    return $result;
                }
            }

            // Check for programmers-deny-{name} pattern
            if (preg_match('/^programmers-deny-(.+)$/i', $decodedCard, $matches)) {
                $name = trim($matches[1]);
                
                $result['granted'] = false;
                $result['name'] = ucfirst($name);
                $result['voice'] = "Access denied for {$name}";
                $result['reason'] = "Programmer access denied for {$name}";
                
                Log::info('Access denied for programmer', ['name' => $name, 'pattern' => 'deny']);
                return $result;
            }

            // For other QR codes, log and deny
            Log::info('QR code does not match programmer patterns', ['decoded' => $decodedCard]);
            $result['reason'] = 'QR code does not match required patterns';
            
        } catch (\Exception $e) {
            Log::error('Error processing QR code', ['card' => $card, 'error' => $e->getMessage()]);
            $result['reason'] = 'Error processing QR code';
        }

        return $result;
    }
}
