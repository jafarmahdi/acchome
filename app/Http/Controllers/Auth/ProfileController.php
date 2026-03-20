<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show(): View
    {
        return view('auth.profile', ['user' => Auth::user()]);
    }

    public function edit(): View
    {
        return view('auth.profile', ['user' => Auth::user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'locale' => ['nullable', 'string', Rule::in(['en', 'ar'])],
            'direction' => ['nullable', 'string', Rule::in(['ltr', 'rtl'])],
            'email_notifications' => ['nullable', 'boolean'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'password' => ['nullable', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $oldValues = $user->only(['name', 'email', 'phone', 'date_of_birth', 'locale', 'direction', 'email_notifications']);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $validated['email_notifications'] = $request->boolean('email_notifications');

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        $newValues = $user->only(['name', 'email', 'phone', 'date_of_birth', 'locale', 'direction', 'email_notifications']);

        AuditService::log('profile_updated', $user, $oldValues, $newValues, 'User updated their profile.');

        return redirect()->route('profile.edit')->with('success', __('Profile updated successfully.'));
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($request->input('password')),
        ]);

        AuditService::log('password_changed', $user, description: 'User changed their password.');

        return redirect()->route('profile.edit')->with('success', __('Password changed successfully.'));
    }

    public function deleteAvatar(): RedirectResponse
    {
        $user = Auth::user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->update(['avatar' => null]);
        }

        return redirect()->route('profile.edit')->with('success', __('Avatar removed.'));
    }
}
