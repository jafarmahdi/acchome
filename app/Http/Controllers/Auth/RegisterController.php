<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Category;
use App\Models\Family;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'family_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        $user = DB::transaction(function () use ($request) {
            $family = Family::create([
                'name' => $request->input('family_name'),
                'currency' => $request->input('currency', 'IQD'),
                'currency_symbol' => currency_symbol($request->input('currency', 'IQD')),
                'locale' => 'ar',
                'direction' => 'rtl',
                'timezone' => 'Asia/Baghdad',
            ]);

            $avatarPath = null;
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
            }

            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'phone' => $request->input('phone'),
                'avatar' => $avatarPath,
                'family_id' => $family->id,
                'role' => 'admin',
                'locale' => 'ar',
                'direction' => 'rtl',
                'email_notifications' => true,
            ]);

            $this->createDefaultCategories($family->id);
            $this->createDefaultAccount($family->id, $user->id);

            return $user;
        });

        AuditService::log('register', $user, description: 'New user registered and family created.');

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    protected function createDefaultCategories(int $familyId): void
    {
        $categories = [
            ['name' => 'Salary', 'type' => 'income', 'icon' => 'briefcase', 'color' => '#4CAF50'],
            ['name' => 'Freelance', 'type' => 'income', 'icon' => 'laptop', 'color' => '#8BC34A'],
            ['name' => 'Investments', 'type' => 'income', 'icon' => 'trending-up', 'color' => '#009688'],
            ['name' => 'Food & Groceries', 'type' => 'expense', 'icon' => 'shopping-cart', 'color' => '#FF5722'],
            ['name' => 'Transportation', 'type' => 'expense', 'icon' => 'truck', 'color' => '#FF9800'],
            ['name' => 'Housing & Rent', 'type' => 'expense', 'icon' => 'home', 'color' => '#795548'],
            ['name' => 'Utilities', 'type' => 'expense', 'icon' => 'zap', 'color' => '#FFC107'],
            ['name' => 'Healthcare', 'type' => 'expense', 'icon' => 'heart', 'color' => '#F44336'],
            ['name' => 'Education', 'type' => 'expense', 'icon' => 'book', 'color' => '#3F51B5'],
            ['name' => 'Entertainment', 'type' => 'expense', 'icon' => 'film', 'color' => '#9C27B0'],
            ['name' => 'Shopping', 'type' => 'expense', 'icon' => 'shopping-bag', 'color' => '#E91E63'],
            ['name' => 'Personal Care', 'type' => 'expense', 'icon' => 'user', 'color' => '#00BCD4'],
            ['name' => 'Insurance', 'type' => 'expense', 'icon' => 'shield', 'color' => '#607D8B'],
            ['name' => 'Savings', 'type' => 'expense', 'icon' => 'dollar-sign', 'color' => '#2196F3'],
            ['name' => 'Other', 'type' => 'expense', 'icon' => 'more-horizontal', 'color' => '#9E9E9E'],
        ];

        foreach ($categories as $category) {
            Category::create(array_merge($category, [
                'family_id' => $familyId,
                'is_default' => true,
            ]));
        }
    }

    protected function createDefaultAccount(int $familyId, int $userId): void
    {
        Account::create([
            'name' => 'Cash',
            'type' => 'cash',
            'balance' => 0,
            'currency' => 'IQD',
            'family_id' => $familyId,
            'user_id' => $userId,
            'is_default' => true,
        ]);
    }
}
