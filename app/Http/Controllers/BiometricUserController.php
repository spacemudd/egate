<?php

namespace App\Http\Controllers;

use App\Models\BiometricDevice;
use App\Models\BiometricUser;
use Illuminate\Http\Request;

class BiometricUserController extends Controller
{
    /**
     * Display a listing of biometric users
     */
    public function index(Request $request)
    {
        $query = BiometricUser::with('device');

        // Filter by device
        if ($request->filled('device_id')) {
            $query->where('device_id', $request->device_id);
        }

        // Filter by active status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Search by name or employee ID
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%")
                  ->orWhere('device_user_id', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')->paginate(20);
        $devices = BiometricDevice::where('is_active', true)->get();

        return view('biometric.users.index', compact('users', 'devices'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $devices = BiometricDevice::where('is_active', true)->get();
        return view('biometric.users.create', compact('devices'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $request->validate([
            'device_id' => 'required|exists:biometric_devices,id',
            'device_user_id' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'employee_id' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'card_number' => 'nullable|string|max:255',
        ]);

        // Check for duplicate user on same device
        $existingUser = BiometricUser::where('device_id', $request->device_id)
            ->where('device_user_id', $request->device_user_id)
            ->first();

        if ($existingUser) {
            return back()->withErrors(['device_user_id' => 'This user ID already exists on the selected device.']);
        }

        BiometricUser::create($request->all());

        return redirect()->route('biometric.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user
     */
    public function show(BiometricUser $user)
    {
        $user->load(['device', 'attendanceRecords' => function($query) {
            $query->latest()->limit(100);
        }]);

        return view('biometric.users.show', compact('user'));
    }

    /**
     * Show the form for editing the user
     */
    public function edit(BiometricUser $user)
    {
        $devices = BiometricDevice::where('is_active', true)->get();
        return view('biometric.users.edit', compact('user', 'devices'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, BiometricUser $user)
    {
        $request->validate([
            'device_id' => 'required|exists:biometric_devices,id',
            'device_user_id' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'employee_id' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'card_number' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        // Check for duplicate user on same device (excluding current user)
        $existingUser = BiometricUser::where('device_id', $request->device_id)
            ->where('device_user_id', $request->device_user_id)
            ->where('id', '!=', $user->id)
            ->first();

        if ($existingUser) {
            return back()->withErrors(['device_user_id' => 'This user ID already exists on the selected device.']);
        }

        $user->update($request->all());

        return redirect()->route('biometric.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user
     */
    public function destroy(BiometricUser $user)
    {
        $user->delete();

        return redirect()->route('biometric.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Bulk import users from device data
     */
    public function bulkImport(Request $request)
    {
        $request->validate([
            'device_id' => 'required|exists:biometric_devices,id',
            'users_data' => 'required|string',
        ]);

        $device = BiometricDevice::findOrFail($request->device_id);
        $lines = explode("\n", trim($request->users_data));
        $imported = 0;
        $errors = [];

        foreach ($lines as $lineNumber => $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $parts = explode("\t", $line);
            if (count($parts) < 2) {
                $errors[] = "Line " . ($lineNumber + 1) . ": Invalid format";
                continue;
            }

            $deviceUserId = $parts[0];
            $name = $parts[1] ?? "User {$deviceUserId}";
            $employeeId = $parts[2] ?? null;

            try {
                BiometricUser::updateOrCreate(
                    [
                        'device_id' => $device->id,
                        'device_user_id' => $deviceUserId,
                    ],
                    [
                        'name' => $name,
                        'employee_id' => $employeeId,
                        'last_sync' => now(),
                    ]
                );
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Line " . ($lineNumber + 1) . ": " . $e->getMessage();
            }
        }

        $message = "Imported {$imported} users successfully.";
        if (!empty($errors)) {
            $message .= " Errors: " . implode(", ", $errors);
        }

        return redirect()->route('biometric.users.index', ['device_id' => $device->id])
            ->with('success', $message);
    }
}
