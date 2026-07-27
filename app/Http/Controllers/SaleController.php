<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function __construct(protected SaleService $saleService)
    {
    }

    public function index()
    {
        return Sale::with('items.product', 'customer')
            ->latest('sale_date')
            ->paginate(15);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            $sale = $this->saleService->createSale(
                $validated['customer_id'],
                $validated['items']
            );

            return response()->json($sale, 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function show(Sale $sale)
    {
        return $sale->load('items.product', 'customer');
    }
}