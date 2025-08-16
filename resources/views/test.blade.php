<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test eGate Request - {{ ucfirst($type) }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-4">Test eGate {{ ucfirst($type) }} Request</h1>
                <p class="text-lg text-gray-600">Click the button below to simulate an eGate {{ $type }} request</p>
            </div>

            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Request Details</h2>
                <div class="bg-gray-50 p-4 rounded-md mb-4">
                    <code class="text-sm text-gray-800 break-all">{{ $url }}</code>
                </div>
                
                <button onclick="sendRequest()" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Send {{ ucfirst($type) }} Request
                </button>
            </div>

            <div id="response" class="bg-white shadow rounded-lg p-6 hidden">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Response</h2>
                <div id="responseContent" class="bg-gray-50 p-4 rounded-md">
                    <!-- Response will be displayed here -->
                </div>
            </div>

            <div class="text-center mt-8">
                <a href="{{ route('logs.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    View Logs
                </a>
                <a href="/" class="ml-3 inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Back to Home
                </a>
            </div>
        </div>
    </div>

    <script>
        async function sendRequest() {
            const button = event.target;
            const originalText = button.textContent;
            
            // Disable button and show loading
            button.disabled = true;
            button.textContent = 'Sending...';
            
            try {
                const response = await fetch('{{ $url }}', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    }
                });
                
                const data = await response.json();
                
                // Show response
                document.getElementById('response').classList.remove('hidden');
                document.getElementById('responseContent').innerHTML = `
                    <div class="mb-4">
                        <strong>Status:</strong> ${response.status} ${response.statusText}
                    </div>
                    <pre class="text-sm text-gray-800 overflow-x-auto">${JSON.stringify(data, null, 2)}</pre>
                `;
                
                // Show success message
                button.textContent = 'Request Sent Successfully!';
                button.classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
                button.classList.add('bg-green-600', 'hover:bg-green-700');
                
            } catch (error) {
                // Show error
                document.getElementById('response').classList.remove('hidden');
                document.getElementById('responseContent').innerHTML = `
                    <div class="text-red-600">
                        <strong>Error:</strong> ${error.message}
                    </div>
                `;
                
                button.textContent = 'Error - Try Again';
                button.classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
                button.classList.add('bg-red-600', 'hover:bg-red-700');
            }
            
            // Re-enable button after 3 seconds
            setTimeout(() => {
                button.disabled = false;
                button.textContent = originalText;
                button.classList.remove('bg-green-600', 'hover:bg-green-700', 'bg-red-600', 'hover:bg-red-700');
                button.classList.add('bg-indigo-600', 'hover:bg-indigo-700');
            }, 3000);
        }
    </script>
</body>
</html>
