<div class="w-full space-y-6 max-w-xl mx-auto">
    <div class="py-20 md:py-28 lg:py-32 space-y-8">
        <div class="text-center">
            <h1 class="text-2xl font-medium">Forgot Password</h1>
            <p>Enter your email to receive a password reset link</p>
        </div>
        <form wire:submit="sendResetLink" class="space-y-6">
            {{ $this->form }}
            <div>
                <button type="submit"
                    class="flex w-full justify-center bg-primary-600 px-3 py-2.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">
                    Send Reset Link
                </button>
            </div>
            <div class="text-center text-sm mt-4">
                Remembered your password?
                <a href="{{ route('login') }}" class="text-primary-600 hover:underline">Sign in</a>
            </div>
        </form>
    </div>
</div>
