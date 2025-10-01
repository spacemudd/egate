<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Add Device - Biometric System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Add Biometric Device</h1>
                        <p class="mt-2 text-sm text-gray-600">Create a new biometric device entry</p>
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
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <form method="POST" action="{{ route('biometric.devices.store') }}" class="space-y-6">
                        @csrf
                        
                        <!-- Basic Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="serial_number" class="block text-sm font-medium text-gray-700">
                                    Serial Number <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="serial_number" id="serial_number" required
                                       value="{{ old('serial_number') }}"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('serial_number') border-red-300 @enderror">
                                @error('serial_number')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="device_name" class="block text-sm font-medium text-gray-700">
                                    Device Name
                                </label>
                                <input type="text" name="device_name" id="device_name"
                                       value="{{ old('device_name') }}"
                                       placeholder="e.g., Main Office Terminal"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('device_name') border-red-300 @enderror">
                                @error('device_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Device Type and Status -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="device_type" class="block text-sm font-medium text-gray-700">
                                    Device Type <span class="text-red-500">*</span>
                                </label>
                                <select name="device_type" id="device_type" required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('device_type') border-red-300 @enderror">
                                    <option value="">Select Device Type</option>
                                    <option value="zkteco" {{ old('device_type') == 'zkteco' ? 'selected' : '' }}>ZKTeco</option>
                                    <option value="honeywell" {{ old('device_type') == 'honeywell' ? 'selected' : '' }}>Honeywell</option>
                                    <option value="morpho" {{ old('device_type') == 'morpho' ? 'selected' : '' }}>Morpho</option>
                                    <option value="other" {{ old('device_type') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('device_type')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">
                                    Status
                                </label>
                                <select name="status" id="status"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('status') border-red-300 @enderror">
                                    <option value="offline" {{ old('status', 'offline') == 'offline' ? 'selected' : '' }}>Offline</option>
                                    <option value="online" {{ old('status') == 'online' ? 'selected' : '' }}>Online</option>
                                    <option value="error" {{ old('status') == 'error' ? 'selected' : '' }}>Error</option>
                                </select>
                                @error('status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Network Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="ip_address" class="block text-sm font-medium text-gray-700">
                                    IP Address
                                </label>
                                <input type="text" name="ip_address" id="ip_address"
                                       value="{{ old('ip_address') }}"
                                       placeholder="192.168.1.100"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('ip_address') border-red-300 @enderror">
                                @error('ip_address')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="mac_address" class="block text-sm font-medium text-gray-700">
                                    MAC Address
                                </label>
                                <input type="text" name="mac_address" id="mac_address"
                                       value="{{ old('mac_address') }}"
                                       placeholder="00:11:22:33:44:55"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('mac_address') border-red-300 @enderror">
                                @error('mac_address')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Device Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="firmware_version" class="block text-sm font-medium text-gray-700">
                                    Firmware Version
                                </label>
                                <input type="text" name="firmware_version" id="firmware_version"
                                       value="{{ old('firmware_version') }}"
                                       placeholder="e.g., 6.60.1.0"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('firmware_version') border-red-300 @enderror">
                                @error('firmware_version')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="language" class="block text-sm font-medium text-gray-700">
                                    Language
                                </label>
                                <input type="text" name="language" id="language"
                                       value="{{ old('language') }}"
                                       placeholder="e.g., 69"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('language') border-red-300 @enderror">
                                @error('language')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Push Version -->
                        <div>
                            <label for="push_version" class="block text-sm font-medium text-gray-700">
                                Push Version
                            </label>
                            <input type="text" name="push_version" id="push_version"
                                   value="{{ old('push_version') }}"
                                   placeholder="e.g., 2.4.1"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('push_version') border-red-300 @enderror">
                            @error('push_version')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">
                                Notes
                            </label>
                            <textarea name="notes" id="notes" rows="3"
                                      placeholder="Additional notes about the device..."
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('notes') border-red-300 @enderror">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('biometric.devices.index') }}" 
                               class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                Create Device
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
