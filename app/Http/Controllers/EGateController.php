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
     */
    public function handleRequest(Request $request): JsonResponse
    {
        try {
            // Get the method from query parameter
            $method = $request->query('method');
            
            if (!$method) {
                return response()->json(['error' => 'Method parameter is required'], 400);
            }

            // Store the request data
            $egateRequest = $this->storeRequest($request, $method);
            
            // Handle different request types
            switch ($method) {
                case 'GetStatus':
                    return $this->handleHeartbeat($request, $egateRequest);
                case 'SearchCardAcs':
                    return $this->handleAccessControl($request, $egateRequest);
                default:
                    return response()->json(['error' => 'Unknown method'], 400);
            }
        } catch (\Exception $e) {
            Log::error('EGate request error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Handle heartbeat requests (GetStatus)
     */
    private function handleHeartbeat(Request $request, EGateRequest $egateRequest): JsonResponse
    {
        // Extract key from request
        $key = $request->input('Key') ?? $request->query('Key');
        
        if (!$key) {
            $egateRequest->update([
                'response_status' => 'error',
                'response_data' => ['error' => 'Key parameter missing']
            ]);
            return response()->json(['error' => 'Key parameter is required'], 400);
        }

        // Basic heartbeat response - just return the key
        $response = ['Key' => $key];
        
        // Store response
        $egateRequest->update([
            'response_status' => 'success',
            'response_data' => $response
        ]);

        return response()->json($response);
    }

    /**
     * Handle access control requests (SearchCardAcs)
     */
    private function handleAccessControl(Request $request, EGateRequest $egateRequest): JsonResponse
    {
        // Extract key from request
        $key = $request->input('Key') ?? $request->query('Key');
        
        if (!$key) {
            $egateRequest->update([
                'response_status' => 'error',
                'response_data' => ['error' => 'Key parameter missing']
            ]);
            return response()->json(['error' => 'Key parameter is required'], 400);
        }

        // Get request parameters
        $type = $request->input('type') ?? $request->query('type');
        $reader = $request->input('Reader') ?? $request->query('Reader');
        $card = $request->input('Card') ?? $request->query('Card');
        
        // Determine access control response based on business logic
        // For now, we'll implement a simple logic - you can customize this
        $accessGranted = $this->evaluateAccess($type, $card, $reader);
        
        // Build response
        $response = [
            'Key' => $key,
            'AcsRes' => $accessGranted ? '1' : '0', // 1 = open, 0 = reject
            'ActIndex' => $reader ?? '0', // 0 = entry, 1 = exit
            'Time' => $accessGranted ? '3' : '0', // Relay action time in seconds
        ];

        // Add optional display fields if access is granted
        if ($accessGranted) {
            $response['Name'] = 'Authorized User';
            $response['Note'] = 'Access granted';
            $response['Voice'] = 'Welcome!';
            $response['Systime'] = now()->format('Y-m-d H:i:s');
        }

        // Store response
        $egateRequest->update([
            'response_status' => $accessGranted ? '1' : '0',
            'response_data' => $response
        ]);

        return response()->json($response);
    }

    /**
     * Store the incoming request in database
     */
    private function storeRequest(Request $request, string $method): EGateRequest
    {
        $data = [
            'method' => $method,
            'type' => $request->input('type') ?? $request->query('type'),
            'serial' => $request->input('Serial') ?? $request->query('Serial'),
            'device_id' => $request->input('ID') ?? $request->query('ID'),
            'mac_address' => $request->input('MAC') ?? $request->query('MAC'),
            'ip_address' => $request->input('IP') ?? $request->query('IP'),
            'reader' => $request->input('Reader') ?? $request->query('Reader'),
            'source' => $request->input('Source') ?? $request->query('Source'),
            'status' => $request->input('Status') ?? $request->query('Status'),
            'input' => $request->input('Input') ?? $request->query('Input'),
            'card' => $request->input('Card') ?? $request->query('Card'),
            'data' => $request->input('data') ?? $request->query('data'),
            'index' => $request->input('Index') ?? $request->query('Index'),
            'key' => $request->input('Key') ?? $request->query('Key'),
            'now' => $request->input('Now') ?? $request->query('Now'),
            'crc' => $request->input('Crc') ?? $request->query('Crc'),
            't1' => $request->input('T1') ?? $request->query('T1'),
            'h1' => $request->input('H1') ?? $request->query('H1'),
            't2' => $request->input('T2') ?? $request->query('T2'),
            'h2' => $request->input('H2') ?? $request->query('H2'),
            'next_num' => $request->input('NextNum') ?? $request->query('NextNum'),
            'ver' => $request->input('Ver') ?? $request->query('Ver'),
            'will_pass' => $request->input('WillPass') ?? $request->query('WillPass'),
            'passed' => $request->input('Passed') ?? $request->query('Passed'),
            'modbus' => $request->input('Modbus') ?? $request->query('Modbus'),
            'order_code' => $request->input('OrderCode') ?? $request->query('OrderCode'),
            'request_data' => $request->all(),
        ];

        return EGateRequest::create($data);
    }

    /**
     * Evaluate access based on business logic
     * This is where you implement your access control rules
     */
    private function evaluateAccess(?string $type, ?string $card, ?string $reader): bool
    {
        // For demonstration purposes, we'll implement some basic logic
        // You should replace this with your actual business logic
        
        // If no card data, deny access
        if (empty($card)) {
            return false;
        }

        // Example: Allow access for card numbers starting with '123'
        if (str_starts_with($card, '123')) {
            return true;
        }

        // Example: Allow access for QR codes (type 1 or 9)
        if (in_array($type, ['1', '9']) && !empty($card)) {
            return true;
        }

        // Example: Allow access for button requests (type 3)
        if ($type === '3') {
            return true;
        }

        // Default: deny access
        return false;
    }

    /**
     * Show logs page
     */
    public function showLogs(Request $request)
    {
        $query = EGateRequest::query();
        
        // Filter by method
        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }
        
        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        // Filter by serial
        if ($request->filled('serial')) {
            $query->where('serial', 'like', '%' . $request->serial . '%');
        }
        
        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(50);
        
        return view('egate.logs', compact('requests'));
    }
}
