<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Barrier\DomPDF\Facade\Pdf; // O la fachada PDF instalada
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SubscriptionsExport;

class SubscriptionReportController extends Controller
{
    public function exportPdf()
    {
        // Se obtiene con 'client' y 'product', eliminando llamadas a 'contact'
        $subscriptions = Subscription::with(['client', 'product'])->get();

        $pdf = Pdf::loadView('reports.subscriptions_pdf', compact('subscriptions'));
        
        return $pdf->download('reporte_suscripciones.pdf');
    }

    public function exportExcel()
    {
        return Excel::download(new SubscriptionsExport, 'suscripciones.xlsx');
    }
}