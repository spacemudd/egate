<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>User Details - {{ $user->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ $user->name }}</h1>
                        <p class="mt-2 text-sm text-gray-600">User details and attendance history</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('biometric.users.edit', $user) }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Edit User
                        </a>
                        <a href="{{ route('biometric.users.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Back to Users
                        </a>
                        <a href="/" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Home
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- User Information -->
                <div class="lg:col-span-2">
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                User Information
                            </h3>
                            
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Full Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $user->name }}</dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Device User ID</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $user->device_user_id }}</dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Employee ID</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $user->employee_id ?: 'Not set' }}</dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Department</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $user->department ?: 'Not set' }}</dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Position</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $user->position ?: 'Not set' }}</dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Card Number</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $user->card_number ?: 'Not set' }}</dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                                    <dd class="mt-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $user->active_status_color }}-100 text-{{ $user->active_status_color }}-800">
                                            {{ $user->active_status_label }}
                                        </span>
                                    </dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Device</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <a href="{{ route('biometric.devices.show', $user->device) }}" class="text-indigo-600 hover:text-indigo-500">
                                            {{ $user->device->device_name ?: $user->device->serial_number }}
                                        </a>
                                    </dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Last Sync</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $user->formatted_last_sync }}</dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Created</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $user->created_at->format('Y-m-d H:i:s') }}</dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $user->updated_at->format('Y-m-d H:i:s') }}</dd>
                                </div>
                            </dl>
                            
                            @if($user->notes)
                                <div class="mt-6">
                                    <dt class="text-sm font-medium text-gray-500">Notes</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $user->notes }}</dd>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Attendance Records -->
                    <div class="mt-6 bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    Recent Attendance ({{ $user->attendanceRecords->count() }})
                                </h3>
                                <a href="{{ route('biometric.attendance.index', ['user_id' => $user->id]) }}" 
                                   class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                    View All
                                </a>
                            </div>
                            
                            @if($user->attendanceRecords->count() > 0)
                                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                                    <table class="min-w-full divide-y divide-gray-300">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date/Time</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Verify Mode</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Work Code</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($user->attendanceRecords->take(20) as $record)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {{ $record->formatted_punch_time }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                            {{ $record->is_punch_in ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                            {{ $record->punch_type_label }}
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ $record->verify_mode_label }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ $record->status ?: '-' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ $record->work_code ?: '-' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-6">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No attendance records</h3>
                                    <p class="mt-1 text-sm text-gray-500">Attendance records will appear here when the user punches in/out.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- User Actions -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                User Actions
                            </h3>
                            
                            <div class="space-y-3">
                                <a href="{{ route('biometric.users.edit', $user) }}" 
                                   class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                    Edit User
                                </a>
                                
                                <a href="{{ route('biometric.attendance.index', ['user_id' => $user->id]) }}" 
                                   class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    View All Attendance
                                </a>
                                
                                <a href="{{ route('biometric.attendance.export', ['user_id' => $user->id, 'start_date' => now()->subMonth()->format('Y-m-d'), 'end_date' => now()->format('Y-m-d'), 'format' => 'excel']) }}" 
                                   class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    Export Last Month
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Device Information -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                Device Information
                            </h3>
                            
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Device Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <a href="{{ route('biometric.devices.show', $user->device) }}" class="text-indigo-600 hover:text-indigo-500">
                                            {{ $user->device->device_name ?: $user->device->serial_number }}
                                        </a>
                                    </dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Serial Number</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $user->device->serial_number }}</dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Device Type</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($user->device->device_type) }}</dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                                    <dd class="mt-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $user->device->status_color }}-100 text-{{ $user->device->status_color }}-800">
                                            {{ $user->device->status_label }}
                                        </span>
                                    </dd>
                                </div>
                                
                                @if($user->device->ip_address)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">IP Address</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $user->device->ip_address }}</dd>
                                    </div>
                                @endif
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Last Seen</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $user->device->formatted_last_seen }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                Statistics
                            </h3>
                            
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Total Records</dt>
                                    <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ $user->attendanceRecords->count() }}</dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Punch Ins</dt>
                                    <dd class="mt-1 text-lg font-medium text-green-600">{{ $user->attendanceRecords->where('punch_type', 'in')->count() }}</dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Punch Outs</dt>
                                    <dd class="mt-1 text-lg font-medium text-red-600">{{ $user->attendanceRecords->where('punch_type', 'out')->count() }}</dd>
                                </div>
                                
                                @php
                                    $lastPunch = $user->attendanceRecords->sortByDesc('punch_time')->first();
                                @endphp
                                
                                @if($lastPunch)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Last Punch</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ $lastPunch->punch_time->format('Y-m-d H:i') }}
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium ml-2 {{ $lastPunch->is_punch_in ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $lastPunch->punch_type_label }}
                                            </span>
                                        </dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
