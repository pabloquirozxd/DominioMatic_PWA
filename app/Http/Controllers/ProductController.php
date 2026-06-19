<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(): Response
    {
        $companyId = auth()->user()->company_id;

        abort_unless($companyId, 403);

        $products = Product::where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        return Inertia::render('Products/Index', [
            'products' => $products,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $companyId = auth()->user()->company_id;

        abort_unless($companyId, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price_list' => ['required', 'numeric', 'min:0'],
            'is_infinite' => ['required', 'boolean'],
            'stock' => ['nullable', 'integer', 'min:0'],
        ]);

        Product::create([
            'company_id' => $companyId,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price_list' => $validated['price_list'],
            'is_infinite' => $validated['is_infinite'],
            'stock' => $validated['is_infinite'] ? 0 : ($validated['stock'] ?? 0),
        ]);

        return redirect()->route('products.index');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $companyId = auth()->user()->company_id;

        abort_unless($companyId && $product->company_id === $companyId, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price_list' => ['required', 'numeric', 'min:0'],
            'is_infinite' => ['required', 'boolean'],
            'stock' => ['nullable', 'integer', 'min:0'],
        ]);

        $product->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price_list' => $validated['price_list'],
            'is_infinite' => $validated['is_infinite'],
            'stock' => $validated['is_infinite'] ? 0 : ($validated['stock'] ?? 0),
        ]);

        return redirect()->route('products.index');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $companyId = auth()->user()->company_id;

        abort_unless($companyId && $product->company_id === $companyId, 403);

        $product->delete();

        return redirect()->route('products.index');
    }
}