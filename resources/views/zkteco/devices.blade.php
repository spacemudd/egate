<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ZKTeco Devices - Debug Page</title>
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
                        <h1 class="text-3xl font-bold text-gray-900">ZKTeco Fingerprint Devices</h1>
                        <p class="mt-2 text-sm text-gray-600">Monitor connected ZKTeco fingerprint devices and their status</p>
                    </div>
                    <div class="flex space-x-3">
                        <button onclick="refreshPage()" 
                                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Refresh
                        </button>
                        <a href="{{ route('zkteco.clear-cache') }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700"
                           onclick="return confirm('Are you sure you want to clear the device cache?')">
                            Clear Cache
                        </a>
                        <a href="{{ route('logs.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            View Logs
                        </a>
                        <a href="/" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Home
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Statistics Cards -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Connected Devices</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ count($devices) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Online Devices</dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        {{ collect($devices)->where('status', 'online')->count() }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Recent Activity</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ count($recentActivity) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Connected Devices -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            Connected Devices
                        </h3>
                        
                        @if(count($devices) > 0)
                            <div class="space-y-4">
                                @foreach($devices as $device)
                                    <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0">
                                                    <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                                                </div>
                                                <div class="ml-3">
                                                    <p class="text-sm font-medium text-gray-900">
                                                        {{ $device['serial'] }}
                                                    </p>
                                                    <p class="text-sm text-gray-500">
                                                        {{ $device['ip_address'] }} • {{ $device['device_type'] }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-xs text-gray-500">
                                                    Last seen: {{ \Carbon\Carbon::parse($device['last_seen'])->diffForHumans() }}
                                                </p>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Online
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-3 grid grid-cols-2 gap-4 text-xs text-gray-600">
                                            <div>
                                                <span class="font-medium">Language:</span> {{ $device['language'] }}
                                            </div>
                                            <div>
                                                <span class="font-medium">Version:</span> {{ $device['push_version'] }}
                                            </div>
                                            <div>
                                                <span class="font-medium">Options:</span> {{ $device['options'] }}
                                            </div>
                                            <div>
                                                <span class="font-medium">User Agent:</span> {{ $device['user_agent'] }}
                                            </div>
                                        </div>
                                        
                                        <div class="mt-3">
                                            <div class="flex items-center space-x-4">
                                                <button onclick="showDeviceDetails('{{ $device['serial'] }}')" 
                                                        class="text-indigo-600 hover:text-indigo-500 text-sm font-medium">
                                                    View Details
                                                </button>
                                                <form method="POST" action="{{ route('zkteco.device.sync', ['serial' => $device['serial']]) }}">
                                                    @csrf
                                                    <button type="submit" class="text-sm font-medium text-green-700 hover:text-green-600">
                                                        Sync
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No devices connected</h3>
                                <p class="mt-1 text-sm text-gray-500">ZKTeco devices will appear here when they connect to the server.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            Recent Activity
                        </h3>
                        
                        @if(count($recentActivity) > 0)
                            <div class="flow-root">
                                <ul class="-mb-8">
                                    @foreach($recentActivity as $index => $activity)
                                        <li>
                                            <div class="relative pb-8">
                                                @if($index < count($recentActivity) - 1)
                                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                                @endif
                                                <div class="relative flex space-x-3">
                                                    <div>
                                                        <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                            </svg>
                                                        </span>
                                                    </div>
                                                    <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                        <div>
                                                            <p class="text-sm text-gray-500">{{ $activity['message'] }}</p>
                                                        </div>
                                                        <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                            {{ \Carbon\Carbon::parse($activity['timestamp'])->diffForHumans() }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No recent activity</h3>
                                <p class="mt-1 text-sm text-gray-500">Activity will appear here when devices communicate with the server.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Device Details Modal -->
    <div id="deviceModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Device Details</h3>
                    <button onclick="closeDeviceModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div id="deviceDetails" class="space-y-3">
                    <!-- Device details will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script>
        function refreshPage() {
            window.location.reload();
        }

        function showDeviceDetails(serial) {
            fetch(`/zkteco/devices/${serial}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert('Device not found');
                        return;
                    }
                    
                    const details = document.getElementById('deviceDetails');
                    details.innerHTML = `
                        <div class="grid grid-cols-2 gap-4">
                            <div><strong>Serial Number:</strong> ${data.serial}</div>
                            <div><strong>IP Address:</strong> ${data.ip_address}</div>
                            <div><strong>Device Type:</strong> ${data.device_type}</div>
                            <div><strong>Language:</strong> ${data.language}</div>
                            <div><strong>Push Version:</strong> ${data.push_version}</div>
                            <div><strong>Options:</strong> ${data.options}</div>
                            <div><strong>Push Options Flag:</strong> ${data.push_options_flag}</div>
                            <div><strong>Status:</strong> <span class="text-green-600">${data.status}</span></div>
                            <div><strong>Last Seen:</strong> ${new Date(data.last_seen).toLocaleString()}</div>
                            <div><strong>User Agent:</strong> ${data.user_agent}</div>
                        </div>
                    `;
                    
                    document.getElementById('deviceModal').classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading device details');
                });
        }

        function closeDeviceModal() {
            document.getElementById('deviceModal').classList.add('hidden');
        }

        // Auto-refresh every 30 seconds
        setInterval(refreshPage, 30000);
    </script>
</body>
</html>
