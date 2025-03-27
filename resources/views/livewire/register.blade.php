<div class="w-full space-y-6 max-w-xl mx-auto">
    <div class="py-20 md:py-28 lg:py-32 space-y-8">
        <div class="text-center">
            <h1 class="text-2xl font-medium">Create an Account</h1>
            <p>Sign up for your 9jahotel account to get started</p>
        </div>
        <div class="">
            <a href="" class="bg-primary-50 border-primary py-3 gap-2 font-medium border w-full text-center block">
                <img src="{{ asset('img/DeviconGoogle.svg') }}" alt="" class="w-6 h-6 inline-block">
                <span class="">Sign up with Google</span>
            </a>
        </div>
        <form wire:submit="register" class="space-y-6">
            {{ $this->form }}

            <div class="flex items-center justify-center text-sm">
                <span>Already have an account?</span>
                <a href="{{ route('login') }}" class="ml-1 font-semibold text-primary-600 hover:text-primary-500">
                    Sign in
                </a>
            </div>

            <div>
                <button type="submit"
                    class="flex w-full justify-center bg-primary-600 px-3 py-2.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">
                    Create Account
                </button>
            </div>
        </form>
    </div>
</div>
