@extends('layouts.guest')
@section('title', __('Register'))

@section('content')
<h2 class="text-xl font-semibold text-gray-800 mb-6">{{ __('Create your account') }}</h2>

<form method="POST" action="{{ url('/register') }}" enctype="multipart/form-data" class="space-y-4">
    @csrf

    <div>
        <label for="family_name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Family Name') }}</label>
        <input type="text" id="family_name" name="family_name" value="{{ old('family_name') }}" required
               class="w-full border border-gray-300 rounded-lg py-2.5 px-4 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
               placeholder="{{ __('e.g. Al-Hassan Family') }}">
    </div>

    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Your Name') }}</label>
        <input type="text" id="name" name="name" value="{{ old('name') }}" required
               class="w-full border border-gray-300 rounded-lg py-2.5 px-4 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
    </div>

    <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Email Address') }}</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required
               class="w-full border border-gray-300 rounded-lg py-2.5 px-4 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Password') }}</label>
            <input type="password" id="password" name="password" required
                   class="w-full border border-gray-300 rounded-lg py-2.5 px-4 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
        </div>
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Confirm') }}</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required
                   class="w-full border border-gray-300 rounded-lg py-2.5 px-4 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
        </div>
    </div>

    <div>
        <label for="avatar" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Profile Photo') }} <span class="text-gray-400">({{ __('optional') }})</span></label>
        <input type="file" id="avatar" name="avatar" accept="image/*"
               class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm file:mr-4 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
    </div>

    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 rounded-lg transition-colors shadow-sm">
        {{ __('Create Account') }}
    </button>
</form>

<p class="mt-6 text-center text-sm text-gray-600">
    {{ __('Already have an account?') }}
    <a href="{{ url('/login') }}" class="text-blue-600 hover:text-blue-800 font-medium">{{ __('Sign In') }}</a>
</p>
@endsection
