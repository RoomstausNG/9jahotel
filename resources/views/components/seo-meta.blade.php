@props([
    'title' => null,
    'description' => null,
    'keywords' => null,
    'image' => null,
    'canonicalUrl' => null,
    'type' => null,
    'hotelName' => null,
])


@php
    $defaultTitle = config('app.name') . ' | Search & Save on your bookings';
    $defaultDescription = 'Find and book the best hotel deals in Nigeria. Save on your accommodation with 9jahotels.';
    $defaultKeywords = 'hotels, Nigeria, accommodation, booking, hotel deals, discount hotels, 9jahotels';
    $defaultImage = asset('img/9jahotel_logo.png');
    $defaultCanonicalUrl = request()->url();
    $defaultType = 'website';

    $pageTitle = $hotelName ? "$hotelName | " . config('app.name') : $title ?? $defaultTitle;
    $pageDescription = $description ?? $defaultDescription;
    $pageKeywords = $keywords ?? $defaultKeywords;
    $pageImage = $image ?? $defaultImage;
    $pageCanonicalUrl = $canonicalUrl ?? $defaultCanonicalUrl;
    $pageType = $type ?? $defaultType;
@endphp

{{-- Primary Meta Tags --}}
<title>{{ $pageTitle }}</title>
<meta name="title" content="{{ $pageTitle }}">
<meta name="description" content="{{ $pageDescription }}">
<meta name="keywords" content="{{ $pageKeywords }}">

{{-- Canonical URL --}}
<link rel="canonical" href="{{ $pageCanonicalUrl }}">

{{-- Open Graph / Facebook --}}
<meta property="og:type" content="{{ $pageType }}">
<meta property="og:url" content="{{ $pageCanonicalUrl }}">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ $pageDescription }}">
<meta property="og:image" content="{{ $pageImage }}">

{{-- Twitter --}}
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="{{ $pageCanonicalUrl }}">
<meta property="twitter:title" content="{{ $pageTitle }}">
<meta property="twitter:description" content="{{ $pageDescription }}">
<meta property="twitter:image" content="{{ $pageImage }}">
