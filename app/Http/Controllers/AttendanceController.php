<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\BiometricUser;
use App\Models\BiometricDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AttendanceExport;

class AttendanceController extends Controller
{
    /**
     * Display a listing of attendance records
     */
    public function index(Request $request)
    {
        $query = AttendanceRecord::with(['device', 'biometricUser']);

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('punch_time', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('punch_time', '<=', $request->end_date);
        }

        // Filter by device
        if ($request->filled('device_id')) {
            $query->where('device_id', $request->device_id);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('biometric_user_id', $request->user_id);
        }

        // Filter by punch type
        if ($request->filled('punch_type')) {
            $query->where('punch_type', $request->punch_type);
        }

        $records = $query->orderBy('punch_time', 'desc')->paginate(50);
        $devices = BiometricDevice::where('is_active', true)->get();
        $users = BiometricUser::where('is_active', true)->with('device')->get();

        return view('biometric.attendance.index', compact('records', 'devices', 'users'));
    }

    /**
     * Export attendance data to Excel
     */
    public function export(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'device_id' => 'nullable|exists:biometric_devices,id',
            'format' => 'required|in:xlsx,csv',
        ]);

        $query = AttendanceRecord::with(['device', 'biometricUser'])
            ->whereDate('punch_time', '>=', $request->start_date)
            ->whereDate('punch_time', '<=', $request->end_date);

        if ($request->filled('device_id')) {
            $query->where('device_id', $request->device_id);
        }

        $records = $query->orderBy('punch_time')->get();

        if ($records->isEmpty()) {
            return redirect()->back()->with('error', 'No attendance records found for the selected date range.');
        }

        $filename = 'attendance_' . $request->start_date . '_to_' . $request->end_date;
        
        // Determine the export type and add proper extension
        $format = $request->format ?? 'xlsx';
        $filename .= '.' . $format;
        
        // Map format to Excel type
        $excelType = match(strtolower($format)) {
            'csv' => \Maatwebsite\Excel\Excel::CSV,
            'xlsx' => \Maatwebsite\Excel\Excel::XLSX,
            default => \Maatwebsite\Excel\Excel::XLSX
        };

        try {
            return Excel::download(new AttendanceExport($records), $filename, $excelType);
        } catch (\Exception $e) {
            \Log::error('Excel export failed', [
                'error' => $e->getMessage(),
                'filename' => $filename,
                'format' => $format,
                'excelType' => $excelType,
                'records_count' => $records->count()
            ]);
            
            return redirect()->back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    /**
     * Generate attendance summary report
     */
    public function summary(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'device_id' => 'nullable|exists:biometric_devices,id',
        ]);

        $query = AttendanceRecord::with(['device', 'biometricUser'])
            ->whereDate('punch_time', '>=', $request->start_date)
            ->whereDate('punch_time', '<=', $request->end_date);

        if ($request->filled('device_id')) {
            $query->where('device_id', $request->device_id);
        }

        $records = $query->orderBy('punch_time')->get();

        // Group by user and date
        $summary = [];
        foreach ($records as $record) {
            $date = $record->punch_time->format('Y-m-d');
            $userId = $record->biometric_user_id;
            
            if (!isset($summary[$userId])) {
                $summary[$userId] = [
                    'user' => $record->biometricUser,
                    'dates' => [],
                ];
            }

            if (!isset($summary[$userId]['dates'][$date])) {
                $summary[$userId]['dates'][$date] = [
                    'punch_in' => null,
                    'punch_out' => null,
                    'total_hours' => 0,
                ];
            }

            // Determine punch type based on time
            if ($record->punch_time->format('H') < 16) {
                $summary[$userId]['dates'][$date]['punch_in'] = $record->punch_time;
            } else {
                $summary[$userId]['dates'][$date]['punch_out'] = $record->punch_time;
            }
        }

        // Calculate total hours for each day
        foreach ($summary as $userId => $userData) {
            foreach ($userData['dates'] as $date => $dateData) {
                if ($dateData['punch_in'] && $dateData['punch_out']) {
                    $summary[$userId]['dates'][$date]['total_hours'] = 
                        $dateData['punch_in']->diffInHours($dateData['punch_out']);
                }
            }
        }

        $devices = BiometricDevice::where('is_active', true)->get();

        return view('biometric.attendance.summary', compact('summary', 'devices'));
    }

    /**
     * Process attendance records (mark as processed)
     */
    public function process(Request $request)
    {
        $request->validate([
            'record_ids' => 'required|array',
            'record_ids.*' => 'exists:attendance_records,id',
        ]);

        AttendanceRecord::whereIn('id', $request->record_ids)
            ->update([
                'is_processed' => true,
                'processed_at' => now(),
            ]);

        return redirect()->route('biometric.attendance.index')
            ->with('success', 'Records processed successfully.');
    }
}
