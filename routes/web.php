<?php

use App\Http\Controllers\AccessRequestController;
use App\Http\Controllers\Admin\AccessRequestController as AdminAccessRequestController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientImportController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Rutas Públicas (Sin autenticación)
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => false, // Registro público cerrado por arquitectura B2B
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Solicitud de Acceso B2B - Flujo con Buscador Inteligente
Route::prefix('join')->group(function () {
    // 1. Buscador inicial de empresa
    Route::get('/', [AccessRequestController::class, 'lookup'])->name('join.lookup');
    Route::post('/find', [AccessRequestController::class, 'find'])->name('join.find');

    // 2. Formulario y confirmación de la empresa encontrada
    Route::get('/{slug}', [AccessRequestController::class, 'create'])->name('join.create');
    Route::post('/{slug}', [AccessRequestController::class, 'store'])
        ->middleware('throttle:5,30')
        ->name('join.store');
    Route::get('/{slug}/submitted/{requestId}', [AccessRequestController::class, 'submitted'])->name('join.submitted');
});

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Rutas Autenticadas y Protegidas por Estado de Empresa
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'company.active'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Módulo Central de Administración B2B
    Route::prefix('admin')->name('admin.')->group(function () {
        // Vista Principal
        Route::get('/', [AdminController::class, 'index'])->name('index');

        // Gestión de Usuarios y Roles
        Route::post('/users/invite', [AdminController::class, 'invite'])->name('users.invite');
        Route::patch('/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.update-role');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

        // Procesamiento de Solicitudes de Acceso
        Route::get('/access-requests', [AdminAccessRequestController::class, 'index'])->name('access-requests.index');
        Route::post('/access-requests/{uuid}/approve', [AdminAccessRequestController::class, 'approve'])->name('access-requests.approve');
        Route::post('/access-requests/{uuid}/reject', [AdminAccessRequestController::class, 'reject'])->name('access-requests.reject');
    });

    // Contactos
    Route::get('/contacts/search', [ClientController::class, 'searchContacts'])->name('contacts.search');
    Route::get('/contacts', [ContactController::class, 'index'])->name('contacts.index');
    Route::post('/contacts', [ContactController::class, 'store'])->name('contacts.store');
    Route::put('/contacts/{contact}', [ContactController::class, 'update'])->name('contacts.update');
    Route::delete('/contacts/{contact}', [ContactController::class, 'destroy'])->name('contacts.destroy');
    Route::post('/contacts/import', [ContactController::class, 'import'])->name('contacts.import');

    // Productos
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::post('/products/import', [ProductController::class, 'import'])->name('products.import');

    // Suscripciones
    Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::post('/subscriptions', [SubscriptionController::class, 'store'])->name('subscriptions.store');
    Route::put('/subscriptions/{subscription}', [SubscriptionController::class, 'update'])->name('subscriptions.update');
    Route::delete('/subscriptions/{subscription}', [SubscriptionController::class, 'destroy'])->name('subscriptions.destroy');
    Route::get('/subscriptions/import/template', [SubscriptionController::class, 'downloadTemplate'])->name('subscriptions.import.template');
    Route::post('/subscriptions/import', [SubscriptionController::class, 'import'])->name('subscriptions.import');

    // Clientes & Importación
    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
    Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
    Route::put('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');
    Route::delete('/clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
    Route::patch('/clients/{client}/contacts/{contact}/primary', [ClientController::class, 'setPrimaryContact'])->name('clients.contacts.set-primary');
    Route::get('/clients/import/template', [ClientImportController::class, 'downloadTemplate'])->name('clients.import.template');
    Route::post('/clients/import', [ClientImportController::class, 'store'])->name('clients.import');
    

    // Reportes
    Route::get('/reports/contacts/pdf', [ReportController::class, 'contactsPdf'])->name('reports.contacts.pdf');
    Route::get('/reports/contacts/excel', [ReportController::class, 'contactsExcel'])->name('reports.contacts.excel');
    Route::get('/reports/products/pdf', [ReportController::class, 'productsPdf'])->name('reports.products.pdf');
    Route::get('/reports/products/excel', [ReportController::class, 'productsExcel'])->name('reports.products.excel');
    Route::get('/reports/subscriptions/pdf', [ReportController::class, 'subscriptionsPdf'])->name('reports.subscriptions.pdf');
    Route::get('/reports/subscriptions/excel', [ReportController::class, 'subscriptionsExcel'])->name('reports.subscriptions.excel');
    Route::get('/reports/clients/pdf', [ReportController::class, 'clientsPdf'])->name('reports.clients.pdf');
    Route::get('/reports/clients/excel', [ReportController::class, 'clientsExcel'])->name('reports.clients.excel');
    

    // Admin
    Route::post('/admin/companies', [CompanyController::class, 'store'])->name('admin.companies.store');
    Route::patch('/admin/companies/{company}', [CompanyController::class, 'update'])->name('admin.companies.update');
    Route::delete('/admin/companies/{company}', [CompanyController::class, 'destroy'])->name('admin.companies.destroy');
});

// Ruta temporal de prueba
Route::get('/test-apple', function () {
    return Inertia::render('Welcome');
});