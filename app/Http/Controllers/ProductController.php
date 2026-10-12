<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private array $rules = [
        'name'      => ['required', 'string', 'max:150'],
        'price'     => ['required', 'numeric', 'min:0'],
        'stock_kg'  => ['required', 'numeric', 'min:0'],
        'image_url' => ['nullable', 'url', 'max:500'],
        'description' => ['nullable', 'string', 'max:255'],
    ];

    public function store(Request $request)
    {
        $product = Product::create($request->validate($this->rules));
        ActivityLog::record('Product created', $product->name);

        return back()->with('ok', 'Product added.');
    }

    public function update(Request $request, Product $product)
    {
        $product->update($request->validate($this->rules));
        ActivityLog::record('Product updated', $product->name);

        return back()->with('ok', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        ActivityLog::record('Product deleted', $product->name);

        return back()->with('ok', 'Product deleted.');
    }
}
