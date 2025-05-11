<div class="w-full space-y-6 max-w-xl mx-auto">
    <div class="py-20 md:py-28 lg:py-32 space-y-8 px-8 lg:px-0">
        <div class="text-center">
            <h1 class="text-2xl font-medium">Welcome Back!</h1>
            <p>Log in your 9jahotel account to continue</p>
        </div>
        <div class="px-8">
            <a href="" class="bg-primary-50 border-primary py-3 gap-2 font-medium border w-full text-center block">
                <img src="{{ asset('img/DeviconGoogle.svg') }}" alt="" class="w-6 h-6 inline-block">
                <span class="">Proceed with Google</span>
            </a>
        </div>
        <form wire:submit="login" class="space-y-6 px-8">
            {{ $this->form }}

            <div class="flex items-center justify-between">
                <div class="text-sm text-right">
                    <a href="{{ route('password.request') }}"
                        class="font-semibold text-primary-600 hover:text-primary-500">
                        Forgot password?
                    </a>
                </div>
                <div class="text-sm text-right">
                    <a href="{{ route('signup') }}" class="font-semibold text-primary-600 hover:text-primary-500">
                        Register
                    </a>
                </div>
            </div>

            <div>
                <button type="submit"
                    class="flex w-full justify-center bg-primary-600 px-3 py-2.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">
                    Sign in
                </button>
            </div>
        </form>
    </div>
</div>
