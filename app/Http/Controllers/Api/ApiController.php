<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Alert;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Loan;
use App\Models\SavingsGoal;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Info(
 *     title="Smart Household Finance Monitor API",
 *     version="1.0.0",
 *     description="API for managing household finances including accounts, transactions, budgets, savings goals, and loans.",
 *     @OA\Contact(email="admin@household-finance.app")
 * )
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class ApiController extends Controller
{
    // ─── AUTH ───

    /**
     * @OA\Post(path="/api/login", tags={"Auth"}, summary="Login and get API token",
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"email","password"},
     *         @OA\Property(property="email", type="string", example="ahmed@example.com"),
     *         @OA\Property(property="password", type="string", example="password")
     *     )),
     *     @OA\Response(response=200, description="Token returned"),
     *     @OA\Response(response=401, description="Invalid credentials")
     * )
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user->only('id', 'name', 'email', 'role', 'family_id'),
        ]);
    }

    // ─── DASHBOARD ───

    /**
     * @OA\Get(path="/api/dashboard/summary", tags={"Dashboard"}, summary="Get dashboard summary",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Dashboard summary data")
     * )
     */
    public function dashboardSummary(Request $request): JsonResponse
    {
        $familyId = $request->user()->family_id;

        $totalBalance = Account::where('family_id', $familyId)->where('is_active', true)->where('include_in_total', true)->sum('balance');
        $monthlyIncome = Transaction::where('family_id', $familyId)->where('type', 'income')->thisMonth()->sum('amount');
        $monthlyExpenses = Transaction::where('family_id', $familyId)->where('type', 'expense')->thisMonth()->sum('amount');

        return response()->json([
            'total_balance' => (float) $totalBalance,
            'monthly_income' => (float) $monthlyIncome,
            'monthly_expenses' => (float) $monthlyExpenses,
            'net_savings' => (float) ($monthlyIncome - $monthlyExpenses),
            'accounts_count' => Account::where('family_id', $familyId)->where('is_active', true)->count(),
            'unread_alerts' => Alert::where('family_id', $familyId)->unread()->count(),
        ]);
    }

    // ─── ACCOUNTS ───

    /**
     * @OA\Get(path="/api/accounts", tags={"Accounts"}, summary="List all accounts",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="type", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="List of accounts")
     * )
     */
    public function accountIndex(Request $request): JsonResponse
    {
        $accounts = Account::where('family_id', $request->user()->family_id)
            ->when($request->type, fn($q, $type) => $q->where('type', $type))
            ->with('user:id,name')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $accounts]);
    }

    /**
     * @OA\Post(path="/api/accounts", tags={"Accounts"}, summary="Create account",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"name","type","balance"},
     *         @OA\Property(property="name", type="string"),
     *         @OA\Property(property="type", type="string", enum={"cash","bank","savings","credit_card","loan","rewards","other"}),
     *         @OA\Property(property="balance", type="number")
     *     )),
     *     @OA\Response(response=201, description="Account created")
     * )
     */
    public function accountStore(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:cash,bank,savings,credit_card,loan,rewards,other',
            'balance' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $account = Account::create(array_merge(
            $request->only('name', 'type', 'balance', 'bank_name', 'account_number', 'color', 'icon', 'low_balance_threshold', 'notes', 'user_id'),
            ['family_id' => $request->user()->family_id, 'initial_balance' => $request->balance]
        ));

        return response()->json(['data' => $account], 201);
    }

    /**
     * @OA\Get(path="/api/accounts/{id}", tags={"Accounts"}, summary="Get account details",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Account details")
     * )
     */
    public function accountShow(Request $request, Account $account): JsonResponse
    {
        if ($account->family_id !== $request->user()->family_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        return response()->json(['data' => $account->load('user:id,name', 'transactions')]);
    }

    public function accountUpdate(Request $request, Account $account): JsonResponse
    {
        if ($account->family_id !== $request->user()->family_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        $account->update($request->only('name', 'type', 'bank_name', 'account_number', 'color', 'icon', 'low_balance_threshold', 'notes', 'user_id'));
        return response()->json(['data' => $account->fresh()]);
    }

    public function accountDestroy(Request $request, Account $account): JsonResponse
    {
        if ($account->family_id !== $request->user()->family_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        $account->delete();
        return response()->json(['message' => 'Account deleted']);
    }

    // ─── TRANSACTIONS ───

    /**
     * @OA\Get(path="/api/transactions", tags={"Transactions"}, summary="List transactions",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="type", in="query", @OA\Schema(type="string", enum={"expense","income","transfer"})),
     *     @OA\Parameter(name="from", in="query", @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="to", in="query", @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="category_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="account_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="List of transactions")
     * )
     */
    public function transactionIndex(Request $request): JsonResponse
    {
        $transactions = Transaction::where('family_id', $request->user()->family_id)
            ->when($request->type, fn($q, $t) => $q->where('type', $t))
            ->when($request->category_id, fn($q, $c) => $q->where('category_id', $c))
            ->when($request->account_id, fn($q, $a) => $q->where('account_id', $a))
            ->when($request->from, fn($q, $f) => $q->where('transaction_date', '>=', $f))
            ->when($request->to, fn($q, $t) => $q->where('transaction_date', '<=', $t))
            ->with(['user:id,name', 'account:id,name', 'category:id,name,color'])
            ->orderByDesc('transaction_date')
            ->paginate(20);

        return response()->json($transactions);
    }

    /**
     * @OA\Post(path="/api/transactions", tags={"Transactions"}, summary="Create transaction",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"type","amount","description","account_id","transaction_date"},
     *         @OA\Property(property="type", type="string", enum={"expense","income"}),
     *         @OA\Property(property="amount", type="number"),
     *         @OA\Property(property="description", type="string"),
     *         @OA\Property(property="account_id", type="integer"),
     *         @OA\Property(property="category_id", type="integer"),
     *         @OA\Property(property="transaction_date", type="string", format="date"),
     *         @OA\Property(property="payment_method", type="string")
     *     )),
     *     @OA\Response(response=201, description="Transaction created")
     * )
     */
    public function transactionStore(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:expense,income',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'account_id' => 'required|exists:accounts,id',
            'transaction_date' => 'required|date',
            'category_id' => 'nullable|exists:categories,id',
            'payment_method' => 'nullable|in:cash,card,bank_transfer,cheque,online,other',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $transaction = Transaction::create(array_merge(
            $request->only('type', 'amount', 'description', 'account_id', 'category_id', 'transaction_date', 'transaction_time', 'payment_method', 'notes', 'location', 'tags'),
            ['family_id' => $request->user()->family_id, 'user_id' => $request->user()->id]
        ));

        $account = Account::find($request->account_id);
        if ($request->type === 'expense') {
            $account->adjustBalance($request->amount, 'subtract');
        } else {
            $account->adjustBalance($request->amount, 'add');
        }

        return response()->json(['data' => $transaction->load('user:id,name', 'account:id,name', 'category:id,name')], 201);
    }

    public function transactionShow(Request $request, Transaction $transaction): JsonResponse
    {
        if ($transaction->family_id !== $request->user()->family_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        return response()->json(['data' => $transaction->load('user', 'account', 'category')]);
    }

    public function transactionUpdate(Request $request, Transaction $transaction): JsonResponse
    {
        if ($transaction->family_id !== $request->user()->family_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        $transaction->update($request->only('description', 'category_id', 'notes', 'location', 'tags'));
        return response()->json(['data' => $transaction->fresh()]);
    }

    public function transactionDestroy(Request $request, Transaction $transaction): JsonResponse
    {
        if ($transaction->family_id !== $request->user()->family_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $account = $transaction->account;
        if ($transaction->type === 'expense') {
            $account->adjustBalance($transaction->amount, 'add');
        } elseif ($transaction->type === 'income') {
            $account->adjustBalance($transaction->amount, 'subtract');
        }

        $transaction->delete();
        return response()->json(['message' => 'Transaction deleted']);
    }

    // ─── TRANSFERS ───

    public function transferStore(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'from_account_id' => 'required|exists:accounts,id|different:to_account_id',
            'to_account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $transaction = Transaction::create([
            'family_id' => $request->user()->family_id,
            'user_id' => $request->user()->id,
            'account_id' => $request->from_account_id,
            'transfer_to_account_id' => $request->to_account_id,
            'type' => 'transfer',
            'amount' => $request->amount,
            'description' => $request->description ?? 'Transfer between accounts',
            'transaction_date' => $request->transaction_date,
            'payment_method' => 'bank_transfer',
        ]);

        Account::find($request->from_account_id)->adjustBalance($request->amount, 'subtract');
        Account::find($request->to_account_id)->adjustBalance($request->amount, 'add');

        return response()->json(['data' => $transaction], 201);
    }

    // ─── BUDGETS ───

    public function budgetIndex(Request $request): JsonResponse
    {
        $budgets = Budget::where('family_id', $request->user()->family_id)
            ->with('category:id,name,color')
            ->orderByDesc('start_date')
            ->get()
            ->map(function ($b) {
                $b->percent_used = $b->percent_used;
                $b->remaining = $b->remaining;
                $b->is_over_budget = $b->isOverBudget();
                return $b;
            });

        return response()->json(['data' => $budgets]);
    }

    public function budgetStore(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'period' => 'required|in:weekly,monthly,quarterly,yearly',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $budget = Budget::create(array_merge(
            $request->only('name', 'amount', 'category_id', 'period', 'start_date', 'end_date', 'alert_threshold', 'notes'),
            ['family_id' => $request->user()->family_id]
        ));

        return response()->json(['data' => $budget], 201);
    }

    public function budgetUpdate(Request $request, Budget $budget): JsonResponse
    {
        if ($budget->family_id !== $request->user()->family_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        $budget->update($request->only('name', 'amount', 'category_id', 'period', 'start_date', 'end_date', 'alert_threshold', 'notes', 'is_active'));
        return response()->json(['data' => $budget->fresh()]);
    }

    public function budgetDestroy(Request $request, Budget $budget): JsonResponse
    {
        if ($budget->family_id !== $request->user()->family_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        $budget->delete();
        return response()->json(['message' => 'Budget deleted']);
    }

    // ─── SAVINGS GOALS ───

    public function savingsIndex(Request $request): JsonResponse
    {
        $goals = SavingsGoal::where('family_id', $request->user()->family_id)
            ->with('account:id,name')
            ->get()
            ->map(function ($g) {
                $g->progress = $g->progress;
                $g->remaining = $g->remaining;
                return $g;
            });

        return response()->json(['data' => $goals]);
    }

    public function savingsStore(Request $request): JsonResponse
    {
        $goal = SavingsGoal::create(array_merge(
            $request->only('name', 'target_amount', 'target_date', 'account_id', 'priority', 'description', 'icon', 'color'),
            ['family_id' => $request->user()->family_id]
        ));
        return response()->json(['data' => $goal], 201);
    }

    public function savingsContribute(Request $request, SavingsGoal $savingsGoal): JsonResponse
    {
        if ($savingsGoal->family_id !== $request->user()->family_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validator = Validator::make($request->all(), ['amount' => 'required|numeric|min:0.01']);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $contribution = $savingsGoal->addContribution($request->amount, $request->user()->id, $request->notes);
        return response()->json(['data' => $contribution, 'goal' => $savingsGoal->fresh()]);
    }

    // ─── LOANS ───

    public function loanIndex(Request $request): JsonResponse
    {
        $loans = Loan::where('family_id', $request->user()->family_id)
            ->with('user:id,name')
            ->when($request->type, fn($q, $t) => $q->where('type', $t))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->get();
        return response()->json(['data' => $loans]);
    }

    public function loanStore(Request $request): JsonResponse
    {
        $loan = Loan::create(array_merge(
            $request->only('name', 'type', 'lender_borrower_name', 'original_amount', 'monthly_payment', 'total_installments', 'interest_rate', 'start_date', 'end_date', 'due_day', 'account_id', 'notes'),
            ['family_id' => $request->user()->family_id, 'user_id' => $request->user()->id, 'remaining_amount' => $request->original_amount]
        ));
        return response()->json(['data' => $loan], 201);
    }

    public function loanPayment(Request $request, Loan $loan): JsonResponse
    {
        if ($loan->family_id !== $request->user()->family_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validator = Validator::make($request->all(), ['amount' => 'required|numeric|min:0.01']);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $payment = $loan->makePayment($request->amount, $request->user()->id, $request->account_id);
        return response()->json(['data' => $payment, 'loan' => $loan->fresh()]);
    }

    // ─── ALERTS ───

    public function alertIndex(Request $request): JsonResponse
    {
        $alerts = Alert::where('family_id', $request->user()->family_id)
            ->where('is_dismissed', false)
            ->orderByDesc('created_at')
            ->paginate(20);
        return response()->json($alerts);
    }

    public function alertRead(Request $request, Alert $alert): JsonResponse
    {
        if ($alert->family_id !== $request->user()->family_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        $alert->markAsRead();
        return response()->json(['message' => 'Alert marked as read']);
    }

    // ─── REPORTS ───

    public function reportSummary(Request $request): JsonResponse
    {
        $familyId = $request->user()->family_id;
        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to = $request->to ?? now()->toDateString();

        return response()->json([
            'total_income' => (float) Transaction::where('family_id', $familyId)->where('type', 'income')->dateRange($from, $to)->sum('amount'),
            'total_expenses' => (float) Transaction::where('family_id', $familyId)->where('type', 'expense')->dateRange($from, $to)->sum('amount'),
            'transaction_count' => Transaction::where('family_id', $familyId)->dateRange($from, $to)->count(),
        ]);
    }

    public function reportCategoryBreakdown(Request $request): JsonResponse
    {
        $familyId = $request->user()->family_id;
        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to = $request->to ?? now()->toDateString();

        $breakdown = Transaction::where('family_id', $familyId)
            ->where('type', 'expense')
            ->dateRange($from, $to)
            ->whereNotNull('category_id')
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->with('category:id,name,color')
            ->orderByDesc('total')
            ->get();

        return response()->json(['data' => $breakdown]);
    }
}
