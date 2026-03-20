<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app_direction() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Login') - {{ config('app.name', 'Smart Household Finance Monitor') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Noto+Sans+Arabic:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', 'Noto Sans Arabic', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-indigo-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600 rounded-2xl shadow-lg mb-4">
                <i class="fas fa-wallet text-white text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">{{ config('app.name') }}</h1>
            <p class="text-gray-500 mt-1">{{ __('Smart family finance management') }}</p>
        </div>

        <!-- Card -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8">
            @if($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            @if(session('status'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
                    {{ session('status') }}
                </div>
            @endif

            @yield('content')
        </div>

        <!-- Language toggle -->
        <div class="text-center mt-6">
            <a href="{{ url('/language/' . (app()->getLocale() === 'en' ? 'ar' : 'en')) }}"
               class="text-sm text-gray-500 hover:text-blue-600 transition-colors">
                {{ app()->getLocale() === 'en' ? 'العربية' : 'English' }}
            </a>
        </div>
    </div>
</body>
</html>
