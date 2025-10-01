<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Biometric Devices - eGate System</title>
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
                        <h1 class="text-3xl font-bold text-gray-900">Biometric Devices</h1>
                        <p class="mt-2 text-sm text-gray-600">Manage biometric devices and their configurations</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('biometric.devices.create') }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            Add Device
                        </a>
                        <form method="POST" action="{{ route('biometric.devices.sync-from-cache') }}" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                    onclick="return confirm('Sync devices from ZKTeco cache?')">
                                Sync from Cache
                            </button>
                        </form>
                        <a href="{{ route('biometric.import.logs') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Import from Logs
                        </a>
                        <a href="{{ route('zkteco.devices') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            ZKTeco Devices
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

            @if(session('warning'))
                <div class="mb-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                    {{ session('warning') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            @if($devices->count() > 0)
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="grid grid-cols-1 gap-4">
                            @foreach($devices as $device)
                                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div class="w-3 h-3 bg-{{ $device->status_color }}-400 rounded-full"></div>
                                            </div>
                                            <div class="ml-3">
                                                <h3 class="text-lg font-medium text-gray-900">
                                                    {{ $device->device_name ?: $device->serial_number }}
                                                </h3>
                                                <p class="text-sm text-gray-500">
                                                    {{ $device->serial_number }} • {{ $device->device_type }} • {{ $device->ip_address }}
                                                </p>
                                                <p class="text-xs text-gray-400">
                                                    Users: {{ $device->biometricUsers->count() }} • 
                                                    Last seen: {{ $device->formatted_last_seen }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $device->status_color }}-100 text-{{ $device->status_color }}-800">
                                                {{ $device->status_label }}
                                            </span>
                                            <div class="flex space-x-1">
                                                <a href="{{ route('biometric.devices.show', $device) }}" 
                                                   class="text-indigo-600 hover:text-indigo-500 text-sm font-medium">
                                                    View
                                                </a>
                                                <a href="{{ route('biometric.devices.edit', $device) }}" 
                                                   class="text-green-600 hover:text-green-500 text-sm font-medium">
                                                    Edit
                                                </a>
                                                <form method="POST" action="{{ route('biometric.devices.destroy', $device) }}" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="text-red-600 hover:text-red-500 text-sm font-medium"
                                                            onclick="return confirm('Are you sure you want to delete this device?')">
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            {{ $devices->links() }}
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No devices found</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by adding a new device or syncing from cache.</p>
                    <div class="mt-6 space-x-3">
                        <a href="{{ route('biometric.devices.create') }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            Add Device
                        </a>
                        <a href="{{ route('biometric.import.logs') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Import from Logs
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</body>
</html>
