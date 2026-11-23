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

    /**
     * Next auto-generated batch number for today, e.g. B261123-001.
     * $offset lets a form reserve several consecutive numbers before any are saved.
     */
    public static function generateNumber(int $offset = 0): string
    {
        $prefix = 'B'.now()->format('ymd').'-';

        $last = static::query()
            ->where('batch_number', 'like', $prefix.'%')
            ->pluck('batch_number')
            ->map(fn (string $number) => (int) substr($number, strlen($prefix)))
            ->max() ?? 0;

        return $prefix.str_pad((string) ($last + 1 + $offset), 3, '0', STR_PAD_LEFT);
    }

    // FEFO Scope: Get available non-expired batches ordered by earliest expiration
    public function scopeFefoAvailable(Builder $query): Builder
    {
        return $query->where('stock_quantity', '>', 0)
            ->where(fn (Builder $q) => $q->whereNull('expiration_date')->orWhere('expiration_date', '>', now()))
            // Batches without an expiry date are sold last, after any dated stock.
            ->orderByRaw('CASE WHEN expiration_date IS NULL THEN 1 ELSE 0 END')
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
