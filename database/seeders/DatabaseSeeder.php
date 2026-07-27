<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\Product;
use App\Services\SaleService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Core reference data
        $employees = Employee::factory()->count(5)->create();
        $products = Product::factory()->count(20)->create();
        $customers = Customer::factory()->count(30)->create();

        // Assign a few customers to employees
        $customers->take(10)->each(function (Customer $customer) use ($employees) {
            $customer->update(['assigned_employee_id' => $employees->random()->id]);
        });

        $saleService = new SaleService();

        // Generate realistic sales
        $customers->each(function (Customer $customer, int $index) use ($products, $saleService) {
            $purchaseCount = fake()->numberBetween(0, 4);

            for ($i = 0; $i < $purchaseCount; $i++) {
                $product = $products->random();

                // Only sell if there's enough stock left
                if ($product->stock_quantity < 1) {
                    continue;
                }

                $quantity = fake()->numberBetween(1, min(3, $product->stock_quantity));

                try {
                    $sale = $saleService->createSale($customer->id, [
                        ['product_id' => $product->id, 'quantity' => $quantity],
                    ]);

                    // Backdate roughly a third of sales beyond 90 days,
                    // so the "lost customer" feature has real data to detect
                    if ($index % 3 === 0) {
                        $sale->update([
                            'sale_date' => now()->subDays(fake()->numberBetween(91, 365)),
                        ]);
                    } else {
                        $sale->update([
                            'sale_date' => now()->subDays(fake()->numberBetween(0, 60)),
                        ]);
                    }
                } catch (\Illuminate\Validation\ValidationException $e) {
                    // Skip if stock ran out mid-seed
                    continue;
                }
            }
        });
    }
}