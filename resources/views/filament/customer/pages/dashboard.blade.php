@extends('filament::page')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
        @foreach ($stats as $stat)
            <div class="bg-white rounded shadow p-6 flex flex-col items-center">
                <div class="text-2xl font-bold">{{ $stat->getValue() }}</div>
                <div class="text-gray-600 mt-2">{{ $stat->getLabel() }}</div>
            </div>
        @endforeach
    </div>
@endsection
