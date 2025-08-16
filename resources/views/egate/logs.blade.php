<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eGate Logs - Access Control System</title>
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
                        <h1 class="text-3xl font-bold text-gray-900">eGate Access Control Logs</h1>
                        <p class="mt-2 text-sm text-gray-600">Monitor and analyze access control requests from eGate devices</p>
                    </div>
                    <div class="flex space-x-3">
                        <!-- Door Control Button -->
                        <button onclick="openDoorControlModal()" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                            </svg>
                            Open Door
                        </button>
                        <a href="{{ route('logs.export') }}?{{ request()->getQueryString() }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            Export CSV
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
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Requests</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ number_format($totalRequests) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Heartbeats</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ number_format($totalHeartbeats) }}</dd>
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
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Access Control</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ number_format($totalAccessControl) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-600 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Access Granted</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ number_format($totalGranted) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-red-600 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Access Rejected</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ number_format($totalRejected) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-6">
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Filters</h3>
                    <form method="GET" action="{{ route('logs.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label for="method" class="block text-sm font-medium text-gray-700">Method</label>
                            <select name="method" id="method" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="">All Methods</option>
                                @foreach($methods as $method)
                                    <option value="{{ $method }}" {{ request('method') == $method ? 'selected' : '' }}>
                                        {{ $method == 'GetStatus' ? 'Heartbeat' : 'Access Control' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
                            <select name="type" id="type" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="">All Types</option>
                                @foreach($types as $type)
                                    <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                                        {{ $type }} - {{ \App\Models\EGateRequest::make(['type' => $type])->type_label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="serial" class="block text-sm font-medium text-gray-700">Serial</label>
                            <input type="text" name="serial" id="serial" value="{{ request('serial') }}" 
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="response_status" class="block text-sm font-medium text-gray-700">Response Status</label>
                            <select name="response_status" id="response_status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="">All Statuses</option>
                                @foreach($responseStatuses as $status)
                                    <option value="{{ $status }}" {{ request('response_status') == $status ? 'selected' : '' }}>
                                        {{ $status }} - {{ \App\Models\EGateRequest::make(['response_status' => $status])->response_status_label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="date_from" class="block text-sm font-medium text-gray-700">Date From</label>
                            <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" 
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="date_to" class="block text-sm font-medium text-gray-700">Date To</label>
                            <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" 
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>

                        <div class="flex items-end space-x-3">
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                Apply Filters
                            </button>
                            <a href="{{ route('logs.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Logs Table -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-6">
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Access Control Requests</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">Detailed log of all eGate device communications</p>
                </div>
                
                @if($requests->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Serial</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Card/Data</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reader</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($requests as $request)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $request->created_at->format('Y-m-d H:i:s') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                {{ $request->method == 'GetStatus' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                                {{ $request->method_label }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if($request->type)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    {{ $request->type_label }}
                                                </span>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $request->serial ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if($request->card)
                                                <div class="max-w-xs truncate" title="{{ $request->card }}">
                                                    {{ $request->card }}
                                                </div>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if($request->reader !== null)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                    {{ $request->reader == '0' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }}">
                                                    {{ $request->reader_label }}
                                                </span>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($request->response_status !== null)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                    {{ $request->response_status == '1' ? 'bg-green-100 text-green-800' : 
                                                       ($request->response_status == '0' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">
                                                    {{ $request->response_status_label }}
                                                </span>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('logs.show', $request) }}" class="text-indigo-600 hover:text-indigo-900">View Details</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                        <div class="flex-1 flex justify-between sm:hidden">
                            @if($requests->previousPageUrl())
                                <a href="{{ $requests->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    Previous
                                </a>
                            @endif
                            @if($requests->nextPageUrl())
                                <a href="{{ $requests->nextPageUrl() }}" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    Next
                                </a>
                            @endif
                        </div>
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Showing
                                    <span class="font-medium">{{ $requests->firstItem() }}</span>
                                    to
                                    <span class="font-medium">{{ $requests->lastItem() }}</span>
                                    of
                                    <span class="font-medium">{{ $requests->total() }}</span>
                                    results
                                </p>
                            </div>
                            <div>
                                {{ $requests->links() }}
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No requests found</h3>
                        <p class="mt-1 text-sm text-gray-500">No eGate requests match your current filters.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Door Control Modal -->
    <div id="doorControlModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden" x-data="{ open: false }">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Door Control</h3>
                    <button onclick="closeDoorControlModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <form id="doorControlForm" class="space-y-4">
                    <div>
                        <label for="door" class="block text-sm font-medium text-gray-700">Door Selection</label>
                        <select name="door" id="door" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm rounded-md">
                            <option value="0">Entry Door (Lock A)</option>
                            <option value="1">Exit Door (Lock B)</option>
                            <option value="2">Both Doors</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="action" class="block text-sm font-medium text-gray-700">Action</label>
                        <select name="action" id="action" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm rounded-md">
                            <option value="open">Open Door</option>
                            <option value="close">Close Door</option>
                            <option value="alarm">Trigger Alarm</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="duration" class="block text-sm font-medium text-gray-700">Duration (seconds)</label>
                        <input type="number" name="duration" id="duration" value="5" min="1" max="30" 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                        <p class="mt-1 text-sm text-gray-500">How long to keep the door open/action active (1-30 seconds)</p>
                    </div>
                    
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="closeDoorControlModal()" 
                                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                            Execute Command
                        </button>
                    </div>
                </form>
                
                <!-- Status Display -->
                <div id="doorControlStatus" class="mt-4 hidden">
                    <div id="successStatus" class="bg-green-50 border border-green-200 rounded-md p-3 hidden">
                        <div class="flex">
                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-green-800">Command Queued Successfully</h3>
                                <div class="mt-2 text-sm text-green-700">
                                    <p id="statusMessage">The door control command has been queued and will be executed on the next heartbeat from the controller.</p>
                                    <p class="mt-1 text-xs">Command expires in <span id="expiryTime">60</span> seconds if not executed.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="errorStatus" class="bg-red-50 border border-red-200 rounded-md p-3 hidden">
                        <div class="flex">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Command Failed</h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <p id="errorMessage">Failed to queue door control command.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="executedStatus" class="bg-blue-50 border border-blue-200 rounded-md p-3 hidden">
                        <div class="flex">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293l-3-3a1 1 0 00-1.414 1.414L10.586 9.5H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd"></path>
                            </svg>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">Command Executed</h3>
                                <div class="mt-2 text-sm text-blue-700">
                                    <p>The door control command has been successfully executed by the controller.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openDoorControlModal() {
            document.getElementById('doorControlModal').classList.remove('hidden');
            hideAllStatusMessages();
        }
        
        function hideAllStatusMessages() {
            document.getElementById('doorControlStatus').classList.add('hidden');
            document.getElementById('successStatus').classList.add('hidden');
            document.getElementById('errorStatus').classList.add('hidden');
            document.getElementById('executedStatus').classList.add('hidden');
        }
        
        function showStatusMessage(type, message = null) {
            hideAllStatusMessages();
            document.getElementById('doorControlStatus').classList.remove('hidden');
            
            if (type === 'success') {
                document.getElementById('successStatus').classList.remove('hidden');
                if (message) document.getElementById('statusMessage').textContent = message;
            } else if (type === 'error') {
                document.getElementById('errorStatus').classList.remove('hidden');
                if (message) document.getElementById('errorMessage').textContent = message;
            } else if (type === 'executed') {
                document.getElementById('executedStatus').classList.remove('hidden');
            }
        }
        
        function closeDoorControlModal() {
            document.getElementById('doorControlModal').classList.add('hidden');
        }
        
        // Handle form submission
        document.getElementById('doorControlForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            
            // Show loading state
            submitButton.disabled = true;
            submitButton.textContent = 'Executing...';
            
            try {
                const response = await fetch('{{ route("door.open") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        door: formData.get('door'),
                        action: formData.get('action'),
                        duration: parseInt(formData.get('duration'))
                    })
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    // Show success message
                    showStatusMessage('success', data.message);
                    
                    // Start countdown
                    let timeLeft = data.expires_in || 60;
                    const countdownElement = document.getElementById('expiryTime');
                    const countdown = setInterval(() => {
                        timeLeft--;
                        countdownElement.textContent = timeLeft;
                        if (timeLeft <= 0) {
                            clearInterval(countdown);
                            countdownElement.textContent = 'expired';
                        }
                    }, 1000);
                    
                    // Start monitoring for command execution
                    monitorCommandExecution();
                    
                } else {
                    showStatusMessage('error', data.message || 'Failed to queue door control command');
                }
            } catch (error) {
                showStatusMessage('error', 'Failed to communicate with server');
                console.error('Door control error:', error);
            } finally {
                // Reset button
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }
        });
        
        // Monitor command execution status
        function monitorCommandExecution() {
            let attempts = 0;
            const maxAttempts = 60; // Monitor for 60 seconds
            
            const monitor = setInterval(async () => {
                attempts++;
                
                try {
                    const response = await fetch('{{ route("door.status") }}', {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                        }
                    });
                    
                    const data = await response.json();
                    
                    // If no pending command, it means it was executed
                    if (!data.has_pending_command) {
                        showStatusMessage('executed');
                        clearInterval(monitor);
                        return;
                    }
                    
                    // Stop monitoring after max attempts
                    if (attempts >= maxAttempts) {
                        clearInterval(monitor);
                    }
                    
                } catch (error) {
                    console.error('Error monitoring command execution:', error);
                    if (attempts >= maxAttempts) {
                        clearInterval(monitor);
                    }
                }
            }, 1000); // Check every second
        }
        
        // Close modal when clicking outside
        document.getElementById('doorControlModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDoorControlModal();
            }
        });
    </script>
</body>
</html>
