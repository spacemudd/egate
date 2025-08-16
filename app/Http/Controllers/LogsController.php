<?php

namespace App\Http\Controllers;

use App\Models\EGateRequest;
use Illuminate\Http\Request;

class LogsController extends Controller
{
    /**
     * Show the logs page with filtering options
     */
    public function index(Request $request)
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
        
        // Filter by response status
        if ($request->filled('response_status')) {
            $query->where('response_status', $request->response_status);
        }
        
        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Get statistics
        $totalRequests = EGateRequest::count();
        $totalHeartbeats = EGateRequest::heartbeat()->count();
        $totalAccessControl = EGateRequest::accessControl()->count();
        $totalGranted = EGateRequest::where('response_status', '1')->count();
        $totalRejected = EGateRequest::where('response_status', '0')->count();

        $requests = $query->orderBy('created_at', 'desc')->paginate(50);
        
        // Get unique values for filter dropdowns
        $methods = EGateRequest::distinct()->pluck('method')->filter();
        $types = EGateRequest::distinct()->pluck('type')->filter();
        $serials = EGateRequest::distinct()->pluck('serial')->filter();
        $responseStatuses = EGateRequest::distinct()->pluck('response_status')->filter();
        
        return view('egate.logs', compact(
            'requests',
            'totalRequests',
            'totalHeartbeats',
            'totalAccessControl',
            'totalGranted',
            'totalRejected',
            'methods',
            'types',
            'serials',
            'responseStatuses'
        ));
    }

    /**
     * Show detailed view of a specific request
     */
    public function show(EGateRequest $egateRequest)
    {
        return view('egate.show', compact('egateRequest'));
    }

    /**
     * Export logs to CSV
     */
    public function export(Request $request)
    {
        $query = EGateRequest::query();
        
        // Apply same filters as index
        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }
        
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        if ($request->filled('serial')) {
            $query->where('serial', 'like', '%' . $request->serial . '%');
        }
        
        if ($request->filled('response_status')) {
            $query->where('response_status', $request->response_status);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $requests = $query->orderBy('created_at', 'desc')->get();
        
        $filename = 'egate-logs-' . now()->format('Y-m-d-H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($requests) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID',
                'Method',
                'Type',
                'Serial',
                'Device ID',
                'MAC Address',
                'IP Address',
                'Reader',
                'Source',
                'Status',
                'Input',
                'Card',
                'Index',
                'Key',
                'Device Time',
                'CRC',
                'Temperature 1',
                'Humidity 1',
                'Temperature 2',
                'Humidity 2',
                'Next Number',
                'Version',
                'Will Pass',
                'Passed',
                'Response Status',
                'Created At'
            ]);
            
            // CSV data
            foreach ($requests as $request) {
                fputcsv($file, [
                    $request->id,
                    $request->method,
                    $request->type,
                    $request->serial,
                    $request->device_id,
                    $request->mac_address,
                    $request->ip_address,
                    $request->reader,
                    $request->source,
                    $request->status,
                    $request->input,
                    $request->card,
                    $request->index,
                    $request->key,
                    $request->now,
                    $request->crc,
                    $request->t1,
                    $request->h1,
                    $request->t2,
                    $request->h2,
                    $request->next_num,
                    $request->ver,
                    $request->will_pass,
                    $request->passed,
                    $request->response_status,
                    $request->created_at
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
