<?php

namespace App\Http\Controllers;

use App\Models\EGateRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class EGateController extends Controller
{
    /**
     * Handle eGate requests with AES128 decryption support
     */
    public function handleRequest(Request $request): JsonResponse
    {
        // STEP 1: IMMEDIATE AUDIT LOG - Capture EVERYTHING before ANY processing
        $auditData = $this->captureCompleteAuditData($request);
        
        // STEP 2: Check for encrypted data
        $decryptedData = $this->handleEncryptedRequest($request);
        if ($decryptedData) {
            // Replace request data with decrypted data
            $request = $this->createRequestFromDecryptedData($decryptedData, $request);
            $auditData['encrypted'] = true;
            $auditData['decrypted_successfully'] = true;
        }
        
        // STEP 3: Store the raw request data immediately (audit trail)
        $egateRequest = $this->storeRawRequest($request, $auditData);
        
        try {
            // Continue with existing logic...
            $method = $request->query('method') ?? $request->input('method');
            
            if (!$method) {
                $this->logValidationError($egateRequest, 'Method parameter is required', $auditData);
                return response()->json(['error' => 'Method parameter is required'], 400);
            }

            // Handle different request types
            switch ($method) {
                case 'GetStatus':
                    return $this->handleHeartbeat($request, $egateRequest, $auditData);
                case 'SearchCardAcs':
                    return $this->handleAccessControl($request, $egateRequest, $auditData);
                default:
                    $this->logValidationError($egateRequest, 'Unknown method: ' . $method, $auditData);
                    return response()->json(['error' => 'Unknown method'], 400);
            }
            
        } catch (\Exception $e) {
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
     * Handle encrypted requests using AES128
     */
    private function handleEncryptedRequest(Request $request): ?array
    {
        // Check for encrypted data parameter
        $encryptedData = $request->input('DATAS') ?? $request->query('DATAS');
        
        if (!$encryptedData) {
            return null; // Not an encrypted request
        }

        try {
            // Get the AES128 key from configuration
            $aesKey = config('egate.aes128_key');
            
            if (empty($aesKey)) {
                Log::error('EGate AES128 key not configured');
                throw new \Exception('AES128 encryption key not configured');
            }

            // Decrypt the data
            $decryptedJson = $this->decryptAES128($encryptedData, $aesKey);
            
            // Parse JSON
            $decryptedData = json_decode($decryptedJson, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON after decryption: ' . json_last_error_msg());
            }

            Log::info('Successfully decrypted AES128 payload', [
                'encrypted_length' => strlen($encryptedData),
                'decrypted_length' => strlen($decryptedJson),
                'has_method' => isset($decryptedData['method']) ? 'yes' : 'no'
            ]);

            return $decryptedData;
            
        } catch (\Exception $e) {
            Log::error('Failed to decrypt AES128 payload', [
                'error' => $e->getMessage(),
                'encrypted_data_length' => strlen($encryptedData),
                'key_configured' => !empty(config('egate.aes128_key'))
            ]);
            
            return null;
        }
    }

    /**
     * Decrypt AES128 encrypted data
     */
    private function decryptAES128(string $encryptedData, string $key): string
    {
        // Remove any whitespace and decode from hex or base64
        $encryptedData = trim($encryptedData);
        
        // Try to decode from hex first (common format from controller)
        if (ctype_xdigit($encryptedData)) {
            $binaryData = hex2bin($encryptedData);
        } else {
            // Try base64 decode
            $binaryData = base64_decode($encryptedData, true);
            if ($binaryData === false) {
                throw new \Exception('Invalid encrypted data format (not hex or base64)');
            }
        }
        
        // Prepare the key (pad or truncate to 16 bytes for AES128)
        $key = substr(str_pad($key, 16, "\0"), 0, 16);
        
        // For AES128, we need to handle the encryption mode
        // The controller likely uses ECB mode (common in embedded systems)
        $decrypted = openssl_decrypt($binaryData, 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
        
        if ($decrypted === false) {
            throw new \Exception('AES128 decryption failed: ' . openssl_error_string());
        }
        
        // Remove padding
        $decrypted = rtrim($decrypted, "\0");
        
        return $decrypted;
    }

    /**
     * Create a new request object from decrypted data
     */
    private function createRequestFromDecryptedData(array $decryptedData, Request $originalRequest): Request
    {
        // Merge decrypted data with original request
        $newData = array_merge($originalRequest->all(), $decryptedData);
        
        // Create new request with decrypted data
        $newRequest = new Request($newData);
        $newRequest->headers = $originalRequest->headers;
        $newRequest->server = $originalRequest->server;
        
        return $newRequest;
    }

    /**
     * Encrypt response data using AES128 (for encrypted responses)
     */
    private function encryptResponse(array $responseData): string
    {
        $aesKey = config('egate.aes128_key');
        
        if (empty($aesKey)) {
            throw new \Exception('AES128 encryption key not configured');
        }
        
        $jsonData = json_encode($responseData);
        $key = substr(str_pad($aesKey, 16, "\0"), 0, 16);
        
        // Encrypt using AES-128-ECB (to match controller expectations)
        $encrypted = openssl_encrypt($jsonData, 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
        
        if ($encrypted === false) {
            throw new \Exception('AES128 encryption failed: ' . openssl_error_string());
        }
        
        // Return as hex string (common format for controller)
        return strtoupper(bin2hex($encrypted));
    }

    /**
     * Handle heartbeat requests (GetStatus)
     */
    private function handleHeartbeat(Request $request, EGateRequest $egateRequest, array $auditData): JsonResponse
    {
        try {
            // Check if request was encrypted
            $isEncrypted = isset($auditData['encrypted']) && $auditData['encrypted'];
            
            // Extract key from request
            $key = $request->input('Key') ?? $request->query('Key');
            
            if (!$key) {
                $this->logValidationError($egateRequest, 'Key parameter missing in heartbeat', $auditData);
                return response()->json(['error' => 'Key parameter is required'], 400);
            }

            // Basic heartbeat response
            $response = ['Key' => $key];
            
            // Check for pending door control commands
            $pendingCommand = Cache::get('remote_door_command');
            if ($pendingCommand) {
                $response['AcsRes'] = $pendingCommand['acsRes'];
                $response['ActIndex'] = $pendingCommand['actIndex'];
                $response['Time'] = (string) $pendingCommand['duration'];
                $response['Voice'] = $pendingCommand['voice'];
                $response['Note'] = 'Remote control activated';
                
                // Clear the command after using it
                Cache::forget('remote_door_command');
                
                // Store command execution in database for history
                $this->storeDoorControlExecution($pendingCommand, $egateRequest);
                
                Log::info('Door control command executed via heartbeat', [
                    'command' => $pendingCommand,
                    'response' => $response,
                    'device_serial' => $egateRequest->serial,
                    'device_ip' => $egateRequest->ip_address
                ]);
            }
            
            // If request was encrypted, encrypt the response
            if ($isEncrypted) {
                $encryptedResponse = $this->encryptResponse($response);
                $responseBody = ['DATAS' => $encryptedResponse];
            } else {
                $responseBody = $response;
            }
            
            // Store successful response
            $egateRequest->update([
                'response_status' => 'success',
                'response_data' => [
                    'response' => $responseBody,
                    'encrypted' => $isEncrypted,
                    'audit_context' => $auditData,
                    'timestamp' => now()->toISOString()
                ]
            ]);

            return response()->json($responseBody);
            
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
            // Check if request was encrypted
            $isEncrypted = isset($auditData['encrypted']) && $auditData['encrypted'];
            
            // Get request parameters
            $type = $request->input('type') ?? $request->query('type');
            $reader = $request->input('Reader') ?? $request->query('Reader');
            $card = $request->input('Card') ?? $request->query('Card');
            
            // Determine access control response based on business logic
            $accessResult = $this->evaluateAccess($type, $card, $reader);
            
            // Build response
            $response = [
                'AcsRes' => $accessResult['granted'] ? '1' : '0',
                'ActIndex' => $reader ?? '0',
                'Time' => $accessResult['granted'] ? '3' : '0',
            ];

            // Add optional display fields
            if ($accessResult['granted']) {
                $response['Name'] = $accessResult['name'] ?? 'Authorized User';
                $response['Note'] = 'Welcome';
                $response['Voice'] = $accessResult['voice'] ?? 'Bobo';
                $response['Systime'] = now()->format('Y-m-d H:i:s');
            } else {
                $response['Name'] = 'Access Denied';
                $response['Note'] = 'Not Authorized';
                $response['Voice'] = 'Access denied';
            }

            // If request was encrypted, encrypt the response
            if ($isEncrypted) {
                $encryptedResponse = $this->encryptResponse($response);
                $responseBody = ['DATAS' => $encryptedResponse];
            } else {
                $responseBody = $response;
            }

            // Store successful response
            $egateRequest->update([
                'response_status' => $accessResult['granted'] ? '1' : '0',
                'response_data' => [
                    'response' => $responseBody,
                    'encrypted' => $isEncrypted,
                    'audit_context' => $auditData,
                    'access_result' => $accessResult,
                    'timestamp' => now()->toISOString()
                ]
            ]);

            return response()->json($responseBody);
            
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
                    // Voice message is just "Bobo" - no name included
                    $result['voice'] = "Bobo";
                    $result['reason'] = "Programmer access granted for {$name}";
                    
                    // Add debugging for voice message
                    Log::info('Voice message being sent', [
                        'voice' => $result['voice'], 
                        'length' => strlen($result['voice']),
                        'name' => $name
                    ]);
                    
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

    /**
     * Remote door control - queues a command for the next heartbeat
     */
    public function remoteDoorControl(Request $request): JsonResponse
    {
        try {
            $door = $request->input('door', '0'); // 0=entry, 1=exit, 2=both
            $duration = $request->input('duration', 5); // seconds
            $action = $request->input('action', 'open'); // open, close, alarm
            
            // Validate inputs
            if (!in_array($door, ['0', '1', '2'])) {
                return response()->json(['error' => 'Invalid door selection'], 400);
            }
            
            if ($duration < 1 || $duration > 30) {
                return response()->json(['error' => 'Duration must be between 1-30 seconds'], 400);
            }
            
            // Determine AcsRes based on action
            $acsRes = match($action) {
                'open' => $door === '2' ? '100' : '1', // 100 = fully open both, 1 = open specific
                'close' => '3',
                'alarm' => '2',
                default => '1'
            };
            
            // Store the command for the next heartbeat to pick up
            $command = [
                'action' => $action,
                'door' => $door,
                'duration' => $duration,
                'acsRes' => $acsRes,
                'actIndex' => $door === '2' ? '0' : $door, // For fully open, use 0
                'timestamp' => now()->toISOString(),
                'voice' => $this->getDoorActionVoice($action, $door),
                'user_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'command_id' => uniqid('cmd_', true)
            ];
            
            Cache::put('remote_door_command', $command, 60); // Expires in 60 seconds
            
            // Store the queued command in database for audit trail
            $this->storeDoorControlQueue($command, $request);
            
            Log::info('Remote door control command queued', [
                'command' => $command,
                'user_ip' => $request->ip()
            ]);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Door control command queued successfully',
                'command' => $command,
                'expires_in' => 60
            ]);
            
        } catch (\Exception $e) {
            Log::error('Remote door control error', [
                'error' => $e->getMessage(),
                'user_ip' => $request->ip()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to queue door control command'
            ], 500);
        }
    }
    
    /**
     * Get door control status
     */
    public function doorStatus(): JsonResponse
    {
        $pendingCommand = Cache::get('remote_door_command');
        
        return response()->json([
            'has_pending_command' => $pendingCommand !== null,
            'pending_command' => $pendingCommand,
            'cache_ttl' => $pendingCommand ? Cache::get('remote_door_command_ttl') : null
        ]);
    }
    
    /**
     * Get voice message for door action
     */
    private function getDoorActionVoice(string $action, string $door): string
    {
        $doorName = match($door) {
            '0' => 'entry door',
            '1' => 'exit door', 
            '2' => 'both doors',
            default => 'door'
        };
        
        return match($action) {
            'open' => "Opening {$doorName}",
            'close' => "Closing {$doorName}",
            'alarm' => "Alarm activated",
            default => "Door control activated"
        };
    }
    
    /**
     * Store door control command queue in database for audit trail
     */
    private function storeDoorControlQueue(array $command, Request $request): void
    {
        try {
            // Create a new EGateRequest record for the queued command
            EGateRequest::create([
                'method' => 'DoorControl',
                'type' => 'remote_queue',
                'serial' => 'WEB_INTERFACE',
                'device_id' => 'WEB_INTERFACE',
                'mac_address' => null,
                'ip_address' => $request->ip(),
                'reader' => $command['door'],
                'source' => 'web_interface',
                'status' => 'queued',
                'data' => json_encode($command),
                'response_status' => 'processing',
                'response_data' => [
                    'door_control' => [
                        'action' => $command['action'],
                        'door' => $command['door'],
                        'duration' => $command['duration'],
                        'queued_at' => now()->toISOString(),
                        'expires_at' => now()->addSeconds(60)->toISOString(),
                        'command_id' => $command['command_id'],
                        'user_ip' => $command['user_ip'],
                        'user_agent' => $command['user_agent']
                    ],
                    'timestamp' => now()->toISOString()
                ]
            ]);
            
            Log::info('Door control command queue stored in database', [
                'command_id' => $command['command_id'],
                'user_ip' => $request->ip()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to store door control queue', [
                'error' => $e->getMessage(),
                'command_id' => $command['command_id'] ?? 'unknown',
                'user_ip' => $request->ip()
            ]);
        }
    }
    
    /**
     * Store door control command execution in database for history tracking
     */
    private function storeDoorControlExecution(array $command, EGateRequest $egateRequest): void
    {
        try {
            // Create a new EGateRequest record specifically for the door control execution
            EGateRequest::create([
                'method' => 'DoorControl',
                'type' => 'remote_execution',
                'serial' => $egateRequest->serial,
                'device_id' => $egateRequest->device_id,
                'mac_address' => $egateRequest->mac_address,
                'ip_address' => $egateRequest->ip_address,
                'reader' => $command['door'],
                'source' => 'web_interface',
                'status' => 'executed',
                'data' => json_encode($command),
                'response_status' => 'success',
                'response_data' => [
                    'door_control' => [
                        'action' => $command['action'],
                        'door' => $command['door'],
                        'duration' => $command['duration'],
                        'executed_at' => now()->toISOString(),
                        'executed_via' => 'heartbeat',
                        'original_request_id' => $egateRequest->id,
                        'command_id' => $command['command_id'] ?? null
                    ],
                    'timestamp' => now()->toISOString()
                ]
            ]);
            
            Log::info('Door control execution stored in database', [
                'command' => $command,
                'original_request_id' => $egateRequest->id
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to store door control execution', [
                'error' => $e->getMessage(),
                'command' => $command,
                'original_request_id' => $egateRequest->id
            ]);
        }
    }
}
