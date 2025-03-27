<div class="w-full space-y-6 max-w-xl mx-auto">
    <div class="py-12 md:py-16 lg:py-20 space-y-8">
        <div class="text-center">
            <h1 class="text-2xl font-medium">Become a 9jahotel customer</h1>
            <p>Let’s get you onboard. It’s easy</p>
        </div>
        <div class="">
            <a href="" class="bg-primary-50 border-primary py-3 gap-2 font-medium border w-full text-center block">
                <img src="{{ asset('img/DeviconGoogle.svg') }}" alt="" class="w-6 h-6 inline-block">
                <span class="">Sign up with Google</span>
            </a>
        </div>
        <form wire:submit="register" class="space-y-6">
            {{ $this->form }}

            <div class="text-xs text-gray-600 text-center">
                By clicking on Sign Up, I agree to 9jahotel
                <a href="{{ route('terms') }}" class="text-primary-600 hover:underline">terms of services</a>,
                and acknowledge the
                <a href="{{ route('privacy') }}" class="text-primary-600 hover:underline">Privacy Policy</a>
            </div>

            <div>
                <button type="submit"
                    class="flex w-full justify-center bg-primary-600 px-3 py-2.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">
                    Create Account
                </button>
            </div>
            <div class="flex items-center justify-center text-sm">
                <span>Already have an account?</span>
                <a href="{{ route('login') }}" class="ml-1 font-semibold text-primary-600 hover:text-primary-500">
                    Sign in
                </a>
            </div>
        </form>
    </div>
</div>
