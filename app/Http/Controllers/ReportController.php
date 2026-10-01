<?php

namespace App\Http\Controllers;

use App\Exports\ClientsExport; // <-- Importante
use App\Exports\ContactsExport;
use App\Exports\ProductsExport;
use App\Exports\SubscriptionsExport;
use App\Models\Client; // <-- Importante
use App\Models\Contact;
use App\Models\Product;
use App\Models\Subscription;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function contactsPdf(Request $request)
    {
        $user = $request->user()->load('company');

        $contacts = Contact::query()
            ->where('company_id', $user->company_id)
            ->orderBy('created_at', 'desc')
            ->get();

        $pdf = Pdf::loadView('reports.contacts-pdf', [
            'company' => $user->company,
            'user' => $user,
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
        $user = $request->user()->load('company');

        $products = Product::query()
            ->where('company_id', $user->company_id)
            ->orderBy('created_at', 'desc')
            ->get();

        $pdf = Pdf::loadView('reports.products-pdf', [
            'company' => $user->company,
            'user' => $user,
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
        $user = $request->user()->load('company');

        $subscriptions = Subscription::query()
            ->where('company_id', $user->company_id)
            ->with(['contact', 'product'])
            ->orderBy('created_at', 'desc')
            ->get();

        $pdf = Pdf::loadView('reports.subscriptions-pdf', [
            'company' => $user->company,
            'user' => $user,
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
        $user = $request->user()->load('company');

        // Cargamos los contactos ordenando para que el principal esté siempre arriba
        $clients = Client::query()
            ->where('company_id', $user->company_id)
            ->with(['contacts' => function ($query) {
                $query->orderBy('is_primary', 'desc')->orderBy('created_at', 'asc');
            }]) 
            ->orderBy('created_at', 'desc')
            ->get();

        $pdf = Pdf::loadView('reports.clients-pdf', [
            'company' => $user->company,
            'user' => $user,
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