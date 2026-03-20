<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Alert;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Family;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\SavingsGoal;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Family ───
        $family = Family::create([
            'name' => 'Al-Hassan Family',
            'currency' => 'USD',
            'currency_symbol' => '$',
            'timezone' => 'Asia/Baghdad',
            'locale' => 'en',
            'direction' => 'ltr',
        ]);

        // ─── Users ───
        $admin = User::create([
            'family_id' => $family->id,
            'name' => 'Ahmed Al-Hassan',
            'email' => 'ahmed@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'relation' => 'self',
            'phone' => '+964 770 123 4567',
            'is_active' => true,
            'email_notifications' => true,
        ]);

        $wife = User::create([
            'family_id' => $family->id,
            'name' => 'Sara Al-Hassan',
            'email' => 'sara@example.com',
            'password' => Hash::make('password'),
            'role' => 'member',
            'relation' => 'wife',
            'is_active' => true,
            'email_notifications' => true,
        ]);

        $son = User::create([
            'family_id' => $family->id,
            'name' => 'Omar Al-Hassan',
            'email' => 'omar@example.com',
            'password' => Hash::make('password'),
            'role' => 'member',
            'relation' => 'son',
            'date_of_birth' => '2005-03-15',
            'is_active' => true,
        ]);

        $daughter = User::create([
            'family_id' => $family->id,
            'name' => 'Fatima Al-Hassan',
            'email' => 'fatima@example.com',
            'password' => Hash::make('password'),
            'role' => 'viewer',
            'relation' => 'daughter',
            'date_of_birth' => '2008-07-22',
            'is_active' => true,
        ]);

        // ─── Accounts ───
        $houseAccount = Account::create([
            'family_id' => $family->id,
            'name' => 'House Account',
            'type' => 'cash',
            'balance' => 2500.00,
            'initial_balance' => 2500.00,
            'color' => '#3B82F6',
            'icon' => 'home',
            'low_balance_threshold' => 500,
        ]);

        $myAccount = Account::create([
            'family_id' => $family->id,
            'user_id' => $admin->id,
            'name' => 'Ahmed\'s Bank Account',
            'type' => 'bank',
            'balance' => 8750.00,
            'initial_balance' => 8750.00,
            'bank_name' => 'Al-Rasheed Bank',
            'account_number' => '****4521',
            'color' => '#10B981',
            'icon' => 'university',
            'low_balance_threshold' => 1000,
        ]);

        $wifeAccount = Account::create([
            'family_id' => $family->id,
            'user_id' => $wife->id,
            'name' => 'Sara\'s Account',
            'type' => 'bank',
            'balance' => 3200.00,
            'initial_balance' => 3200.00,
            'color' => '#F59E0B',
            'icon' => 'credit-card',
            'low_balance_threshold' => 300,
        ]);

        $savingsAccount = Account::create([
            'family_id' => $family->id,
            'name' => 'Family Savings',
            'type' => 'savings',
            'balance' => 15000.00,
            'initial_balance' => 15000.00,
            'color' => '#8B5CF6',
            'icon' => 'piggy-bank',
        ]);

        $childrenAccount = Account::create([
            'family_id' => $family->id,
            'name' => 'Children\'s Fund',
            'type' => 'savings',
            'balance' => 1200.00,
            'initial_balance' => 1200.00,
            'color' => '#EC4899',
            'icon' => 'child',
        ]);

        $rewardsAccount = Account::create([
            'family_id' => $family->id,
            'name' => 'Rewards Points',
            'type' => 'rewards',
            'balance' => 350.00,
            'initial_balance' => 350.00,
            'color' => '#F97316',
            'icon' => 'gift',
            'include_in_total' => false,
        ]);

        // ─── Categories ───
        $categories = [];
        $catData = [
            // Expense categories
            ['name' => 'Groceries', 'name_ar' => 'بقالة', 'type' => 'expense', 'icon' => 'shopping-cart', 'color' => '#22C55E'],
            ['name' => 'Rent & Housing', 'name_ar' => 'الإيجار والسكن', 'type' => 'expense', 'icon' => 'home', 'color' => '#3B82F6'],
            ['name' => 'Utilities', 'name_ar' => 'فواتير الخدمات', 'type' => 'expense', 'icon' => 'bolt', 'color' => '#F59E0B'],
            ['name' => 'Transportation', 'name_ar' => 'المواصلات', 'type' => 'expense', 'icon' => 'car', 'color' => '#8B5CF6'],
            ['name' => 'Healthcare', 'name_ar' => 'الرعاية الصحية', 'type' => 'expense', 'icon' => 'heartbeat', 'color' => '#EF4444'],
            ['name' => 'Education', 'name_ar' => 'التعليم', 'type' => 'expense', 'icon' => 'graduation-cap', 'color' => '#6366F1'],
            ['name' => 'Entertainment', 'name_ar' => 'الترفيه', 'type' => 'expense', 'icon' => 'film', 'color' => '#EC4899'],
            ['name' => 'Dining Out', 'name_ar' => 'المطاعم', 'type' => 'expense', 'icon' => 'utensils', 'color' => '#F97316'],
            ['name' => 'Shopping', 'name_ar' => 'التسوق', 'type' => 'expense', 'icon' => 'shopping-bag', 'color' => '#14B8A6'],
            ['name' => 'Personal Care', 'name_ar' => 'العناية الشخصية', 'type' => 'expense', 'icon' => 'spa', 'color' => '#A855F7'],
            ['name' => 'Insurance', 'name_ar' => 'التأمين', 'type' => 'expense', 'icon' => 'shield-alt', 'color' => '#64748B'],
            ['name' => 'Charity & Gifts', 'name_ar' => 'الصدقات والهدايا', 'type' => 'expense', 'icon' => 'hand-holding-heart', 'color' => '#D946EF'],
            // Income categories
            ['name' => 'Salary', 'name_ar' => 'الراتب', 'type' => 'income', 'icon' => 'money-bill-wave', 'color' => '#22C55E'],
            ['name' => 'Freelance', 'name_ar' => 'عمل حر', 'type' => 'income', 'icon' => 'laptop', 'color' => '#3B82F6'],
            ['name' => 'Investments', 'name_ar' => 'الاستثمارات', 'type' => 'income', 'icon' => 'chart-line', 'color' => '#8B5CF6'],
            ['name' => 'Bonus', 'name_ar' => 'مكافأة', 'type' => 'income', 'icon' => 'star', 'color' => '#F59E0B'],
            ['name' => 'Other Income', 'name_ar' => 'دخل آخر', 'type' => 'income', 'icon' => 'coins', 'color' => '#64748B'],
        ];

        foreach ($catData as $cat) {
            $categories[$cat['name']] = Category::create(array_merge($cat, ['family_id' => $family->id]));
        }

        // Subcategories
        $subCats = [
            ['name' => 'Electricity', 'name_ar' => 'كهرباء', 'parent' => 'Utilities', 'type' => 'expense', 'icon' => 'bolt', 'color' => '#F59E0B'],
            ['name' => 'Water', 'name_ar' => 'ماء', 'parent' => 'Utilities', 'type' => 'expense', 'icon' => 'tint', 'color' => '#3B82F6'],
            ['name' => 'Internet', 'name_ar' => 'إنترنت', 'parent' => 'Utilities', 'type' => 'expense', 'icon' => 'wifi', 'color' => '#6366F1'],
            ['name' => 'Phone Bill', 'name_ar' => 'فاتورة الهاتف', 'parent' => 'Utilities', 'type' => 'expense', 'icon' => 'phone', 'color' => '#10B981'],
            ['name' => 'Fuel', 'name_ar' => 'وقود', 'parent' => 'Transportation', 'type' => 'expense', 'icon' => 'gas-pump', 'color' => '#8B5CF6'],
            ['name' => 'Car Maintenance', 'name_ar' => 'صيانة السيارة', 'parent' => 'Transportation', 'type' => 'expense', 'icon' => 'wrench', 'color' => '#64748B'],
            ['name' => 'School Fees', 'name_ar' => 'رسوم مدرسية', 'parent' => 'Education', 'type' => 'expense', 'icon' => 'school', 'color' => '#6366F1'],
            ['name' => 'Books & Supplies', 'name_ar' => 'كتب ومستلزمات', 'parent' => 'Education', 'type' => 'expense', 'icon' => 'book', 'color' => '#A855F7'],
        ];

        foreach ($subCats as $sc) {
            Category::create([
                'family_id' => $family->id,
                'parent_id' => $categories[$sc['parent']]->id,
                'name' => $sc['name'],
                'name_ar' => $sc['name_ar'],
                'type' => $sc['type'],
                'icon' => $sc['icon'],
                'color' => $sc['color'],
            ]);
        }

        // ─── Transactions (last 3 months) ───
        $expenseData = [
            ['desc' => 'Monthly Rent', 'amount' => 800, 'cat' => 'Rent & Housing', 'acc' => $houseAccount, 'user' => $admin, 'method' => 'bank_transfer'],
            ['desc' => 'Carrefour Groceries', 'amount' => 245.50, 'cat' => 'Groceries', 'acc' => $houseAccount, 'user' => $wife, 'method' => 'card'],
            ['desc' => 'Electricity Bill', 'amount' => 120, 'cat' => 'Utilities', 'acc' => $houseAccount, 'user' => $admin, 'method' => 'online'],
            ['desc' => 'Water Bill', 'amount' => 35, 'cat' => 'Utilities', 'acc' => $houseAccount, 'user' => $admin, 'method' => 'online'],
            ['desc' => 'Internet Subscription', 'amount' => 45, 'cat' => 'Utilities', 'acc' => $myAccount, 'user' => $admin, 'method' => 'online'],
            ['desc' => 'Phone Bill - Ahmed', 'amount' => 30, 'cat' => 'Utilities', 'acc' => $myAccount, 'user' => $admin, 'method' => 'online'],
            ['desc' => 'Phone Bill - Sara', 'amount' => 25, 'cat' => 'Utilities', 'acc' => $wifeAccount, 'user' => $wife, 'method' => 'online'],
            ['desc' => 'Fuel', 'amount' => 85, 'cat' => 'Transportation', 'acc' => $myAccount, 'user' => $admin, 'method' => 'card'],
            ['desc' => 'Kids School Fees', 'amount' => 450, 'cat' => 'Education', 'acc' => $myAccount, 'user' => $admin, 'method' => 'bank_transfer'],
            ['desc' => 'Family Dinner at Restaurant', 'amount' => 75, 'cat' => 'Dining Out', 'acc' => $houseAccount, 'user' => $admin, 'method' => 'card'],
            ['desc' => 'Doctor Visit - Sara', 'amount' => 60, 'cat' => 'Healthcare', 'acc' => $wifeAccount, 'user' => $wife, 'method' => 'cash'],
            ['desc' => 'Movie Tickets', 'amount' => 32, 'cat' => 'Entertainment', 'acc' => $houseAccount, 'user' => $son, 'method' => 'cash'],
            ['desc' => 'Clothing - Kids', 'amount' => 120, 'cat' => 'Shopping', 'acc' => $wifeAccount, 'user' => $wife, 'method' => 'card'],
            ['desc' => 'Car Insurance', 'amount' => 150, 'cat' => 'Insurance', 'acc' => $myAccount, 'user' => $admin, 'method' => 'bank_transfer'],
            ['desc' => 'Mosque Donation', 'amount' => 50, 'cat' => 'Charity & Gifts', 'acc' => $myAccount, 'user' => $admin, 'method' => 'cash'],
            ['desc' => 'Weekly Groceries', 'amount' => 180, 'cat' => 'Groceries', 'acc' => $houseAccount, 'user' => $wife, 'method' => 'card'],
            ['desc' => 'School Books', 'amount' => 65, 'cat' => 'Education', 'acc' => $myAccount, 'user' => $admin, 'method' => 'cash'],
            ['desc' => 'Hair Salon - Sara', 'amount' => 40, 'cat' => 'Personal Care', 'acc' => $wifeAccount, 'user' => $wife, 'method' => 'cash'],
            ['desc' => 'Car Maintenance', 'amount' => 200, 'cat' => 'Transportation', 'acc' => $myAccount, 'user' => $admin, 'method' => 'cash'],
            ['desc' => 'Birthday Gift - Omar', 'amount' => 100, 'cat' => 'Charity & Gifts', 'acc' => $houseAccount, 'user' => $wife, 'method' => 'cash'],
        ];

        // Create 3 months of transactions
        for ($m = 2; $m >= 0; $m--) {
            $month = now()->subMonths($m);

            // Expenses
            foreach ($expenseData as $i => $exp) {
                $day = min($i + 1, 28);
                Transaction::create([
                    'family_id' => $family->id,
                    'user_id' => $exp['user']->id,
                    'account_id' => $exp['acc']->id,
                    'category_id' => $categories[$exp['cat']]->id,
                    'type' => 'expense',
                    'amount' => $exp['amount'] + ($m === 0 ? 0 : rand(-20, 20)),
                    'description' => $exp['desc'],
                    'transaction_date' => $month->copy()->day($day)->format('Y-m-d'),
                    'payment_method' => $exp['method'],
                ]);
            }

            // Income
            Transaction::create([
                'family_id' => $family->id,
                'user_id' => $admin->id,
                'account_id' => $myAccount->id,
                'category_id' => $categories['Salary']->id,
                'type' => 'income',
                'amount' => 5000,
                'description' => 'Monthly Salary - Ahmed',
                'transaction_date' => $month->copy()->day(1)->format('Y-m-d'),
                'payment_method' => 'bank_transfer',
            ]);

            Transaction::create([
                'family_id' => $family->id,
                'user_id' => $wife->id,
                'account_id' => $wifeAccount->id,
                'category_id' => $categories['Salary']->id,
                'type' => 'income',
                'amount' => 2000,
                'description' => 'Monthly Salary - Sara',
                'transaction_date' => $month->copy()->day(1)->format('Y-m-d'),
                'payment_method' => 'bank_transfer',
            ]);

            if ($m === 1) {
                Transaction::create([
                    'family_id' => $family->id,
                    'user_id' => $admin->id,
                    'account_id' => $myAccount->id,
                    'category_id' => $categories['Freelance']->id,
                    'type' => 'income',
                    'amount' => 800,
                    'description' => 'Website Development Project',
                    'transaction_date' => $month->copy()->day(15)->format('Y-m-d'),
                    'payment_method' => 'bank_transfer',
                ]);
            }
        }

        // ─── Budgets ───
        $now = now();
        Budget::create([
            'family_id' => $family->id, 'category_id' => $categories['Groceries']->id,
            'name' => 'Monthly Groceries', 'amount' => 500, 'spent' => 425.50,
            'period' => 'monthly', 'start_date' => $now->copy()->startOfMonth(), 'end_date' => $now->copy()->endOfMonth(),
            'alert_threshold' => 80,
        ]);
        Budget::create([
            'family_id' => $family->id, 'category_id' => $categories['Dining Out']->id,
            'name' => 'Dining Out Budget', 'amount' => 150, 'spent' => 75,
            'period' => 'monthly', 'start_date' => $now->copy()->startOfMonth(), 'end_date' => $now->copy()->endOfMonth(),
        ]);
        Budget::create([
            'family_id' => $family->id, 'category_id' => $categories['Entertainment']->id,
            'name' => 'Entertainment', 'amount' => 100, 'spent' => 95,
            'period' => 'monthly', 'start_date' => $now->copy()->startOfMonth(), 'end_date' => $now->copy()->endOfMonth(),
            'alert_threshold' => 80,
        ]);
        Budget::create([
            'family_id' => $family->id, 'name' => 'Overall Monthly Budget', 'amount' => 4000, 'spent' => 2907.50,
            'period' => 'monthly', 'start_date' => $now->copy()->startOfMonth(), 'end_date' => $now->copy()->endOfMonth(),
        ]);

        // ─── Savings Goals ───
        SavingsGoal::create([
            'family_id' => $family->id, 'account_id' => $savingsAccount->id,
            'name' => 'Family Vacation to Turkey', 'target_amount' => 5000, 'current_amount' => 2800,
            'target_date' => now()->addMonths(6), 'icon' => 'plane', 'color' => '#3B82F6', 'priority' => 'high',
        ]);
        SavingsGoal::create([
            'family_id' => $family->id, 'account_id' => $savingsAccount->id,
            'name' => 'New Laptop for Omar', 'target_amount' => 1200, 'current_amount' => 450,
            'target_date' => now()->addMonths(3), 'icon' => 'laptop', 'color' => '#8B5CF6', 'priority' => 'medium',
        ]);
        SavingsGoal::create([
            'family_id' => $family->id,
            'name' => 'Emergency Fund', 'target_amount' => 10000, 'current_amount' => 6500,
            'icon' => 'shield-alt', 'color' => '#EF4444', 'priority' => 'high',
        ]);
        SavingsGoal::create([
            'family_id' => $family->id,
            'name' => 'Home Renovation', 'target_amount' => 8000, 'current_amount' => 1200,
            'target_date' => now()->addYear(), 'icon' => 'paint-roller', 'color' => '#F59E0B', 'priority' => 'low',
        ]);

        // ─── Loans ───
        $carLoan = Loan::create([
            'family_id' => $family->id, 'user_id' => $admin->id, 'account_id' => $myAccount->id,
            'name' => 'Car Loan', 'type' => 'installment', 'lender_borrower_name' => 'Al-Rasheed Bank',
            'original_amount' => 18000, 'remaining_amount' => 12000,
            'interest_rate' => 5.5, 'monthly_payment' => 500, 'total_installments' => 36, 'paid_installments' => 12,
            'start_date' => now()->subYear(), 'end_date' => now()->addYears(2), 'due_day' => 5,
        ]);

        Loan::create([
            'family_id' => $family->id, 'user_id' => $admin->id,
            'name' => 'Salary Advance', 'type' => 'salary_advance', 'lender_borrower_name' => 'Company HR',
            'original_amount' => 2000, 'remaining_amount' => 1000,
            'monthly_payment' => 500, 'total_installments' => 4, 'paid_installments' => 2,
            'start_date' => now()->subMonths(2), 'due_day' => 1,
        ]);

        Loan::create([
            'family_id' => $family->id, 'user_id' => $admin->id,
            'name' => 'Money Lent to Friend', 'type' => 'lent', 'lender_borrower_name' => 'Ali Mohammed',
            'original_amount' => 500, 'remaining_amount' => 500,
            'monthly_payment' => 0, 'total_installments' => 1, 'paid_installments' => 0,
            'start_date' => now()->subMonth(),
        ]);

        // Loan payments for car loan
        for ($i = 1; $i <= 12; $i++) {
            LoanPayment::create([
                'loan_id' => $carLoan->id, 'user_id' => $admin->id, 'account_id' => $myAccount->id,
                'amount' => 500, 'principal' => 458, 'interest' => 42,
                'payment_date' => now()->subYear()->addMonths($i)->day(5)->format('Y-m-d'),
                'due_date' => now()->subYear()->addMonths($i)->day(5)->format('Y-m-d'),
                'installment_number' => $i, 'status' => 'paid',
            ]);
        }

        // ─── Alerts ───
        Alert::create([
            'family_id' => $family->id, 'user_id' => $admin->id,
            'type' => 'over_budget', 'title' => 'Entertainment Budget Alert',
            'message' => 'Entertainment budget is at 95% - only $5 remaining this month.',
            'severity' => 'warning', 'icon' => 'exclamation-triangle',
            'alertable_type' => Budget::class, 'alertable_id' => 3,
            'action_url' => '/budgets',
        ]);
        Alert::create([
            'family_id' => $family->id,
            'type' => 'loan_due', 'title' => 'Car Loan Payment Due',
            'message' => 'Car loan payment of $500 is due on the 5th.',
            'severity' => 'info', 'icon' => 'clock',
            'alertable_type' => Loan::class, 'alertable_id' => $carLoan->id,
            'action_url' => '/loans',
        ]);
        Alert::create([
            'family_id' => $family->id,
            'type' => 'goal_milestone', 'title' => 'Emergency Fund at 65%',
            'message' => 'Great progress! Emergency fund has reached 65% of the target.',
            'severity' => 'success', 'icon' => 'trophy',
            'alertable_type' => SavingsGoal::class, 'alertable_id' => 3,
            'action_url' => '/savings-goals',
        ]);

        echo "✅ Demo data seeded successfully!\n";
        echo "   Login: ahmed@example.com / password\n";
        echo "   Family: Al-Hassan Family\n";
        echo "   Accounts: 6 | Categories: 25 | Transactions: 66+\n";
        echo "   Budgets: 4 | Savings Goals: 4 | Loans: 3\n";
    }
}
