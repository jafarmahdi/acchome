<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public auth routes
Route::post('/login', [ApiController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    // Dashboard
    Route::get('/dashboard/summary', [ApiController::class, 'dashboardSummary']);

    // Accounts
    Route::get('/accounts', [ApiController::class, 'accountIndex']);
    Route::post('/accounts', [ApiController::class, 'accountStore']);
    Route::get('/accounts/{account}', [ApiController::class, 'accountShow']);
    Route::put('/accounts/{account}', [ApiController::class, 'accountUpdate']);
    Route::delete('/accounts/{account}', [ApiController::class, 'accountDestroy']);

    // Transactions (covers expenses + incomes)
    Route::get('/transactions', [ApiController::class, 'transactionIndex']);
    Route::post('/transactions', [ApiController::class, 'transactionStore']);
    Route::get('/transactions/{transaction}', [ApiController::class, 'transactionShow']);
    Route::put('/transactions/{transaction}', [ApiController::class, 'transactionUpdate']);
    Route::delete('/transactions/{transaction}', [ApiController::class, 'transactionDestroy']);

    // Transfers
    Route::post('/transfers', [ApiController::class, 'transferStore']);

    // Budgets
    Route::get('/budgets', [ApiController::class, 'budgetIndex']);
    Route::post('/budgets', [ApiController::class, 'budgetStore']);
    Route::put('/budgets/{budget}', [ApiController::class, 'budgetUpdate']);
    Route::delete('/budgets/{budget}', [ApiController::class, 'budgetDestroy']);

    // Savings Goals
    Route::get('/savings-goals', [ApiController::class, 'savingsIndex']);
    Route::post('/savings-goals', [ApiController::class, 'savingsStore']);
    Route::post('/savings-goals/{savingsGoal}/contribute', [ApiController::class, 'savingsContribute']);

    // Loans
    Route::get('/loans', [ApiController::class, 'loanIndex']);
    Route::post('/loans', [ApiController::class, 'loanStore']);
    Route::post('/loans/{loan}/payment', [ApiController::class, 'loanPayment']);

    // Alerts
    Route::get('/alerts', [ApiController::class, 'alertIndex']);
    Route::post('/alerts/{alert}/read', [ApiController::class, 'alertRead']);

    // Reports
    Route::get('/reports/summary', [ApiController::class, 'reportSummary']);
    Route::get('/reports/category-breakdown', [ApiController::class, 'reportCategoryBreakdown']);
});
