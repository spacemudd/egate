<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Import from Logs - eGate System</title>
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
                        <h1 class="text-3xl font-bold text-gray-900">Import from Laravel Logs</h1>
                        <p class="mt-2 text-sm text-gray-600">Import SYZ8252100929 attendance data from Laravel logs</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('biometric.devices.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Back to Devices
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

            @if($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Import Form -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            Import SYZ8252100929 Data
                        </h3>
                        
                        <form method="POST" action="{{ route('biometric.import.logs') }}" class="space-y-4">
                            @csrf
                            
                            <div>
                                <label for="log_file_path" class="block text-sm font-medium text-gray-700">
                                    Log File Path
                                </label>
                                <input type="text" name="log_file_path" id="log_file_path" 
                                       value="{{ storage_path('logs/laravel.log') }}"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <p class="mt-1 text-sm text-gray-500">
                                    Default: {{ storage_path('logs/laravel.log') }}
                                </p>
                            </div>

                            <div>
                                <label for="device_serial" class="block text-sm font-medium text-gray-700">
                                    Device Serial Number
                                </label>
                                <input type="text" name="device_serial" id="device_serial" 
                                       value="SYZ8252100929"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <p class="mt-1 text-sm text-gray-500">
                                    Device serial to look for in the logs
                                </p>
                            </div>

                            <div class="flex justify-end space-x-3">
                                <button type="button" onclick="previewData()" 
                                        class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    Preview Data
                                </button>
                                <button type="submit" 
                                        class="px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                    Import Data
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            Import Instructions
                        </h3>
                        
                        <div class="space-y-4 text-sm text-gray-600">
                            <div>
                                <h4 class="font-medium text-gray-900">What this does:</h4>
                                <ul class="mt-2 list-disc list-inside space-y-1">
                                    <li>Scans Laravel logs for SYZ8252100929 device data</li>
                                    <li>Extracts ATTLOG records from log entries</li>
                                    <li>Creates biometric users and attendance records</li>
                                    <li>Automatically determines punch in/out based on time</li>
                                </ul>
                            </div>

                            <div>
                                <h4 class="font-medium text-gray-900">Data Format Expected:</h4>
                                <ul class="mt-2 list-disc list-inside space-y-1">
                                    <li>User ID (device user ID)</li>
                                    <li>Date and time (YYYY-MM-DD HH:MM:SS)</li>
                                    <li>Verification mode (fingerprint, card, etc.)</li>
                                    <li>Status and work code (optional)</li>
                                </ul>
                            </div>

                            <div>
                                <h4 class="font-medium text-gray-900">Punch Type Logic:</h4>
                                <ul class="mt-2 list-disc list-inside space-y-1">
                                    <li><strong>Before 4:00 PM:</strong> Punch In</li>
                                    <li><strong>After 4:00 PM:</strong> Punch Out</li>
                                </ul>
                            </div>

                            <div class="bg-blue-50 border border-blue-200 rounded-md p-3">
                                <p class="text-blue-800">
                                    <strong>Tip:</strong> Use "Preview Data" first to see what records will be imported before running the full import.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Existing Devices -->
            @if($devices->count() > 0)
                <div class="mt-6 bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            Existing Devices
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($devices as $device)
                                <div class="border border-gray-200 rounded-lg p-3">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-900">
                                                {{ $device->device_name ?: $device->serial_number }}
                                            </h4>
                                            <p class="text-xs text-gray-500">
                                                {{ $device->serial_number }} • {{ $device->biometricUsers->count() }} users
                                            </p>
                                        </div>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $device->status_color }}-100 text-{{ $device->status_color }}-800">
                                            {{ $device->status_label }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        function previewData() {
            const logPath = document.getElementById('log_file_path').value;
            const deviceSerial = document.getElementById('device_serial').value;
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("biometric.import.preview") }}';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            
            const logPathInput = document.createElement('input');
            logPathInput.type = 'hidden';
            logPathInput.name = 'log_file_path';
            logPathInput.value = logPath;
            
            const deviceSerialInput = document.createElement('input');
            deviceSerialInput.type = 'hidden';
            deviceSerialInput.name = 'device_serial';
            deviceSerialInput.value = deviceSerial;
            
            form.appendChild(csrfToken);
            form.appendChild(logPathInput);
            form.appendChild(deviceSerialInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>
</html>
