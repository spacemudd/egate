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
