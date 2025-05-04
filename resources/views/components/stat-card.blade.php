@props([
    'title',
    'value',
    'icon',
    'gradient',
    'subtitle' => ''
])
<div class="rounded-xl p-6 h-40 flex flex-col justify-between relative overflow-hidden" style="background: {{ $gradient }};">
    <div class="flex justify-between items-start">
        <div class="text-white text-lg font-medium">{{ $title }}</div>
        <div class="h-6 w-6 text-white opacity-80">{!! $icon !!}</div>
    </div>
    <div class="text-white text-3xl font-bold">{{ $value }}</div>
    @if($subtitle)
        <div class="text-white text-sm opacity-80">{{ $subtitle }}</div>
    @endif
    <div class="absolute right-0 bottom-0 w-32 h-32 bg-white opacity-10 rounded-full -mb-12 -mr-12"></div>
</div>