<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - {{ config('app.name', 'Running') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex min-h-screen items-center justify-center bg-gray-50 px-4 font-sans">
    <main class="w-full max-w-lg rounded-3xl bg-white p-8 text-center shadow-xl">
        <p class="mb-2 text-sm font-black uppercase tracking-widest text-orange-600">{{ config('app.name', 'Running') }} · Error {{ $code }}</p>
        <h1 class="mb-3 text-3xl font-black text-gray-900">{{ $title }}</h1>
        <p class="mb-8 text-gray-600">{{ $message }}</p>
        <a href="{{ url('/') }}" class="inline-block rounded-xl bg-orange-500 px-6 py-3 font-bold text-white hover:bg-orange-600">
            Volver al inicio
        </a>
    </main>
</body>
</html>
