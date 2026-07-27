<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'assigned_employee_id',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function assignedEmployee()
    {
        return $this->belongsTo(Employee::class, 'assigned_employee_id');
    }

    /**
     * Date of the customer's most recent purchase, or null if they've never purchased.
     */
    public function lastPurchaseDate(): ?Carbon
    {
        return $this->sales()->latest('sale_date')->value('sale_date');
    }

    /**
     * Total number of purchases made by this customer.
     */
    public function purchaseFrequency(): int
    {
        return $this->sales()->count();
    }

    /**
     * Tracking inactive customers.
     */
    public function scopeInactive(Builder $query, int $days = 90): Builder
    {
        $cutoff = now()->subDays($days);

        return $query->whereDoesntHave('sales', function (Builder $q) use ($cutoff) {
            $q->where('sale_date', '>=', $cutoff);
        });
    }
}