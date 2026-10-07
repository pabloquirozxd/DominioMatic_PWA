<?php

namespace App\Http\Controllers;

use App\Exports\ClientsExport;
use App\Exports\ContactsExport;
use App\Exports\ProductsExport;
use App\Exports\SubscriptionsExport;
use App\Models\Client;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Product;
use App\Models\Subscription;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    /**
     * Obtener la empresa activa del usuario.
     */
    private function getCompany(Request $request): ?Company
    {
        $user = $request->user();

        if (session()->has('active_company_id')) {
            $companyId = (int) session('active_company_id');
            return $user->companies()->where('companies.id', $companyId)->first();
        }

        return $user->companies()->first();
    }

    public function contactsPdf(Request $request)
    {
        $company = $this->getCompany($request);

        $contacts = Contact::query()
            ->where('company_id', $company?->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $pdf = Pdf::loadView('reports.contacts-pdf', [
            'company' => $company,
            'user' => $request->user(),
            'contacts' => $contacts,
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download($this->reportFilename('contactos', 'pdf'));
    }

    public function contactsExcel(Request $request)
    {
        return Excel::download(
            new ContactsExport($request->user()),
            $this->reportFilename('contactos', 'xlsx')
        );
    }

    public function productsPdf(Request $request)
    {
        $company = $this->getCompany($request);

        if (!$company) {
            abort(403, 'No se encontró una empresa activa vinculada.');
        }

        $products = Product::query()
            ->where('company_id', $company->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $pdf = Pdf::loadView('reports.products-pdf', [
            'company' => $company,
            'user' => $request->user(),
            'products' => $products,
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download($this->reportFilename('productos', 'pdf'));
    }

    public function productsExcel(Request $request)
    {
        return Excel::download(
            new ProductsExport($request->user()),
            $this->reportFilename('productos', 'xlsx')
        );
    }

    public function subscriptionsPdf(Request $request)
    {
        $company = $this->getCompany($request);

        if (!$company) {
            abort(403, 'No se encontró una empresa activa vinculada.');
        }

        // Se usa 'client' en lugar de 'contact'
        $subscriptions = Subscription::query()
            ->where('company_id', $company->id)
            ->with(['client', 'product'])
            ->orderBy('created_at', 'desc')
            ->get();

        $pdf = Pdf::loadView('reports.subscriptions-pdf', [
            'company' => $company,
            'user' => $request->user(),
            'subscriptions' => $subscriptions,
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download($this->reportFilename('suscripciones', 'pdf'));
    }

    public function subscriptionsExcel(Request $request)
    {
        return Excel::download(
            new SubscriptionsExport($request->user()),
            $this->reportFilename('suscripciones', 'xlsx')
        );
    }

    // ==========================================
    // Módulo de Clientes
    // ==========================================

    public function clientsPdf(Request $request)
    {
        $company = $this->getCompany($request);

        $clients = Client::query()
            ->where('company_id', $company?->id)
            ->with(['contacts' => function ($query) {
                $query->orderBy('is_primary', 'desc')->orderBy('created_at', 'asc');
            }]) 
            ->orderBy('created_at', 'desc')
            ->get();

        $pdf = Pdf::loadView('reports.clients-pdf', [
            'company' => $company,
            'user' => $request->user(),
            'clients' => $clients,
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download($this->reportFilename('clientes', 'pdf'));
    }

    public function clientsExcel(Request $request)
    {
        return Excel::download(
            new ClientsExport($request->user()),
            $this->reportFilename('clientes', 'xlsx')
        );
    }

    private function reportFilename(string $module, string $extension): string
    {
        $timestamp = now()->format('Y-m-d_H-i-s');

        return "reporte-{$module}-{$timestamp}.{$extension}";
    }
}