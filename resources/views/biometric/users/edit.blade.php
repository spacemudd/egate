<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Edit User - Biometric System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Edit User</h1>
                        <p class="mt-2 text-sm text-gray-600">Update user information for {{ $user->name }}</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('biometric.users.show', $user) }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            View User
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
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <form method="POST" action="{{ route('biometric.users.update', $user) }}" class="space-y-6">
                        @csrf
                        @method('PUT')
                        
                        <!-- Device Selection -->
                        <div>
                            <label for="device_id" class="block text-sm font-medium text-gray-700">
                                Device <span class="text-red-500">*</span>
                            </label>
                            <select name="device_id" id="device_id" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('device_id') border-red-300 @enderror">
                                <option value="">Select Device</option>
                                @foreach($devices as $device)
                                    <option value="{{ $device->id }}" {{ old('device_id', $user->device_id) == $device->id ? 'selected' : '' }}>
                                        {{ $device->device_name ?: $device->serial_number }} ({{ $device->serial_number }})
                                    </option>
                                @endforeach
                            </select>
                            @error('device_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- User Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="device_user_id" class="block text-sm font-medium text-gray-700">
                                    Device User ID <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="device_user_id" id="device_user_id" required
                                       value="{{ old('device_user_id', $user->device_user_id) }}"
                                       placeholder="e.g., 1, 2, 3..."
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('device_user_id') border-red-300 @enderror">
                                @error('device_user_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500">The user ID from the biometric device</p>
                            </div>

                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">
                                    Full Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" id="name" required
                                       value="{{ old('name', $user->name) }}"
                                       placeholder="e.g., John Doe"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-300 @enderror">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Employee Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="employee_id" class="block text-sm font-medium text-gray-700">
                                    Employee ID
                                </label>
                                <input type="text" name="employee_id" id="employee_id"
                                       value="{{ old('employee_id', $user->employee_id) }}"
                                       placeholder="e.g., EMP001"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('employee_id') border-red-300 @enderror">
                                @error('employee_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500">Employee badge number or ID</p>
                            </div>

                            <div>
                                <label for="department" class="block text-sm font-medium text-gray-700">
                                    Department
                                </label>
                                <input type="text" name="department" id="department"
                                       value="{{ old('department', $user->department) }}"
                                       placeholder="e.g., IT, HR, Finance"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('department') border-red-300 @enderror">
                                @error('department')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Position and Card -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="position" class="block text-sm font-medium text-gray-700">
                                    Position
                                </label>
                                <input type="text" name="position" id="position"
                                       value="{{ old('position', $user->position) }}"
                                       placeholder="e.g., Software Developer, Manager"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('position') border-red-300 @enderror">
                                @error('position')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="card_number" class="block text-sm font-medium text-gray-700">
                                    Card Number
                                </label>
                                <input type="text" name="card_number" id="card_number"
                                       value="{{ old('card_number', $user->card_number) }}"
                                       placeholder="e.g., 1234567890"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('card_number') border-red-300 @enderror">
                                @error('card_number')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500">RFID card number if applicable</p>
                            </div>
                        </div>

                        <!-- Biometric Templates -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="fingerprint_template" class="block text-sm font-medium text-gray-700">
                                    Fingerprint Template
                                </label>
                                <input type="text" name="fingerprint_template" id="fingerprint_template"
                                       value="{{ old('fingerprint_template', $user->fingerprint_template) }}"
                                       placeholder="Fingerprint template data"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('fingerprint_template') border-red-300 @enderror">
                                @error('fingerprint_template')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500">Usually auto-populated from device</p>
                            </div>

                            <div>
                                <label for="face_template" class="block text-sm font-medium text-gray-700">
                                    Face Template
                                </label>
                                <input type="text" name="face_template" id="face_template"
                                       value="{{ old('face_template', $user->face_template) }}"
                                       placeholder="Face recognition template data"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('face_template') border-red-300 @enderror">
                                @error('face_template')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500">Usually auto-populated from device</p>
                            </div>
                        </div>

                        <!-- Status -->
                        <div>
                            <div class="flex items-center">
                                <input type="checkbox" name="is_active" id="is_active" value="1"
                                       {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                    Active User
                                </label>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">Uncheck to deactivate this user</p>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">
                                Notes
                            </label>
                            <textarea name="notes" id="notes" rows="3"
                                      placeholder="Additional notes about the user..."
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('notes') border-red-300 @enderror">{{ old('notes', $user->notes) }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('biometric.users.show', $user) }}" 
                               class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                Update User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
