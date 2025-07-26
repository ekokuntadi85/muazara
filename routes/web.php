<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\CategoryManager;
use App\Livewire\CustomerManager;
use App\Livewire\ProductManager;
use App\Livewire\UnitManager;
use App\Livewire\SupplierManager;
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

require __DIR__.'/auth.php';
