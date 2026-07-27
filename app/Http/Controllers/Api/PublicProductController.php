<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;

class PublicProductController extends Controller
{
    /**
     * Expose product info for third-party e-commerce consumption.
     */
    public function index()
    {
        return Product::select('sku', 'name', 'price', 'stock_quantity')
            ->get()
            ->map(fn (Product $product) => [
                'sku' => $product->sku,
                'product_name' => $product->name,
                'price' => (float) $product->price,
                'available_stock' => $product->stock_quantity,
            ]);
    }
}