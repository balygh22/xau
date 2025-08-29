# Task: Cleanup legacy columns from `currencies`

Priority: Medium
Status: TODO
Owner: Backend

Summary:
- Remove legacy columns after ensuring all FKs reference `currencies.id` and code uses standard columns.

Legacy columns to remove (verify before action):
- CurrencyID (legacy PK)
- CurrencyCode
- CurrencyName
- IsDefault

Prerequisites:
- Identify and migrate external FKs such as `exchangerates.CurrencyID` to reference `currencies.id`.
- Update code paths still relying on legacy columns.

Steps:
1) Inventory FKs pointing to `currencies` via information_schema.
2) Create migrations to add `currency_id` columns and backfill where needed.
3) Switch FKs to `currencies.id`.
4) Drop legacy columns from `currencies`.
5) Add proper PK/indices if needed; ensure `id` is AUTO_INCREMENT.

Rollback:
- Keep backups/exports before structural changes.

Notes:
- Coordinate with reporting modules and any triggers/views if exist.