<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Imports\ProductsImport;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    /**
     * Obtiene y valida el ID de la empresa activa del usuario desde la sesión.
     */
    private function getActiveCompanyId(Request $request): int
    {
        $activeCompanyId = $request->session()->get('active_company_id');

        abort_unless($activeCompanyId, 403);

        // Validar que el usuario realmente pertenezca a la empresa activa en sesión
        $belongsToCompany = $request->user()
            ->companies()
            ->where('companies.id', $activeCompanyId)
            ->exists();

        abort_unless($belongsToCompany, 403);

        return (int) $activeCompanyId;
    }

    public function index(Request $request): Response
    {
        $companyId = $this->getActiveCompanyId($request);

        $products = Product::where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        return Inertia::render('Products/Index', [
            'products' => $products,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $companyId = $this->getActiveCompanyId($request);

        $validated = $request->validate([
            'type'        => ['required', 'string', 'in:service,product'],
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price_list'  => ['required', 'numeric', 'min:0'],
            'currency'    => ['required', 'string', 'in:USD,BOB'],
            'is_infinite' => ['required', 'boolean'],
            'stock'       => ['nullable', 'integer', 'min:0'],
        ]);

        $isService = $validated['type'] === 'service';
        $isInfinite = $isService ? true : $validated['is_infinite'];

        Product::create([
            'company_id'  => $companyId,
            'type'        => $validated['type'],
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price_list'  => $validated['price_list'],
            'currency'    => $validated['currency'],
            'is_infinite' => $isInfinite,
            'stock'       => $isInfinite ? 0 : ($validated['stock'] ?? 0),
        ]);

        return redirect()->route('products.index');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $companyId = $this->getActiveCompanyId($request);

        abort_unless($product->company_id === $companyId, 403);

        $validated = $request->validate([
            'type'        => ['required', 'string', 'in:service,product'],
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price_list'  => ['required', 'numeric', 'min:0'],
            'currency'    => ['required', 'string', 'in:USD,BOB'],
            'is_infinite' => ['required', 'boolean'],
            'stock'       => ['nullable', 'integer', 'min:0'],
        ]);

        $isService = $validated['type'] === 'service';
        $isInfinite = $isService ? true : $validated['is_infinite'];

        $product->update([
            'type'        => $validated['type'],
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price_list'  => $validated['price_list'],
            'currency'    => $validated['currency'],
            'is_infinite' => $isInfinite,
            'stock'       => $isInfinite ? 0 : ($validated['stock'] ?? 0),
        ]);

        return redirect()->route('products.index');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $companyId = $this->getActiveCompanyId($request);

        abort_unless($product->company_id === $companyId, 403);

        $product->delete();

        return redirect()->route('products.index');
    }

    public function import(Request $request): RedirectResponse
    {
        $companyId = $this->getActiveCompanyId($request);

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,xlsx,xls', 'max:10240'],
        ]);

        Excel::import(new ProductsImport($companyId), $request->file('file'));

        return redirect()->route('products.index')->with('success', 'Catálogo importado correctamente.');
    }
}