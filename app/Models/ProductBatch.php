<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'batch_number',
        'purchase_price',
        'selling_price',
        'stock_quantity',
        'manufactured_date',
        'expiration_date',
    ];

    protected function casts(): array
    {
        return [
            'stock_quantity' => 'integer',
            'purchase_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'manufactured_date' => 'date',
            'expiration_date' => 'date',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    // FEFO Scope: Get available non-expired batches ordered by earliest expiration
    public function scopeFefoAvailable(Builder $query): Builder
    {
        return $query->where('stock_quantity', '>', 0)
            ->where('expiration_date', '>', now())
            ->orderBy('expiration_date', 'asc');
    }

    // Scope: Check batches expiring within given number of days
    public function scopeExpiringSoon(Builder $query, int $days = 30): Builder
    {
        return $query->where('stock_quantity', '>', 0)
            ->whereBetween('expiration_date', [now(), now()->addDays($days)])
            ->orderBy('expiration_date', 'asc');
    }
}
