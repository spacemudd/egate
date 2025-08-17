<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eGate Request Details - Access Control System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">eGate Request Details</h1>
                        <p class="mt-2 text-sm text-gray-600">Detailed information about request #{{ $egateRequest->id }}</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('logs.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Back to Logs
                        </a>
                        <a href="/" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Home
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <!-- Request Summary -->
            <div class="bg-white shadow rounded-lg mb-6">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Request Summary</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Request ID</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $egateRequest->id }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Method</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $egateRequest->method == 'GetStatus' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                    {{ $egateRequest->method_label }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created At</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $egateRequest->created_at->format('Y-m-d H:i:s') }}</dd>
                        </div>
                        @if($egateRequest->type)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Data Type</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ $egateRequest->type_label }}
                                </span>
                            </dd>
                        </div>
                        @endif
                        @if($egateRequest->serial)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Device Serial</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $egateRequest->serial }}</dd>
                        </div>
                        @endif
                        @if($egateRequest->device_id)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Device ID</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $egateRequest->device_id }}</dd>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Request Details -->
            <div class="bg-white shadow rounded-lg mb-6">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Request Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @if($egateRequest->mac_address)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">MAC Address</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $egateRequest->mac_address }}</dd>
                        </div>
                        @endif
                        @if($egateRequest->ip_address)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">IP Address</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $egateRequest->ip_address }}</dd>
                        </div>
                        @endif
                        @if($egateRequest->reader !== null)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Reader Direction</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $egateRequest->reader == '0' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }}">
                                    {{ $egateRequest->reader_label }}
                                </span>
                            </dd>
                        </div>
                        @endif
                        @if($egateRequest->source)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Data Source</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                @switch($egateRequest->source)
                                    @case('0')
                                        WG Reader
                                        @break
                                    @case('1')
                                        RS232 Interface
                                        @break
                                    @case('2')
                                        485 Interface
                                        @break
                                    @case('5')
                                        USB Interface
                                        @break
                                    @case('6')
                                        232 Converter
                                        @break
                                    @case('7')
                                        Controller Button
                                        @break
                                    @case('9')
                                        Network
                                        @break
                                    @default
                                        {{ $egateRequest->source }}
                                @endswitch
                            </dd>
                        </div>
                        @endif
                        @if($egateRequest->status)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Gate Status</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $egateRequest->status }}</dd>
                        </div>
                        @endif
                        @if($egateRequest->input)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Input Status</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $egateRequest->input }}</dd>
                        </div>
                        @endif
                        @if($egateRequest->card)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Card/Data</dt>
                            <dd class="mt-1 text-sm text-gray-900 break-all">{{ $egateRequest->card }}</dd>
                        </div>
                        @endif
                        @if($egateRequest->key)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Key</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $egateRequest->key }}</dd>
                        </div>
                        @endif
                        @if($egateRequest->now)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Device Time</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $egateRequest->now }}</dd>
                        </div>
                        @endif
                        @if($egateRequest->crc)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">CRC</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $egateRequest->crc }}</dd>
                        </div>
                        @endif
                        @if($egateRequest->index)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Index</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $egateRequest->index }}</dd>
                        </div>
                        @endif
                        @if($egateRequest->ver)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Firmware Version</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $egateRequest->ver }}</dd>
                        </div>
                        @endif
                    </div>

                    <!-- Temperature and Humidity -->
                    @if($egateRequest->t1 || $egateRequest->h1 || $egateRequest->t2 || $egateRequest->h2)
                    <div class="mt-6">
                        <h4 class="text-md font-medium text-gray-900 mb-3">Environmental Data</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @if($egateRequest->t1 || $egateRequest->h1)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Sensor 1</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @if($egateRequest->t1) Temp: {{ $egateRequest->t1 }}°C @endif
                                    @if($egateRequest->h1) Humidity: {{ $egateRequest->h1 }}% @endif
                                </dd>
                            </div>
                            @endif
                            @if($egateRequest->t2 || $egateRequest->h2)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Sensor 2</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @if($egateRequest->t2) Temp: {{ $egateRequest->t2 }}°C @endif
                                    @if($egateRequest->h2) Humidity: {{ $egateRequest->h2 }}% @endif
                                </dd>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Additional Fields -->
                    @if($egateRequest->will_pass || $egateRequest->passed || $egateRequest->next_num || $egateRequest->order_code)
                    <div class="mt-6">
                        <h4 class="text-md font-medium text-gray-900 mb-3">Additional Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @if($egateRequest->will_pass)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Will Pass</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $egateRequest->will_pass }}</dd>
                            </div>
                            @endif
                            @if($egateRequest->passed)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Already Passed</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $egateRequest->passed }}</dd>
                            </div>
                            @endif
                            @if($egateRequest->next_num)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Next Number</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $egateRequest->next_num }}</dd>
                            </div>
                            @endif
                            @if($egateRequest->order_code)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Order Code</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $egateRequest->order_code }}</dd>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if($egateRequest->modbus)
                    <div class="mt-6">
                        <h4 class="text-md font-medium text-gray-900 mb-3">Modbus Data</h4>
                        <div class="bg-gray-50 p-3 rounded-md">
                            <code class="text-sm text-gray-800 break-all">{{ $egateRequest->modbus }}</code>
                        </div>
                    </div>
                    @endif

                    @if($egateRequest->data)
                    <div class="mt-6">
                        <h4 class="text-md font-medium text-gray-900 mb-3">Additional Data</h4>
                        <div class="bg-gray-50 p-3 rounded-md">
                            <code class="text-sm text-gray-800 break-all">{{ $egateRequest->data }}</code>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Response Details -->
            @if($egateRequest->response_status !== null || $egateRequest->response_data)
            <div class="bg-white shadow rounded-lg mb-6">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Response Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @if($egateRequest->response_status !== null)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Response Status</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $egateRequest->response_status == '1' ? 'bg-green-100 text-green-800' : 
                                       ($egateRequest->response_status == '0' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">
                                    {{ $egateRequest->response_status_label }}
                                </span>
                            </dd>
                        </div>
                        @endif
                        @if($egateRequest->updated_at)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Response Time</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $egateRequest->updated_at->format('Y-m-d H:i:s') }}</dd>
                        </div>
                        @endif
                    </div>

                    @if($egateRequest->response_data)
                    <div class="mt-6">
                        <h4 class="text-md font-medium text-gray-900 mb-3">Response Data</h4>
                        <div class="bg-gray-50 p-3 rounded-md">
                            <pre class="text-sm text-gray-800 overflow-x-auto">{{ json_encode($egateRequest->response_data, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- API Validation Results -->
            @php
                $apiValidationData = null;
                if ($egateRequest->response_data && is_array($egateRequest->response_data)) {
                    // Check if this was a QR code request with API validation
                    if (isset($egateRequest->response_data['access_result'])) {
                        $apiValidationData = $egateRequest->response_data;
                    }
                }
                
                // Also check if there's API response data in audit context
                $apiResponseData = null;
                if ($egateRequest->response_data && 
                    isset($egateRequest->response_data['audit_context']) && 
                    is_array($egateRequest->response_data['audit_context'])) {
                    
                    // Look for API response in logs
                    $auditData = $egateRequest->response_data['audit_context'];
                    if (isset($auditData['api_response'])) {
                        $apiResponseData = $auditData['api_response'];
                    }
                }
            @endphp

            @if($apiValidationData || ($egateRequest->type == '1' || $egateRequest->type == '9') && $egateRequest->card)
            <div class="bg-white shadow rounded-lg mb-6">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                        <span class="inline-flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            API Validation Results
                        </span>
                    </h3>
                    
                    <div class="grid grid-cols-1 gap-6">
                        <!-- QR Code Information -->
                        @if($egateRequest->card)
                        <div class="border-l-4 border-blue-500 bg-blue-50 p-4">
                            <h4 class="text-sm font-medium text-blue-900 mb-3">QR Code Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-xs font-medium text-blue-700">Original (Base64)</dt>
                                    <dd class="mt-1 text-sm text-blue-900 font-mono break-all">{{ $egateRequest->card }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-blue-700">Decoded UUID</dt>
                                    <dd class="mt-1 text-sm text-blue-900 font-mono">
                                        @php
                                            // Use stored decoded UUID if available, otherwise decode it
                                            $decodedUuid = null;
                                            if ($apiValidationData && isset($apiValidationData['access_result']['decoded_uuid'])) {
                                                $decodedUuid = $apiValidationData['access_result']['decoded_uuid'];
                                            } else {
                                                $decodedUuid = base64_decode($egateRequest->card, true);
                                                if ($decodedUuid === false) {
                                                    $decodedUuid = $egateRequest->card;
                                                }
                                            }
                                        @endphp
                                        {{ $decodedUuid }}
                                    </dd>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- API Call Information -->
                        @if($egateRequest->card)
                        <div class="border-l-4 border-purple-500 bg-purple-50 p-4">
                            <h4 class="text-sm font-medium text-purple-900 mb-3">API Call Details</h4>
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <dt class="text-xs font-medium text-purple-700">API Endpoint</dt>
                                    <dd class="mt-1 text-sm text-purple-900 font-mono">https://eco-app.eco-propertiesglobal.co.uk/api/gate</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-purple-700">Parameters</dt>
                                    <dd class="mt-1 text-sm text-purple-900">
                                        <code class="bg-purple-100 px-2 py-1 rounded text-xs">
                                            secret=xkjalskdjalsd&qr_code_value={{ $decodedUuid ?? (base64_decode($egateRequest->card, true) ?: $egateRequest->card) }}
                                        </code>
                                    </dd>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- API Response -->
                        @if($apiValidationData && isset($apiValidationData['access_result']))
                        @php
                            $accessResult = $apiValidationData['access_result'];
                            $isGranted = isset($accessResult['granted']) && $accessResult['granted'];
                        @endphp
                        <div class="border-l-4 {{ $isGranted ? 'border-green-500 bg-green-50' : 'border-red-500 bg-red-50' }} p-4">
                            <h4 class="text-sm font-medium {{ $isGranted ? 'text-green-900' : 'text-red-900' }} mb-3">
                                API Validation Result
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <dt class="text-xs font-medium {{ $isGranted ? 'text-green-700' : 'text-red-700' }}">Access Decision</dt>
                                    <dd class="mt-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            {{ $isGranted ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $isGranted ? 'GRANTED' : 'DENIED' }}
                                        </span>
                                    </dd>
                                </div>
                                @if(isset($accessResult['name']))
                                <div>
                                    <dt class="text-xs font-medium {{ $isGranted ? 'text-green-700' : 'text-red-700' }}">User Name</dt>
                                    <dd class="mt-1 text-sm {{ $isGranted ? 'text-green-900' : 'text-red-900' }}">{{ $accessResult['name'] }}</dd>
                                </div>
                                @endif
                                @if(isset($accessResult['reason']))
                                <div>
                                    <dt class="text-xs font-medium {{ $isGranted ? 'text-green-700' : 'text-red-700' }}">Reason</dt>
                                    <dd class="mt-1 text-sm {{ $isGranted ? 'text-green-900' : 'text-red-900' }}">{{ $accessResult['reason'] }}</dd>
                                </div>
                                @endif
                            </div>
                            
                            @if(isset($accessResult['voice']))
                            <div class="mt-4">
                                <dt class="text-xs font-medium {{ $isGranted ? 'text-green-700' : 'text-red-700' }}">Voice Message</dt>
                                <dd class="mt-1 text-sm {{ $isGranted ? 'text-green-900' : 'text-red-900' }} italic">"{{ $accessResult['voice'] }}"</dd>
                            </div>
                            @endif
                        </div>
                        @endif

                        <!-- Raw API Response (if available) -->
                        @if(($apiValidationData && isset($apiValidationData['access_result']['api_response'])) || $apiResponseData)
                        <div class="border-l-4 border-gray-500 bg-gray-50 p-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Raw API Response</h4>
                            <div class="bg-white p-3 rounded-md border">
                                <pre class="text-xs text-gray-800 overflow-x-auto">{{ json_encode($apiValidationData['access_result']['api_response'] ?? $apiResponseData, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </div>
                        @endif

                        <!-- API Failure Details (if API failed) -->
                        @if($apiValidationData && isset($apiValidationData['access_result']['api_failure']))
                        @php
                            $apiFailure = $apiValidationData['access_result']['api_failure'];
                        @endphp
                        <div class="border-l-4 border-red-500 bg-red-50 p-4">
                            <h4 class="text-sm font-medium text-red-900 mb-3">
                                <span class="inline-flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    API Call Failed
                                </span>
                            </h4>
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <dt class="text-xs font-medium text-red-700">Error Message</dt>
                                    <dd class="mt-1 text-sm text-red-900">{{ $apiFailure['error'] }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-red-700">Attempted URL</dt>
                                    <dd class="mt-1 text-sm text-red-900 font-mono">{{ $apiFailure['attempted_url'] }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-red-700">Attempted Parameters</dt>
                                    <dd class="mt-1 text-sm text-red-900">
                                        <code class="bg-red-100 px-2 py-1 rounded text-xs">
                                            @foreach($apiFailure['attempted_params'] as $key => $value)
                                                {{ $key }}={{ $value }}{{ !$loop->last ? '&' : '' }}
                                            @endforeach
                                        </code>
                                    </dd>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Fallback Information -->
                        @if($apiValidationData && isset($apiValidationData['access_result']['reason']) && str_contains($apiValidationData['access_result']['reason'], 'fallback'))
                        <div class="border-l-4 border-yellow-500 bg-yellow-50 p-4">
                            <div class="flex">
                                <svg class="w-5 h-5 text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                <div>
                                    <h4 class="text-sm font-medium text-yellow-900">API Fallback Used</h4>
                                    <p class="text-sm text-yellow-700 mt-1">The external API was unavailable, so the system used local pattern matching as a fallback.</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Raw Request Data -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Raw Request Data</h3>
                    <div class="bg-gray-50 p-3 rounded-md">
                        <pre class="text-sm text-gray-800 overflow-x-auto">{{ json_encode($egateRequest->request_data, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
