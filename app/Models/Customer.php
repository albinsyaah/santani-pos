<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'farmer_group',
        'address',
        'credit_limit',
        'current_debt',
    ];

    protected function casts(): array
    {
        return [
            'credit_limit' => 'decimal:2',
            'current_debt' => 'decimal:2',
        ];
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(CustomerPayment::class);
    }

    // Helper: Calculate remaining credit available
    public function getAvailableCreditAttribute(): float
    {
        return max(0, $this->credit_limit - $this->current_debt);
    }

    // Helper: Check if customer can borrow an amount
    public function canBorrow(float $amount): bool
    {
        return ($this->current_debt + $amount) <= $this->credit_limit;
    }
}
