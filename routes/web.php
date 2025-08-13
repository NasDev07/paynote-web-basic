<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\ExpensesController;
use App\Http\Controllers\IncomesController;

// Home Route
Route::view('/', 'home.index')->name('home');

// Auth Routes
Route::group(['middleware' => 'web'], function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

// Kategori
Route::group(['middleware' => ['auth']], function () {
    Route::get('categories', [CategoriesController::class, 'index'])->name('categories');
    Route::get('categories/add', [CategoriesController::class, 'addPage'])->name('categories.addPage');
    Route::post('categories/insert', [CategoriesController::class, 'insert'])->name('categories.insert');
    Route::get('categories/edit/{id}', [CategoriesController::class, 'editPage'])->name('categories.editPage');
    Route::put('categories/update/{id}', [CategoriesController::class, 'update'])->name('categories.update');
    Route::get('categories/delete/{id}', [CategoriesController::class, 'delete'])->name('categories.delete');
});

// Incomes
Route::middleware(['auth'])->group(function () {
    Route::get('incomes', [IncomesController::class, 'index'])->name('incomes');
    Route::get('incomes/add', [IncomesController::class, 'addPage'])->name('incomes.addPage');
    Route::post('incomes/insert', [IncomesController::class, 'insert'])->name('incomes.insert');
    Route::get('incomes/edit/{id}', [IncomesController::class, 'editPage'])->name('incomes.editPage');
    Route::put('incomes/update/{id}', [IncomesController::class, 'update'])->name('incomes.update');
    Route::get('incomes/delete/{id}', [IncomesController::class, 'delete'])->name('incomes.delete');
});

// Expenses
Route::middleware(['auth'])->group(function () {
    Route::get('expenses', [ExpensesController::class, 'index'])->name('expenses');
    Route::get('expenses/add', [ExpensesController::class, 'create'])->name('expenses.addPage');
    Route::post('expenses/insert', [ExpensesController::class, 'store'])->name('expenses.insert');
    Route::get('expenses/show/{id}', [ExpensesController::class, 'show'])->name('expenses.show');
    Route::get('expenses/edit/{id}', [ExpensesController::class, 'edit'])->name('expenses.editPage');
    Route::put('expenses/update/{id}', [ExpensesController::class, 'update'])->name('expenses.update');
    Route::delete('expenses/delete/{id}', [ExpensesController::class, 'destroy'])->name('expenses.delete');
    Route::get('expenses/{id}/download-receipt', [ExpensesController::class, 'downloadReceipt'])->name('expenses.download-receipt');
    Route::get('expenses/api/data', [ExpensesController::class, 'apiData'])->name('expenses.api.data');
});

// Dashboard Route
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Auth Routes
Auth::routes();
