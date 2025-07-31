<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\CategoryManager;
use App\Livewire\CustomerManager;
use App\Livewire\CustomerShow;
use App\Livewire\CustomerEdit;
use App\Livewire\ProductManager;
use App\Livewire\UnitManager;
use App\Livewire\SupplierManager;
use App\Livewire\ProductCreate;
use App\Livewire\ProductEdit;
use App\Livewire\ProductShow;
use App\Livewire\PurchaseCreate;
use App\Livewire\PurchaseManager;
use App\Livewire\PurchaseShow;
use App\Livewire\PurchaseEdit;
use App\Livewire\TransactionManager;
use App\Livewire\TransactionCreate;
use App\Livewire\TransactionShow;
use App\Livewire\TransactionEdit;
use App\Livewire\PointOfSale;
use App\Livewire\AccountsReceivable;
use App\Livewire\InvoiceCreate;
use App\Livewire\SalesReport;
use App\Livewire\UserManager;
use App\Livewire\ExpiringStockReport;
use App\Livewire\LowStockReport;
use App\Livewire\StockOpname;
use App\Livewire\StockCard;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/', ProductManager::class)->name('home');
    Route::view('dashboard', 'dashboard')
        ->middleware(['verified', 'can:view-dashboard'])
        ->name('dashboard');

    Route::middleware(['can:manage-settings'])->group(function () {
        Route::redirect('settings', 'settings/profile');

        Route::get('settings/profile', Profile::class)->name('settings.profile');
        Route::get('settings/password', Password::class)->name('settings.password');
        Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
    });

    // Data Management Modules
    Route::get('/categories', CategoryManager::class)->name('categories.index');
    Route::get('/units', UnitManager::class)->name('units.index');
    Route::get('/suppliers', SupplierManager::class)->name('suppliers.index');
    Route::get('/customers', CustomerManager::class)->name('customers.index');
    Route::get('/customers/{customer}', CustomerShow::class)->name('customers.show');
    Route::get('/customers/{customer}/edit', CustomerEdit::class)->name('customers.edit');

    // Product Modules
    Route::get('/products', ProductManager::class)->name('products.index');
    Route::get('/products/create', ProductCreate::class)->name('products.create');
    Route::get('/products/{product}', ProductShow::class)->name('products.show');
    Route::middleware(['can:manage-products'])->group(function () {
        Route::get('/products/{product}/edit', ProductEdit::class)->name('products.edit');
    });

    // Purchase Modules
    Route::middleware(['can:manage-purchases'])->group(function () {
        Route::get('/purchases/create', PurchaseCreate::class)->name('purchases.create');
        Route::get('/purchases', PurchaseManager::class)->name('purchases.index');
        Route::get('/purchases/{purchase}', PurchaseShow::class)->name('purchases.show');
        Route::get('/purchases/{purchase}/edit', PurchaseEdit::class)->name('purchases.edit');
    });

    // Transaction Modules
    Route::middleware(['can:manage-sales'])->group(function () {
        Route::get('/transactions', TransactionManager::class)->name('transactions.index');
        Route::get('/transactions/create', TransactionCreate::class)->name('transactions.create');
        Route::get('/transactions/{transaction}', TransactionShow::class)->name('transactions.show');
        
        // Point of Sale Module
        Route::get('/pos', PointOfSale::class)->name('pos.index');
        
        // Accounts Receivable Module
        Route::get('/accounts-receivable', AccountsReceivable::class)->name('accounts-receivable.index');
        
        Route::get('/invoices/create', InvoiceCreate::class)->name('invoices.create');

        // Document Printing Routes
        Route::get('/transactions/{transaction}/receipt', [App\Http\Controllers\DocumentController::class, 'printReceipt'])->name('transactions.print-receipt');
        Route::get('/transactions/{transaction}/invoice', [App\Http\Controllers\DocumentController::class, 'printInvoice'])->name('transactions.print-invoice');
    });
    
    Route::middleware(['can:delete-sales'])->group(function(){
        Route::get('/transactions/{transaction}/edit', TransactionEdit::class)->name('transactions.edit');
    });

    // Reporting Modules
    Route::middleware(['can:view-reports'])->group(function () {
        Route::get('/reports/sales', SalesReport::class)->name('reports.sales');
        Route::get('/reports/expiring-stock', ExpiringStockReport::class)->name('reports.expiring-stock');
        Route::get('/reports/low-stock', LowStockReport::class)->name('reports.low-stock');
        Route::get('/reports/expiring-stock/print', [App\Http\Controllers\DocumentController::class, 'printExpiringStockReport'])->name('reports.expiring-stock.print');
        Route::get('/reports/stock-card/print', [App\Http\Controllers\DocumentController::class, 'printStockCard'])->name('reports.stock-card.print'); // Added
    });

    // User Management Module
    Route::middleware(['can:manage-users'])->group(function () {
        Route::get('/users', UserManager::class)->name('users.index');
    });

    // Stock Opname Module
    Route::middleware(['can:manage-products'])->group(function () { // Assuming manage-products permission
        Route::get('/stock-opname', StockOpname::class)->name('stock-opname.index');
    });

    // Stock Card Module
    Route::middleware(['can:view-reports'])->group(function () { // Assuming view-reports permission
        Route::get('/stock-card', StockCard::class)->name('stock-card.index');
    });

    // cetak
    Route::get('/print/receipt/{transactionId}', [App\Http\Controllers\DocumentController::class, 'printReceipt'])->name('print.receipt');
    Route::get('/print/invoice/{transactionId}', [App\Http\Controllers\DocumentController::class, 'printInvoice'])->name('print.invoice');
});

require __DIR__.'/auth.php';