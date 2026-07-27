<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Mail\InvoiceEmail;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class SaleService
{
    /**
     * Create a sale with multiple line items, deducting stock atomically.
     *
     * @param  int  $customerId
     * @param  array<int, array{product_id: int, quantity: int}>  $items
     * @return Sale
     *
     * @throws ValidationException if any product has insufficient stock
     */
    public function createSale(int $customerId, array $items): Sale
    {
        return DB::transaction(function () use ($customerId, $items) {
            $customerWasInactive = Customer::inactive()->whereKey($customerId)->exists();

            $totalAmount = 0;
            $lockedProducts = [];

            // Lock rows first to prevent race conditions on concurrent sales
            foreach ($items as $item) {
                $product = Product::where('id', $item['product_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($product->stock_quantity < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'stock' => "Insufficient stock for product '{$product->name}'. Available: {$product->stock_quantity}, requested: {$item['quantity']}.",
                    ]);
                }

                $lockedProducts[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                ];

                $totalAmount += $product->price * $item['quantity'];
            }

            $sale = Sale::create([
                'customer_id' => $customerId,
                'total_amount' => $totalAmount,
                'sale_date' => now(),
            ]);

            foreach ($lockedProducts as $entry) {
                $product = $entry['product'];
                $quantity = $entry['quantity'];

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                ]);

                $product->decrement('stock_quantity', $quantity);
            }

            if ($customerWasInactive) {
                $this->rewardAssignedEmployee($customerId);
            }

            $sale->load('items.product', 'customer');

            Mail::to($sale->customer->email)->send(new InvoiceEmail($sale));

            return $sale;
        });
    }

    /**
     * If a previously-inactive customer just purchased and is assigned
     * to an employee, increase that employee's KPI score.
     */
    protected function rewardAssignedEmployee(int $customerId): void
    {
        $customer = Customer::find($customerId);

        if ($customer && $customer->assigned_employee_id) {
            $customer->assignedEmployee->incrementKpi();
        }
    }
}