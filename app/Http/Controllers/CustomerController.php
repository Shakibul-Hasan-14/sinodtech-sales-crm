<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Mail\ReengagementEmail;
use Illuminate\Support\Facades\Mail;

class CustomerController extends Controller
{
    public function index()
    {
        return Customer::with('assignedEmployee')->latest()->paginate(15);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'phone' => 'nullable|string|max:30',
        ]);

        $customer = Customer::create($validated);

        return response()->json($customer, 201);
    }

    public function show(Customer $customer)
    {
        return [
            'customer' => $customer->load('assignedEmployee'),
            'purchase_history' => $customer->sales()->with('items.product')->latest('sale_date')->get(),
            'purchase_frequency' => $customer->purchaseFrequency(),
            'last_purchase_date' => $customer->lastPurchaseDate(),
        ];
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:customers,email,' . $customer->id,
            'phone' => 'nullable|string|max:30',
        ]);

        $customer->update($validated);

        return $customer;
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return response()->json(null, 204);
    }

    /**
     * List customers inactive for the given period (default 90 days).
     * GET /api/customers/inactive?days=90
     */
    public function inactive(Request $request)
    {
        $days = (int) $request->query('days', 90);

        return Customer::inactive($days)
            ->with('assignedEmployee')
            ->get()
            ->map(function (Customer $customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'assigned_employee' => $customer->assignedEmployee?->name,
                    'last_purchase_date' => $customer->lastPurchaseDate(),
                ];
            });
    }

    /**
     * Assign an inactive customer to an employee for follow-up.
     * PATCH /api/customers/{customer}/assign
     */
    public function assign(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
        ]);

        $customer->update(['assigned_employee_id' => $validated['employee_id']]);

        return $customer->load('assignedEmployee');
    }

    public function reengage(Customer $customer)
    {
        Mail::to($customer->email)->send(new ReengagementEmail($customer));

        return response()->json([
            'message' => "Re-engagement email sent to {$customer->name}.",
        ]);
    }
}