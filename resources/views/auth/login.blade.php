@extends('layouts.guest')
@section('title', __('Login'))

@section('content')
<h2 class="text-xl font-semibold text-gray-800 mb-6">{{ __('Sign in to your account') }}</h2>

<form method="POST" action="{{ url('/login') }}" class="space-y-5">
    @csrf

    <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Email Address') }}</label>
        <div class="relative">
            <span class="absolute inset-y-0 {{ is_rtl() ? 'right-0 pr-3' : 'left-0 pl-3' }} flex items-center text-gray-400">
                <i class="fas fa-envelope"></i>
            </span>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                   class="{{ is_rtl() ? 'pr-10' : 'pl-10' }} w-full border border-gray-300 rounded-lg py-2.5 px-4 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                   placeholder="your@email.com">
        </div>
    </div>

    <div>
        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Password') }}</label>
        <div class="relative">
            <span class="absolute inset-y-0 {{ is_rtl() ? 'right-0 pr-3' : 'left-0 pl-3' }} flex items-center text-gray-400">
                <i class="fas fa-lock"></i>
            </span>
            <input type="password" id="password" name="password" required
                   class="{{ is_rtl() ? 'pr-10' : 'pl-10' }} w-full border border-gray-300 rounded-lg py-2.5 px-4 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                   placeholder="••••••••">
        </div>
    </div>

    <div class="flex items-center justify-between">
        <label class="flex items-center">
            <input type="checkbox" name="remember" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
            <span class="text-sm text-gray-600 {{ is_rtl() ? 'mr-2' : 'ml-2' }}">{{ __('Remember me') }}</span>
        </label>
        <a href="{{ url('/forgot-password') }}" class="text-sm text-blue-600 hover:text-blue-800">{{ __('Forgot password?') }}</a>
    </div>

    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 rounded-lg transition-colors shadow-sm">
        {{ __('Sign In') }}
    </button>
</form>

<p class="mt-6 text-center text-sm text-gray-600">
    {{ __("Don't have an account?") }}
    <a href="{{ url('/register') }}" class="text-blue-600 hover:text-blue-800 font-medium">{{ __('Register') }}</a>
</p>
@endsection
