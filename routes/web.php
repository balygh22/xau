<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ReportController;

Route::get('/', fn () => redirect()->route('dashboard'));

// Auth routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes (only what appears in the UI)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

    // Main sections
    // Transactions


Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');

Route::get('/transactions/create/sale', [TransactionController::class, 'createSale'])->name('transactions.create.sale');
Route::get('/transactions/create/purchase', [TransactionController::class, 'createPurchase'])->name('transactions.create.purchase');

Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
Route::delete('/transactions/{id}', [TransactionController::class, 'destroy'])->name('transactions.destroy');

Route::get('/transactions/{id}', [TransactionController::class, 'show'])->name('transactions.show');
// Returns
Route::get('/transactions/{id}/return', [TransactionController::class, 'createReturn'])->name('transactions.return.create');
Route::post('/transactions/{id}/return', [TransactionController::class, 'storeReturn'])->name('transactions.return.store');
// Route::get('/transactions/{id}/print', [TransactionController::class, 'print'])->name('transactions.print'); // لاحقاً


    // Payments
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/receipt/create', [PaymentController::class, 'createReceipt'])->name('payments.receipt.create');
    Route::get('/payments/disbursement/create', [PaymentController::class, 'createDisbursement'])->name('payments.disbursement.create');
    Route::get('/payments/transfer/create', [PaymentController::class, 'createTransfer'])->name('payments.transfer.create');
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::delete('/payments/{id}', [PaymentController::class, 'destroy'])->name('payments.destroy');

    // Inventory -> Products
    Route::get('/inventory', [\App\Http\Controllers\InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/create', [\App\Http\Controllers\InventoryController::class, 'create'])->name('inventory.create');
    Route::post('/inventory', [\App\Http\Controllers\InventoryController::class, 'store'])->name('inventory.store');
    Route::get('/inventory/{product}', [\App\Http\Controllers\InventoryController::class, 'show'])->name('inventory.show');
    Route::get('/inventory/{product}/edit', [\App\Http\Controllers\InventoryController::class, 'edit'])->name('inventory.edit');
    Route::put('/inventory/{product}', [\App\Http\Controllers\InventoryController::class, 'update'])->name('inventory.update');
    Route::delete('/inventory/{product}', [\App\Http\Controllers\InventoryController::class, 'destroy'])->name('inventory.destroy');

    // Accounts (resource-like minimal for now)
    Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
    Route::get('/accounts/create', [AccountController::class, 'create'])->name('accounts.create');
    Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.store');
    Route::post('/accounts/{account}/activate', [AccountController::class, 'activate'])->name('accounts.activate');
    Route::post('/accounts/{account}/deactivate', [AccountController::class, 'deactivate'])->name('accounts.deactivate');
    Route::get('/accounts/{account}/statement', [AccountController::class, 'statement'])->name('accounts.statement');

    // Reports
    Route::get('/reports', fn () => view('reports.index'))->name('reports.index');
    Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
    Route::get('/reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');

    // Settings
    Route::get('/settings', fn () => view('settings'))->name('settings');

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::resource('currencies', CurrencyController::class);
        Route::resource('categories', CategoryController::class);
        Route::resource('roles', RoleController::class)->only(['index','create','store','edit','update']);
        Route::get('users', fn () => view('settings.users'))->name('users');
        Route::post('users', [\App\Http\Controllers\UserManagementController::class, 'store'])->name('users.store');

    });
});
