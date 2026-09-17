<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendingCart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_id',
        'note',
        'cart_data',
        'payment_method',
        'discount',
        'tax',
    ];

    protected function casts(): array
    {
        return [
            'cart_data' => 'array',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function getItemCountAttribute(): int
    {
        return collect($this->cart_data)->sum('quantity');
    }

    public function getSubtotalAttribute(): float
    {
        return collect($this->cart_data)->sum(fn ($line) => $line['unit_price'] * $line['quantity']);
    }

    public function getGrandTotalAttribute(): float
    {
        return max(0, $this->subtotal - (float) $this->discount + (float) $this->tax);
    }
}
