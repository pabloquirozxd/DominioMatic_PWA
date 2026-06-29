<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\ReportController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});
// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::middleware(['auth', 'verified'])->group(function () {
    // Rutas para el directorio inteligente de contactos

    // Contactos
    Route::get('/contacts', [ContactController::class, 'index'])->name('contacts.index');
    Route::post('/contacts', [ContactController::class, 'store'])->name('contacts.store');
    Route::put('/contacts/{contact}', [ContactController::class, 'update'])->name('contacts.update');
    Route::delete('/contacts/{contact}', [ContactController::class, 'destroy'])->name('contacts.destroy');

    // Productos
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

    // Suscripciones

    Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::post('/subscriptions', [SubscriptionController::class, 'store'])->name('subscriptions.store');
    Route::put('/subscriptions/{subscription}', [SubscriptionController::class, 'update'])->name('subscriptions.update');
    Route::delete('/subscriptions/{subscription}', [SubscriptionController::class, 'destroy'])->name('subscriptions.destroy');

    // Reportes - Contactos
    Route::get('/reports/contacts/pdf', [ReportController::class, 'contactsPdf'])
        ->name('reports.contacts.pdf');

    Route::get('/reports/contacts/excel', [ReportController::class, 'contactsExcel'])
        ->name('reports.contacts.excel');

    // Reportes - Productos
    Route::get('/reports/products/pdf', [ReportController::class, 'productsPdf'])
        ->name('reports.products.pdf');

    Route::get('/reports/products/excel', [ReportController::class, 'productsExcel'])
        ->name('reports.products.excel');

    // Reportes - Suscripciones
    Route::get('/reports/subscriptions/pdf', [ReportController::class, 'subscriptionsPdf'])
        ->name('reports.subscriptions.pdf');

    Route::get('/reports/subscriptions/excel', [ReportController::class, 'subscriptionsExcel'])
        ->name('reports.subscriptions.excel');
});



// Ruta temporal para probar el diseño
Route::get('/test-apple', function () {
    return Inertia::render('Welcome');
});