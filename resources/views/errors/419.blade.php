@extends('errors.minimal')

@section('title', __('Page Expired'))
@section('code', '419')
@section('message')
    <div class="mx-auto space-y-4">
        <h1> {{ __('Page Expired') }}</h1>
        <p class="text-black lowercase first-letter:capitalize">Please refresh the page as it has been idle for too long and
            the session
            has expired. This
            ensures your data
            remains secure and up-to-date.</p>
        <button onclick="refreshPage()" class="px-6 py-2 text-white rounded-md cursor-pointer bg-primary">Refresh
            Page</button>
    </div>
@endsection
