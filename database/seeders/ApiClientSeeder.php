<?php

namespace Database\Seeders;

use App\Models\ApiClient;
use Illuminate\Database\Seeder;

class ApiClientSeeder extends Seeder
{
    /**
     * Seed a simulated third-party e-commerce client and issue
     * an API token via Sanctum's standard token creation flow.
     */
    public function run(): void
    {
        $client = ApiClient::firstOrCreate(['name' => 'Simulated E-Commerce Platform']);

        // Clear old tokens so re-seeding doesn't accumulate duplicates
        $client->tokens()->delete();

        $token = $client->createToken('ecommerce-integration')->plainTextToken;

        $this->command->info("API Client seeded. Test token: {$token}");
    }
}