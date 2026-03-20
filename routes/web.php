<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\SavingsGoalController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ExchangeRateController;
use App\Http\Controllers\RecurringTransactionController;
use App\Http\Controllers\AccountAdjustmentController;
use App\Http\Controllers\LoanInstallmentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Auth routes (guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.update');
});

// Logout
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Language switch
Route::get('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');

// Authenticated routes
Route::middleware('auth')->group(function () {

    // Root redirect
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Resource routes
    Route::resource('accounts', AccountController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('expenses', ExpenseController::class);
    Route::resource('incomes', IncomeController::class);
    Route::resource('transfers', TransferController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::resource('budgets', BudgetController::class);
    Route::resource('recurring-transactions', RecurringTransactionController::class)->except(['show']);
    Route::post('/recurring-transactions/{recurringTransaction}/process', [RecurringTransactionController::class, 'process'])->name('recurring-transactions.process');
    Route::resource('savings-goals', SavingsGoalController::class)->parameters(['savings-goals' => 'goal']);
    Route::post('/savings-goals/{goal}/contribute', [SavingsGoalController::class, 'contribute'])->name('savings-goals.contribute');
    Route::resource('loans', LoanController::class);
    Route::post('/loans/{loan}/payment', [LoanController::class, 'payment'])->name('loans.payment');
    Route::get('/loans/{loan}/installments', [LoanInstallmentController::class, 'index'])->name('loans.installments.index');
    Route::post('/loans/{loan}/installments', [LoanInstallmentController::class, 'store'])->name('loans.installments.store');
    Route::put('/loans/{loan}/installments/{payment}', [LoanInstallmentController::class, 'update'])->name('loans.installments.update');
    Route::get('/exchange-rates', [ExchangeRateController::class, 'index'])->name('exchange-rates.index');
    Route::post('/exchange-rates', [ExchangeRateController::class, 'store'])->name('exchange-rates.store');
    Route::delete('/exchange-rates/{exchangeRate}', [ExchangeRateController::class, 'destroy'])->name('exchange-rates.destroy');
    Route::get('/account-adjustments', [AccountAdjustmentController::class, 'index'])->name('account-adjustments.index');
    Route::post('/account-adjustments', [AccountAdjustmentController::class, 'store'])->name('account-adjustments.store');

    // Alerts
    Route::get('/alerts', [AlertController::class, 'index'])->name('alerts.index');
    Route::post('/alerts/{alert}/read', [AlertController::class, 'read'])->name('alerts.read');
    Route::post('/alerts/read-all', [AlertController::class, 'readAll'])->name('alerts.read-all');
    Route::post('/alerts/{alert}/dismiss', [AlertController::class, 'dismiss'])->name('alerts.dismiss');
    Route::get('/alerts/unread-count', [AlertController::class, 'unreadCount'])->name('alerts.unread-count');

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/income-vs-expense', [ReportController::class, 'incomeVsExpense'])->name('income-vs-expense');
        Route::get('/category-breakdown', [ReportController::class, 'categoryBreakdown'])->name('category-breakdown');
        Route::get('/monthly-trend', [ReportController::class, 'monthlyTrend'])->name('monthly-trend');
        Route::get('/member-spending', [ReportController::class, 'memberSpending'])->name('member-spending');
        Route::get('/account-summary', [ReportController::class, 'accountSummary'])->name('account-summary');
        Route::get('/export', [ReportController::class, 'export'])->name('export');
    });

    // Audit logs
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

    // Settings
    Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
    Route::get('/settings/members', [SettingController::class, 'members'])->name('settings.members');
    Route::post('/settings/members', [SettingController::class, 'addMember'])->name('settings.members.store');
    Route::delete('/settings/members/{user}', [SettingController::class, 'removeMember'])->name('settings.members.destroy');
});
