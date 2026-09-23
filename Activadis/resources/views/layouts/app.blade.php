<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Activadis')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 antialiased">
    <div class="min-h-screen">
        <header class="bg-brand-dark">
            <div class="mx-auto flex max-w-5xl items-center justify-between px-6 py-4">
                <a href="{{ route('activities.index') }}" class="text-lg font-semibold text-white">Activadis</a>
                <a href="{{ route('activities.create') }}" class="rounded-md bg-brand-primary px-4 py-2 text-sm font-medium text-white hover:opacity-90">
                    Nieuwe activiteit
                </a>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-6 py-10">
            @if (session('status'))
                <div class="mb-6 rounded-md bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
