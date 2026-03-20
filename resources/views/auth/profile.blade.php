@extends('layouts.app')
@section('title', __('Profile'))
@section('page-title', __('My Profile'))

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center space-x-4 {{ is_rtl() ? 'space-x-reverse' : '' }} mb-6 pb-6 border-b border-gray-100">
            <img src="{{ auth()->user()->avatar_url }}" class="w-20 h-20 rounded-full object-cover border-4 border-blue-100">
            <div>
                <h2 class="text-xl font-bold text-gray-800">{{ auth()->user()->name }}</h2>
                <p class="text-sm text-gray-500">{{ auth()->user()->email }}</p>
                <p class="text-xs text-gray-400 capitalize">{{ auth()->user()->role }} &middot; {{ __(ucfirst(auth()->user()->relation)) }}</p>
            </div>
        </div>

        <form method="POST" action="{{ url('/profile') }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Name') }}</label>
                    <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required
                           class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Email') }}</label>
                    <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required
                           class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Phone') }}</label>
                    <input type="text" name="phone" value="{{ old('phone', auth()->user()->phone) }}"
                           class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Date of Birth') }}</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth', auth()->user()->date_of_birth?->format('Y-m-d')) }}"
                           class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Profile Photo') }}</label>
                    <input type="file" name="avatar" accept="image/*"
                           class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm file:mr-4 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:bg-blue-50 file:text-blue-700">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Language') }}</label>
                    <select name="locale" class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="en" {{ auth()->user()->locale === 'en' ? 'selected' : '' }}>English</option>
                        <option value="ar" {{ auth()->user()->locale === 'ar' ? 'selected' : '' }}>العربية</option>
                    </select>
                </div>
                <div class="sm:col-span-2 pt-4 border-t border-gray-100">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">{{ __('Change Password') }} <span class="text-gray-400">({{ __('optional') }})</span></h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <input type="password" name="password" placeholder="{{ __('New Password') }}"
                                   class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <input type="password" name="password_confirmation" placeholder="{{ __('Confirm Password') }}"
                                   class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-6">
                <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">{{ __('Update Profile') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
