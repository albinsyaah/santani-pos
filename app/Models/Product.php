<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'sku',
        'barcode',
        'name',
        'image',
        'active_ingredient',
        'unit',
        'min_stock_level',
        'purchase_price',
        'selling_price',
        'is_restricted',
    ];

    protected function casts(): array
    {
        return [
            'min_stock_level' => 'integer',
            'purchase_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'is_restricted' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(ProductBatch::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    // Accessor: Get aggregated stock across all valid non-expired batches
    public function getTotalStockAttribute(): int
    {
        return $this->batches()
            ->where('stock_quantity', '>', 0)
            ->where(fn ($q) => $q->whereNull('expiration_date')->orWhere('expiration_date', '>', now()))
            ->sum('stock_quantity');
    }
}
