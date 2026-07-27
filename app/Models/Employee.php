<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'kpi_score',
    ];

    public function customers()
    {
        return $this->hasMany(Customer::class, 'assigned_employee_id');
    }

    public function incrementKpi(int $points = 1): void
    {
        $this->increment('kpi_score', $points);
    }
}