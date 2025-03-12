<div class="w-full px-4 py-8 space-y-6 text-white bg-black rounded-lg bg-opacity-80 lg:py-12 lg:px-8 lg:space-y-8">
    @session('success')
        <div class="px-4 py-2 text-green-600 bg-red-100 rounded-md">
            {{ $value }}
        </div>
    @endsession
    @session('error')
        <div class="px-4 py-2 text-white bg-green-500 rounded-md">
            {{ $value }}
        </div>
    @endsession
    <div class="space-y-1">
        <h1 class="text-2xl font-medium">OTP Verification</h1>
        <p class="text-sm">Kindly check your email for the otp. You can check your spam folder if not found in inbox</p>
    </div>
    <form wire:submit='verifyOtp' class="w-full px-2 space-y-3 text-white">
        @csrf
        <div class="space-y-1 text-left">
            <div class="mb-6 space-y-1 text-left">
                <input type="text" wire:model="email" readonly
                    class="w-full px-4 py-3 font-medium text-gray-600 rounded shadow-sm cursor-not-allowed focus:ring-0 focus:border-primary">
            </div>
            <div class="relative z-10 w-full overflow-hidden">
                <input type="text" wire:model="otp"
                    class="w-full px-4 py-3 font-mono font-bold leading-loose text-gray-600 rounded shadow-sm focus:ring-0 focus:border-primary">
                <button
                    class="absolute top-0 right-0 z-30 inline-block h-full px-4 py-3 text-center text-white bg-blue-600 rounded-r-md rounded-br-md hover:bg-blue-700 tt ">Confirm</button>
            </div>
            @error('otp')
                <span class="text-sm font-medium text-red-600 bg-transparent">{{ $message }}</span>
            @enderror
            <p class="flex justify-between">
                <span class="">Wait for 5minutes to resend the OTP again</span>
                <span wire:click="resendOtp" class="font-medium text-green-500 cursor-pointer">Resend</span>
            </p>
        </div>

        <div class="space-y-1 text-left">

        </div>
    </form>
</div>
