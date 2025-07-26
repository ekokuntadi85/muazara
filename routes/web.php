<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\CategoryManager;
use App\Livewire\CustomerManager;
use App\Livewire\ProductManager;
use App\Livewire\UnitManager;
use App\Livewire\SupplierManager;
use App\Livewire\ProductCreate;
use App\Livewire\ProductEdit;
use App\Livewire\PurchaseCreate;
use App\Livewire\PurchaseManager;
use App\Livewire\PurchaseShow;
use App\Livewire\PurchaseEdit;
use App\Livewire\TransactionManager;
use App\Livewire\TransactionCreate;
use App\Livewire\TransactionShow;
use App\Livewire\TransactionEdit;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
});

Route::get('/categories', CategoryManager::class)->name('categories.index');

Route::get('/units', UnitManager::class)->name('units.index');

Route::get('/suppliers', SupplierManager::class)->name('suppliers.index');

Route::get('/customers', CustomerManager::class)->name('customers.index');

Route::get('/products', ProductManager::class)->name('products.index');
Route::get('/products/create', ProductCreate::class)->name('products.create');
Route::get('/products/{product}/edit', ProductEdit::class)->name('products.edit');

Route::get('/purchases/create', PurchaseCreate::class)->name('purchases.create');

Route::get('/purchases', PurchaseManager::class)->name('purchases.index');
Route::get('/purchases/{purchase}', PurchaseShow::class)->name('purchases.show');
Route::get('/purchases/{purchase}/edit', PurchaseEdit::class)->name('purchases.edit');

Route::get('/transactions', TransactionManager::class)->name('transactions.index');
Route::get('/transactions/create', TransactionCreate::class)->name('transactions.create');
Route::get('/transactions/{transaction}', TransactionShow::class)->name('transactions.show');
Route::get('/transactions/{transaction}/edit', TransactionEdit::class)->name('transactions.edit');

require __DIR__.'/auth.php';
