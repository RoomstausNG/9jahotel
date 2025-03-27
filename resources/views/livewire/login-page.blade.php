 <div class="w-full space-y-6 max-w-xl mx-auto">
     <div class="py-12 space-y-8">
         <div class="text-center">
             <h1 class="text-2xl font-medium">Welcome Back!</h1>
             <p>Log in your 9jahotel account to continue</p>
         </div>
         <form wire:submit="login" class="space-y-6">
             {{ $this->form }}

             <div class="flex items-center justify-end">
                 <div class="text-sm text-right">
                     <a href="{{ route('password.request') }}" class="font-semibold text-green-600 hover:text-green-500">
                         Forgot password?
                     </a>
                 </div>
             </div>

             <div>
                 <button type="submit"
                     class="flex w-full justify-center rounded-md bg-green-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-green-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600">
                     Sign in
                 </button>
             </div>
         </form>
     </div>
 </div>
