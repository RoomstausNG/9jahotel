<x-layouts.frontend>
    <div class="py-8 lg:py-48">
        <div
            class="w-5/6 max-w-[500px] text-center space-y-8 flex flex-col items-center mx-auto rounded-lg bg-white p-8 shadow-lg">
            <span class="flex items-center justify-center h-[100px] aspect-square text-white rounded-full bg-green-500">
                <i class="fas fa-check-double fa-3x"></i>
            </span>

            <div class="space-y-4">
                <h1 class="text-2xl font-bold text-gray-800">Payment Successful!</h1>

                @if (session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                <p class="text-gray-600">
                    Your booking has been confirmed and payment processed successfully.
                    You will receive a confirmation email shortly with your booking details.
                </p>

                <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded">
                    <p class="text-sm">
                        <strong>Note:</strong> Please check your email for booking confirmation and receipt.
                        If you don't receive it within 10 minutes, please check your spam folder.
                    </p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4">
                <a href="/"
                    class="px-6 py-3 border-2 rounded text-primary border-primary hover:bg-primary hover:text-white transition-colors duration-200">
                    Go to Home
                </a>

                @auth('customers')
                    <a href="/home"
                        class="px-6 py-3 bg-primary text-white rounded hover:bg-primary-700 transition-colors duration-200">
                        View My Bookings
                    </a>
                @endauth
            </div>
        </div>
    </div>
</x-layouts.frontend>
