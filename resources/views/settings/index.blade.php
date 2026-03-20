@extends('layouts.app')
@section('title', __('Settings'))
@section('page-title', __('Settings'))

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Family Settings -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Family Settings') }}</h3>
        <form method="POST" action="{{ url('/settings') }}">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Family Name') }}</label>
                    <input type="text" name="family_name" value="{{ auth()->user()->family->name }}"
                           class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Currency') }}</label>
                    <select name="currency" class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="IQD" {{ ($settings['currency'] ?? auth()->user()->family->currency) === 'IQD' ? 'selected' : '' }}>IQD (د.ع)</option>
                        <option value="USD" {{ ($settings['currency'] ?? auth()->user()->family->currency) === 'USD' ? 'selected' : '' }}>USD ($)</option>
                        <option value="EUR" {{ ($settings['currency'] ?? auth()->user()->family->currency) === 'EUR' ? 'selected' : '' }}>EUR (EUR)</option>
                        <option value="GBP" {{ ($settings['currency'] ?? auth()->user()->family->currency) === 'GBP' ? 'selected' : '' }}>GBP (GBP)</option>
                        <option value="SAR" {{ ($settings['currency'] ?? auth()->user()->family->currency) === 'SAR' ? 'selected' : '' }}>SAR (ر.س)</option>
                        <option value="AED" {{ ($settings['currency'] ?? auth()->user()->family->currency) === 'AED' ? 'selected' : '' }}>AED (د.إ)</option>
                    </select>
                    <p class="text-xs text-gray-400 mt-1">{{ __('New accounts and family totals will use this currency by default.') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Language') }}</label>
                    <select name="locale" class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="ar" {{ ($settings['locale'] ?? auth()->user()->locale) === 'ar' ? 'selected' : '' }}>العربية</option>
                        <option value="en" {{ ($settings['locale'] ?? auth()->user()->locale) === 'en' ? 'selected' : '' }}>English</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Direction') }}</label>
                    <select name="direction" class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="rtl" {{ ($settings['direction'] ?? auth()->user()->direction) === 'rtl' ? 'selected' : '' }}>RTL (Right to Left)</option>
                        <option value="ltr" {{ ($settings['direction'] ?? auth()->user()->direction) === 'ltr' ? 'selected' : '' }}>LTR (Left to Right)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Timezone') }}</label>
                    <input type="text" name="timezone" value="{{ $settings['timezone'] ?? auth()->user()->family->timezone }}"
                           class="w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex items-end">
                    <label class="flex items-center">
                        <input type="checkbox" name="email_notifications" value="1" {{ auth()->user()->email_notifications ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-600 {{ is_rtl() ? 'mr-2' : 'ml-2' }}">{{ __('Email Notifications') }}</span>
                    </label>
                </div>
            </div>
            <div class="mt-5">
                <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">{{ __('Save Settings') }}</button>
            </div>
        </form>
    </div>

    <!-- Family Members -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">{{ __('Family Members') }}</h3>
        </div>

        <div class="space-y-3 mb-6">
            @foreach($members ?? [] as $member)
            <div class="flex items-center justify-between py-3 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                <div class="flex items-center space-x-3 {{ is_rtl() ? 'space-x-reverse' : '' }}">
                    <img src="{{ $member->avatar_url }}" class="w-10 h-10 rounded-full object-cover">
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $member->name }}</p>
                        <p class="text-xs text-gray-400">{{ $member->email }} &middot; {{ __(ucfirst($member->role)) }} &middot; {{ __(ucfirst($member->relation)) }}</p>
                    </div>
                </div>
                @if($member->id !== auth()->id())
                <form method="POST" action="{{ url('/settings/members/' . $member->id) }}" onsubmit="return confirm('{{ __('Remove this member?') }}')">
                    @csrf @method('DELETE')
                    <button class="text-xs text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></button>
                </form>
                @endif
            </div>
            @endforeach
        </div>

        <!-- Add Member Form -->
        <div class="bg-gray-50 rounded-lg p-4">
            <h4 class="text-sm font-medium text-gray-700 mb-3">{{ __('Add Family Member') }}</h4>
            <form method="POST" action="{{ url('/settings/members') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @csrf
                <input type="text" name="name" placeholder="{{ __('Name') }}" required class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                <input type="email" name="email" placeholder="{{ __('Email') }}" required class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                <input type="password" name="password" placeholder="{{ __('Password') }}" required class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                <input type="password" name="password_confirmation" placeholder="{{ __('Confirm Password') }}" required class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                <select name="relation" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    @foreach(['wife','husband','son','daughter','parent','other'] as $r)
                        <option value="{{ $r }}">{{ __(ucfirst($r)) }}</option>
                    @endforeach
                </select>
                <select name="role" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="member">{{ __('Member') }}</option>
                    <option value="viewer">{{ __('Viewer') }}</option>
                    <option value="admin">{{ __('Admin') }}</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">{{ __('Add Member') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection
