<label for="{{ $name }}"
    class="block capitalize mb-2 text-sm font-medium {{ !request()->is('register') ? 'text-gray-900' : 'text-white' }} dark:text-white">{{ $label }}</label>
