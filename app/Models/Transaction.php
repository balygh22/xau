<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $table = 'transactions';
    protected $primaryKey = 'TransactionID';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'TransactionNumber',
        'TransactionType', // 'Sale' | 'Purchase' | 'SaleReturn' | 'PurchaseReturn'
        'AccountID',       // عميل/مورد
        'CurrencyID',
        'TotalAmount',
        'PaidAmount',
        'TransactionDate',
        'Notes',
        'UserID',
        'OriginalTransactionID',
    ];

    protected $casts = [
        'TransactionDate' => 'datetime',
        'TotalAmount'     => 'decimal:2',
        'PaidAmount'      => 'decimal:2',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(TransactionDetail::class, 'TransactionID', 'TransactionID');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'AccountID', 'AccountID');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'CurrencyID', 'CurrencyID');
    }

    public function user(): BelongsTo
    {
        // Foreign key on transactions = UserID, Owner key on users = UserID
        return $this->belongsTo(User::class, 'UserID', 'UserID');
    }

    // حالة الدفع
    public function getPaymentStatusAttribute(): string
    {
        $total = (float) $this->TotalAmount;
        $paid  = (float) $this->PaidAmount;
        if ($total <= 0) return 'غير محدد';
        if ($paid <= 0) return 'غير مدفوعة';
        if ($paid >= $total) return 'مدفوعة بالكامل';
        return 'مدفوعة جزئياً';
    }

    public function getPaymentBadgeClassAttribute(): string
    {
        return match ($this->payment_status) {
            'مدفوعة بالكامل' => 'success',
            'مدفوعة جزئياً'  => 'warning',
            'غير مدفوعة'     => 'danger',
            default           => 'secondary',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->TransactionType) {
            'Sale'            => 'فاتورة بيع',
            'Purchase'        => 'فاتورة شراء',
            'SaleReturn'      => 'مرتجع بيع',
            'PurchaseReturn'  => 'مرتجع شراء',
            default           => 'معاملة',
        };
    }
}
