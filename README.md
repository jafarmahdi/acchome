# AccHome

AccHome is a Laravel-based household finance system for families who want to track daily money movement in a practical way.

It focuses on:

- expenses and income
- account balances
- transfers between accounts
- budgets
- savings goals
- loans and installments
- alerts and reminders
- Arabic support with RTL layout
- IQD/USD-friendly workflows

## Tech Stack

- PHP 8.0+
- Laravel 9
- MySQL
- Blade
- Tailwind via CDN
- Alpine.js
- Chart.js

## Main Features

- Family-based finance tracking
- Separate accounts with balance control
- Daily expense and income recording
- Transfer flow between accounts with insufficient-balance protection
- Budget monitoring and strong alerts
- Savings goals with contributions from selected accounts
- Loan and installment tracking with receipt uploads
- Reports dashboard
- Audit logs
- Arabic localization and RTL support

## Local Setup

1. Clone the repository.
2. Install PHP dependencies:

```bash
composer install
```

3. Copy the environment file:

```bash
cp .env.example .env
```

4. Generate the app key:

```bash
php artisan key:generate
```

5. Update your database settings in `.env`.

6. Run migrations:

```bash
php artisan migrate
```

7. Start the app:

```bash
php artisan serve
```

If you are using XAMPP, you can also serve it directly from your local Apache setup and open:

```text
http://localhost/acchome
```

## Default Environment Notes

The example environment is prepared with:

- database: `acchome`
- timezone: `Asia/Baghdad`
- app URL: `http://localhost/acchome`

## Important Notes

- `.env` is ignored and is not committed.
- `vendor/` is ignored and is not committed.
- Temporary generated files inside storage are excluded from the repository.

## Project Status

This project is actively being shaped around real household usage, especially for Iraqi family finance workflows.

## License

MIT
