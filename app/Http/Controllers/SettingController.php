<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Family;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SettingController extends Controller
{
    public function index()
    {
        return $this->edit();
    }

    public function edit()
    {
        $family = auth()->user()->family;
        $settings = [
            'currency' => $family->getSetting('currency', $family->currency ?? 'IQD'),
            'currency_symbol' => $family->getSetting('currency_symbol', $family->currency_symbol ?? 'د.ع'),
            'timezone' => $family->getSetting('timezone', $family->timezone ?? 'UTC'),
            'locale' => $family->getSetting('locale', $family->locale ?? 'ar'),
            'direction' => $family->getSetting('direction', $family->direction ?? 'rtl'),
            'date_format' => $family->getSetting('date_format', 'Y-m-d'),
            'email_budget_alerts' => $family->getSetting('email_budget_alerts', 'true'),
            'email_low_balance' => $family->getSetting('email_low_balance', 'true'),
            'email_loan_reminders' => $family->getSetting('email_loan_reminders', 'true'),
        ];

        $members = User::where('family_id', auth()->user()->family_id)
            ->orderBy('name')
            ->get();

        return view('settings.index', compact('family', 'settings', 'members'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'family_name' => 'required|string|max:255',
            'currency' => 'required|string|max:10',
            'currency_symbol' => 'nullable|string|max:5',
            'timezone' => 'required|string|max:100',
            'locale' => 'required|in:en,ar',
            'direction' => 'required|in:ltr,rtl',
            'date_format' => 'nullable|string|max:20',
            'email_notifications' => 'nullable|boolean',
        ]);

        $family = auth()->user()->family;
        $user = auth()->user();
        $oldValues = $family->toArray();

        $family->update([
            'name' => $validated['family_name'],
            'currency' => $validated['currency'],
            'currency_symbol' => currency_symbol($validated['currency']),
            'timezone' => $validated['timezone'],
            'locale' => $validated['locale'],
            'direction' => $validated['direction'],
        ]);

        $family->setSetting('date_format', $validated['date_format'] ?? 'Y-m-d', 'general');

        $user->update([
            'locale' => $validated['locale'],
            'direction' => $validated['direction'],
            'email_notifications' => $request->boolean('email_notifications'),
        ]);

        AuditLog::record('updated', $family, $oldValues, $family->fresh()->toArray(), 'Updated family settings');

        return redirect()->route('settings.edit')
            ->with('success', __('Settings updated successfully.'));
    }

    public function familyMembers()
    {
        return $this->members();
    }

    public function members()
    {
        return $this->edit();
    }

    public function addMember(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,member,viewer',
            'relation' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
        ]);

        $validated['family_id'] = auth()->user()->family_id;
        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = true;
        $validated['locale'] = auth()->user()->locale ?? 'en';
        $validated['direction'] = auth()->user()->direction ?? 'ltr';

        $member = User::create($validated);

        AuditLog::record('created', $member, null, $member->toArray(), 'Added family member: ' . $member->name);

        return redirect()->route('settings.members')
            ->with('success', __('Family member added successfully.'));
    }

    public function removeMember(User $user)
    {
        $familyId = auth()->user()->family_id;
        abort_if($user->family_id !== $familyId, 403);
        abort_if($user->id === auth()->id(), 403, __('You cannot remove yourself.'));

        $oldValues = $user->toArray();
        $user->update(['is_active' => false]);
        $user->delete();

        AuditLog::record('deleted', $user, $oldValues, null, 'Removed family member: ' . $user->name);

        return redirect()->route('settings.members')
            ->with('success', __('Family member removed successfully.'));
    }
}
