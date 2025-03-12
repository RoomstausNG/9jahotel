<div class="p-4 space-y-4 overflow-hidden bg-white rounded shadow 2xl:px-6 lg:space-y-6">
    <div class="flex items-center justify-between">
        @if ($route)
            <a href="{{ route($route) }}" class="text-lg font-medium hover:underline">{{ $name }}</a>
        @else
            <span class="text-lg font-medium">{{ $name }}</span>
        @endif
        <span class="px-3 py-2 rounded aspect-square text-primary bg-primary-50">
            {{ $slot }}
        </span>
    </div>
    <h1 data-tooltip-target="{{ $name }}" data-tooltip-placement="right"
        class="font-mono text-3xl font-black max-w-max">
        {!! $currency ? '&#8358;' . formatMoney($value) : formatMoney($value) !!}</h1>
    <x-tooltip id="{{ $name }}" content="{{ number_format($value) }}" />
</div>
