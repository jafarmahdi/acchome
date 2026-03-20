@extends('layouts.guest')
@section('title', __('Forgot Password'))

@section('content')
<h2 class="text-xl font-semibold text-gray-800 mb-2">{{ __('Reset Password') }}</h2>
<p class="text-sm text-gray-500 mb-6">{{ __('Enter your email and we\'ll send you a reset link.') }}</p>

<form method="POST" action="{{ url('/forgot-password') }}" class="space-y-5">
    @csrf
    <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Email Address') }}</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
               class="w-full border border-gray-300 rounded-lg py-2.5 px-4 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
    </div>

    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 rounded-lg transition-colors">
        {{ __('Send Reset Link') }}
    </button>
</form>

<p class="mt-6 text-center text-sm text-gray-600">
    <a href="{{ url('/login') }}" class="text-blue-600 hover:text-blue-800 font-medium">{{ __('Back to Login') }}</a>
</p>
@endsection
