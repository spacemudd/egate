<?php

namespace App\Http\Controllers;

use App\Models\BiometricDevice;
use App\Models\BiometricUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BiometricDeviceController extends Controller
{
    /**
     * Display a listing of biometric devices
     */
    public function index()
    {
        $devices = BiometricDevice::with('biometricUsers')
            ->orderBy('last_seen', 'desc')
            ->paginate(10);

        return view('biometric.devices.index', compact('devices'));
    }

    /**
     * Show the form for creating a new device
     */
    public function create()
    {
        return view('biometric.devices.create');
    }

    /**
     * Store a newly created device
     */
    public function store(Request $request)
    {
        $request->validate([
            'serial_number' => 'required|string|max:255|unique:biometric_devices',
            'device_name' => 'nullable|string|max:255',
            'device_type' => 'required|string|max:255',
            'ip_address' => 'nullable|ip',
            'mac_address' => 'nullable|string|max:255',
        ]);

        $device = BiometricDevice::create($request->all());

        return redirect()->route('biometric.devices.index')
            ->with('success', 'Device created successfully.');
    }

    /**
     * Display the specified device
     */
    public function show(BiometricDevice $device)
    {
        $device->load(['biometricUsers', 'attendanceRecords' => function($query) {
            $query->with('biometricUser')->latest()->limit(50);
        }]);

        return view('biometric.devices.show', compact('device'));
    }

    /**
     * Show the form for editing the device
     */
    public function edit(BiometricDevice $device)
    {
        return view('biometric.devices.edit', compact('device'));
    }

    /**
     * Update the specified device
     */
    public function update(Request $request, BiometricDevice $device)
    {
        $request->validate([
            'serial_number' => 'required|string|max:255|unique:biometric_devices,serial_number,' . $device->id,
            'device_name' => 'nullable|string|max:255',
            'device_type' => 'required|string|max:255',
            'ip_address' => 'nullable|ip',
            'mac_address' => 'nullable|string|max:255',
        ]);

        $device->update($request->all());

        return redirect()->route('biometric.devices.index')
            ->with('success', 'Device updated successfully.');
    }

    /**
     * Remove the specified device
     */
    public function destroy(BiometricDevice $device)
    {
        $device->delete();

        return redirect()->route('biometric.devices.index')
            ->with('success', 'Device deleted successfully.');
    }

    /**
     * Sync users from cache (ZKTeco devices)
     */
    public function syncFromCache()
    {
        $cachedDevices = Cache::store('file')->get('zkteco_devices', []);
        
        foreach ($cachedDevices as $serial => $deviceData) {
            $device = BiometricDevice::updateOrCreate(
                ['serial_number' => $serial],
                [
                    'device_name' => $deviceData['device_type'] ?? null,
                    'device_type' => 'zkteco',
                    'ip_address' => $deviceData['ip_address'] ?? null,
                    'firmware_version' => $deviceData['push_version'] ?? null,
                    'language' => $deviceData['language'] ?? null,
                    'push_version' => $deviceData['push_version'] ?? null,
                    'device_options' => [
                        'options' => $deviceData['options'] ?? null,
                        'push_options_flag' => $deviceData['push_options_flag'] ?? null,
                    ],
                    'status' => $deviceData['status'] ?? 'offline',
                    'last_seen' => $deviceData['last_seen'] ?? null,
                ]
            );
        }

        return redirect()->route('biometric.devices.index')
            ->with('success', 'Devices synced from cache successfully.');
    }
}
