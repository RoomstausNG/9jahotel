 <div class="w-full space-y-6 text-white">
     <form action="" class="w-full px-2 space-y-6 text-white" method="post">
         @csrf
         <div class="space-y-1 text-left">
             <label for="code" class="inline-block text-left text-white">Hotel Code</label>
             <input type="text" name="code" value="{{ old('code') }}"
                 class="w-full px-4 py-2 text-sm text-gray-600 rounded shadow-sm focus:ring-0 focus:border-primary">
             @error('code')
                 <span class="text-sm font-medium text-red-600 bg-transparent">{{ $message }}</span>
             @enderror
         </div>
         <div class="space-y-1 text-left">
             <label for="username" class="inline-block text-left text-white">Username</label>
             <input type="text" name="username" value="{{ old('username') }}"
                 class="w-full px-4 py-2 text-sm text-gray-600 rounded shadow-sm focus:ring-0 focus:border-primary">
             @error('username')
                 <span class="text-sm font-medium text-red-600 bg-transparent">{{ $message }}</span>
             @enderror
         </div>
         <div class="space-y-1 text-left">
             <label for="password" class="inline-block text-left text-white">Password</label>
             <input type="password" name="password"
                 class="w-full px-4 py-2 text-sm text-gray-600 rounded shadow-sm focus:ring-0 focus:border-primary">
             @error('password')
                 <span class="text-sm font-medium text-red-600 bg-transparent">{{ $message }}</span>
             @enderror
         </div>
         <div class="space-y-1 text-left">
             <button
                 class="inline-block w-full py-2 text-center text-white rounded-md bg-primary hover:bg-primary-700 tt">Sign-in</button>
         </div>
     </form>
 </div>
