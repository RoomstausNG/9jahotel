<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title')</title>
    @vite(['resources/css/app.css'])
</head>

<body class="antialiased">
    <div
        class="relative flex justify-center min-h-screen bg-gray-100 items-top dark:bg-gray-900 sm:items-center sm:pt-0">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-col items-center pt-8 space-y-6 text-center sm:justify-start sm:pt-0">
                <div class="px-4 text-2xl tracking-wider text-gray-500 border-gray-400">
                    <h1 class="font-black text-center text-7xl md:text-[150px] lg:text-[200px]">
                        @yield('code')
                    </h1>
                </div>

                <div class="ml-4 text-lg tracking-wider text-center text-gray-500 uppercase">
                    @yield('message')
                </div>
            </div>
        </div>
    </div>


    <script>
        function refreshPage() {

            var cookies = document.cookie.split(";");

            for (var i = 0; i < cookies.length; i++) {
                var cookie = cookies[i];
                var eqPos = cookie.indexOf("=");
                var name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
                document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/";
            }

            // Clear session storage
            sessionStorage.clear();

            // Clear local storage
            localStorage.clear();

            // Redirect to another page
            window.location.href = "{{ route('login') }}";

        }
    </script>
</body>

</html>
