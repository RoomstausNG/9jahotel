@props([
    'title' => config('app.name') . ' | Search & Save on your bookings',
    'description' => 'Find and book the best hotel deals in Nigeria. Save on your accommodation with 9jahotels.',
    'keywords' => 'hotels, Nigeria, accommodation, booking, hotel deals, discount hotels, 9jahotels',
    'image' => asset('img/9jahotel_logo.png'),
    'canonicalUrl' => request()->url(),
    'type' => 'website',
    'hotelName' => null,
])

@php
    // Prepare dynamic title based on whether a hotel name is provided
    $pageTitle = $hotelName ? "$hotelName | " . config('app.name') : $title;
@endphp

{{-- Primary Meta Tags --}}
<title>{{ $pageTitle }}</title>
<meta name="title" content="{{ $pageTitle }}">
<meta name="description" content="{{ $description }}">
<meta name="keywords" content="{{ $keywords }}">

{{-- Canonical URL --}}
<link rel="canonical" href="{{ $canonicalUrl }}">

{{-- Open Graph / Facebook --}}
<meta property="og:type" content="{{ $type }}">
<meta property="og:url" content="{{ $canonicalUrl }}">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:image" content="{{ $image }}">

{{-- Twitter --}}
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="{{ $canonicalUrl }}">
<meta property="twitter:title" content="{{ $pageTitle }}">
<meta property="twitter:description" content="{{ $description }}">
<meta property="twitter:image" content="{{ $image }}">
