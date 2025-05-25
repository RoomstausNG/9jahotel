<div class="container mx-auto p-4 max-w-2xl bg-white shadow-lg rounded-lg my-10">
    <h2 class="text-2xl font-semibold text-gray-700 mb-6 text-center">Complete Your Payment</h2>

    @if (session()->has('payment_success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
        <p class="font-bold">Success</p>
        <p>{{ session('payment_success') }}</p>
        @if ($paymentResponse)
        <div class="mt-2 text-sm">
            <pre class="bg-gray-100 p-2 rounded">{{ json_encode($paymentResponse, JSON_PRETTY_PRINT) }}</pre>
        </div>
        @endif
    </div>
    @endif

    @if ($errorMessage)
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
        <p class="font-bold">Error</p>
        <p>{{ $errorMessage }}</p>
    </div>
    @endif

    <form wire:submit.prevent="processPayment">
        @csrf

        <!-- Amount and Customer Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label for="amount" class="block text-sm font-medium text-gray-700">Amount ({{ $currency }})</label>
                <input type="number" wire:model.lazy="amount" id="amount"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm p-2.5"
                    placeholder="e.g., 5000">
                @error('amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label for="customerEmail" class="block text-sm font-medium text-gray-700">Email Address</label>
                <input type="email" wire:model.lazy="customerEmail" id="customerEmail"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm p-2.5"
                    placeholder="you@example.com">
                @error('customerEmail') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label for="customerPhone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                <input type="tel" wire:model.lazy="customerPhone" id="customerPhone"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm p-2.5"
                    placeholder="+2348012345678">
                @error('customerPhone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label for="reason" class="block text-sm font-medium text-gray-700">Payment Reason</label>
                <input type="text" wire:model.lazy="reason" id="reason"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm p-2.5"
                    placeholder="Payment for Order #123">
                @error('reason') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Payment Method Selection -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Select Payment Method</label>
            <div class="flex flex-wrap gap-4">
                <label class="flex items-center space-x-2 p-3 border rounded-lg cursor-pointer hover:bg-gray-50"
                    :class="{ 'bg-primary-50 border-primary-500': paymentMethod === 'BankCard' }">
                    <input type="radio" wire:model="paymentMethod" value="BankCard" name="paymentMethod"
                        class="form-radio h-4 w-4 text-primary-600 focus:ring-primary-500">
                    <span>Pay with Card</span>
                </label>
                <label class="flex items-center space-x-2 p-3 border rounded-lg cursor-pointer hover:bg-gray-50"
                    :class="{ 'bg-primary-50 border-primary-500': paymentMethod === 'BankTransfer' }">
                    <input type="radio" wire:model="paymentMethod" value="BankTransfer" name="paymentMethod"
                        class="form-radio h-4 w-4 text-primary-600 focus:ring-primary-500">
                    <span>Bank Transfer</span>
                </label>
                <label class="flex items-center space-x-2 p-3 border rounded-lg cursor-pointer hover:bg-gray-50"
                    :class="{ 'bg-primary-50 border-primary-500': paymentMethod === 'BankUssd' }">
                    <input type="radio" wire:model="paymentMethod" value="BankUssd" name="paymentMethod"
                        class="form-radio h-4 w-4 text-primary-600 focus:ring-primary-500">
                    <span>USSD</span>
                </label>
                <label class="flex items-center space-x-2 p-3 border rounded-lg cursor-pointer hover:bg-gray-50"
                    :class="{ 'bg-primary-50 border-primary-500': paymentMethod === 'token' }">
                    <input type="radio" wire:model="paymentMethod" value="token" name="paymentMethod"
                        class="form-radio h-4 w-4 text-primary-600 focus:ring-primary-500">
                    <span>Pay with Token</span>
                </label>
            </div>
            @error('paymentMethod') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <!-- Card Details Section -->
        @if ($paymentMethod === 'card')
        <div wire:key="card-details" class="mb-6 p-4 border border-gray-200 rounded-lg bg-gray-50">
            <h3 class="text-lg font-medium text-gray-800 mb-4">Enter Card Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="firstName" class="block text-sm font-medium text-gray-700">First Name</label>
                    <input type="text" wire:model.lazy="firstName" id="firstName"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm p-2.5"
                        placeholder="John">
                    @error('firstName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="lastName" class="block text-sm font-medium text-gray-700">Last Name</label>
                    <input type="text" wire:model.lazy="lastName" id="lastName"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm p-2.5"
                        placeholder="Doe">
                    @error('lastName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="md:col-span-2">
                    <label for="cardNumber" class="block text-sm font-medium text-gray-700">Card Number</label>
                    <input type="text" wire:model.lazy="cardNumber" id="cardNumber"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm p-2.5"
                        placeholder="0000 0000 0000 0000">
                    @error('cardNumber') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="cardDateMonth" class="block text-sm font-medium text-gray-700">Expiry Month (MM)</label>
                    <input type="text" wire:model.lazy="cardDateMonth" id="cardDateMonth"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm p-2.5"
                        placeholder="MM">
                    @error('cardDateMonth') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="cardDateYear" class="block text-sm font-medium text-gray-700">Expiry Year (YYYY)</label>
                    <input type="text" wire:model.lazy="cardDateYear" id="cardDateYear"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm p-2.5"
                        placeholder="YYYY">
                    @error('cardDateYear') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="cardCVC" class="block text-sm font-medium text-gray-700">CVC</label>
                    <input type="text" wire:model.lazy="cardCVC" id="cardCVC"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm p-2.5"
                        placeholder="123">
                    @error('cardCVC') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        @endif

        <!-- Bank Transfer Details Section -->
        @if ($paymentMethod === 'bank_transfer')
        <div wire:key="bank-details" class="mb-6 p-4 border border-gray-200 rounded-lg bg-gray-50">
            <h3 class="text-lg font-medium text-gray-800 mb-4">Enter Bank Account Details</h3>
            <p class="text-sm text-gray-600 mb-4">Please ensure these details are correct. You might be redirected to
                complete the payment.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="bankAccountNumber" class="block text-sm font-medium text-gray-700">Bank Account
                        Number</label>
                    <input type="text" wire:model.lazy="bankAccountNumber" id="bankAccountNumber"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm p-2.5"
                        placeholder="0123456789">
                    @error('bankAccountNumber') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="bankCode" class="block text-sm font-medium text-gray-700">Bank Code</label>
                    <input type="text" wire:model.lazy="bankCode" id="bankCode"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm p-2.5"
                        placeholder="e.g., 058">
                    @error('bankCode') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="bvn" class="block text-sm font-medium text-gray-700">BVN (Bank Verification
                        Number)</label>
                    <input type="text" wire:model.lazy="bvn" id="bvn"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm p-2.5"
                        placeholder="Enter your BVN">
                    @error('bvn') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="md:col-span-2 grid grid-cols-3 gap-4">
                    <div>
                        <label for="dobDay" class="block text-sm font-medium text-gray-700">Date of Birth (Day)</label>
                        <input type="text" wire:model.lazy="dobDay" id="dobDay"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm p-2.5"
                            placeholder="DD">
                        @error('dobDay') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="dobMonth" class="block text-sm font-medium text-gray-700">Month</label>
                        <input type="text" wire:model.lazy="dobMonth" id="dobMonth"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm p-2.5"
                            placeholder="MM">
                        @error('dobMonth') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="dobYear" class="block text-sm font-medium text-gray-700">Year</label>
                        <input type="text" wire:model.lazy="dobYear" id="dobYear"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm p-2.5"
                            placeholder="YYYY">
                        @error('dobYear') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- USSD Details Section -->
        @if ($paymentMethod === 'ussd')
        <div wire:key="ussd-details" class="mb-6 p-4 border border-gray-200 rounded-lg bg-gray-50">
            <h3 class="text-lg font-medium text-gray-800 mb-4">USSD Payment</h3>
            <p class="text-sm text-gray-600">
                Follow the instructions after submitting. A USSD code might be generated for you to dial.
                (This section is a placeholder - actual implementation depends on OPay's USSD flow.)
            </p>
            <!-- Add any USSD specific fields if required by the package -->
        </div>
        @endif

        <!-- Token Payment Section -->
        @if ($paymentMethod === 'token')
        <div wire:key="token-details" class="mb-6 p-4 border border-gray-200 rounded-lg bg-gray-50">
            <h3 class="text-lg font-medium text-gray-800 mb-4">Pay with Token</h3>
            <div>
                <label for="token" class="block text-sm font-medium text-gray-700">Payment Token</label>
                <input type="text" wire:model.lazy="token" id="token"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm p-2.5"
                    placeholder="Enter your payment token">
                @error('token') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>
        @endif

        <!-- Submit Button -->
        <div class="mt-8">
            <button type="submit" wire:loading.attr="disabled" wire:target="processPayment"
                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50">
                <span wire:loading.remove wire:target="processPayment">Pay Now</span>
                <span wire:loading wire:target="processPayment">
                    Processing...
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </span>
            </button>
        </div>
    </form>
</div>
