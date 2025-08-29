# Repo Info

- Name: xau (Laravel app)
- Stack: PHP (Laravel), Blade, Bootstrap
- Notable conventions:
  - Moving to Laravel-standard snake_case schema for new tables
  - Existing `currencies` model uses custom PK `CurrencyID` (legacy). New modules will use standard `id`.
- Database: MySQL (target), SQLite exists for local dev. Migrations added for accounts module.
- Modules present:
  - Auth, Dashboard, Settings (currencies, categories, roles), Accounts (new)
- Routes: see routes/web.php
- Notes:
  - Currency model maps to legacy columns (CurrencyID, CurrencyCode, ...)
  - Accounts module uses `accounts` (id,name,account_type,identifier,is_active,timestamps) and `account_balances` (account_id,currency_id,current_balance,timestamps)