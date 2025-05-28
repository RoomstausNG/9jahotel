<x-layouts.frontend>
    <div class="py-8 lg:py-48">
        <div
            class="w-5/6 max-w-[500px] text-center space-y-8 flex flex-col items-center mx-auto rounded-lg bg-white p-8 shadow-lg">
            <span class="flex items-center justify-center h-[100px] aspect-square text-white rounded-full bg-red-500">
                <i class="fas fa-times fa-3x"></i>
            </span>

            <div class="space-y-4">
                <h1 class="text-2xl font-bold text-gray-800">Payment Failed</h1>

                @if (session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        {{ session('error') }}
                    </div>
                @endif

                <p class="text-gray-600">
                    Unfortunately, your payment could not be processed. Your booking has not been confirmed.
                </p>

                <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded">
                    <p class="text-sm">
                        <strong>What to do next:</strong>
                        <br>• Check your payment details and try again
                        <br>• Ensure you have sufficient funds
                        <br>• Contact your bank if the problem persists
                        <br>• Contact our support team for assistance
                    </p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4">
                <a href="/"
                    class="px-6 py-3 border-2 rounded text-primary border-primary hover:bg-primary hover:text-white transition-colors duration-200">
                    Go to Home
                </a>

                <button onclick="history.back()"
                    class="px-6 py-3 bg-primary text-white rounded hover:bg-primary-700 transition-colors duration-200">
                    Try Again
                </button>

                <a href="{{ route('support') }}"
                    class="px-6 py-3 bg-gray-500 text-white rounded hover:bg-gray-600 transition-colors duration-200">
                    Contact Support
                </a>
            </div>
        </div>
    </div>
</x-layouts.frontend>
