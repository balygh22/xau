<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';
    protected $primaryKey = 'PaymentID';
    public $timestamps = false;

    protected $fillable = [
        'TransactionID',
        'FromAccountID',
        'ToAccountID',
        'PaymentDate',
        'Amount',
        'CurrencyID',
        'Description',
        'UserID',
    ];

    protected $casts = [
        'PaymentDate' => 'datetime',
        'Amount' => 'decimal:2',
    ];

    // Accessors to expose snake_case names used in views
    public function getPaymentNumberAttribute(): ?string
    {
        return $this->attributes['payment_number']
            ?? $this->attributes['PaymentNumber']
            ?? null;
    }

    public function getPaymentDateAttribute($value)
    {
        // Normalize to Carbon regardless of column case
        $date = $this->attributes['payment_date']
            ?? $this->attributes['PaymentDate']
            ?? $value;
        return $date ? \Illuminate\Support\Carbon::parse($date) : null;
    }

    public function getAmountAttribute($value)
    {
        return $this->attributes['amount']
            ?? $this->attributes['Amount']
            ?? $value;
    }

    public function getDescriptionAttribute($value)
    {
        return $this->attributes['description']
            ?? $this->attributes['Description']
            ?? $value;
    }

    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'FromAccountID', 'AccountID');
    }

    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'ToAccountID', 'AccountID');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'CurrencyID', 'CurrencyID');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'UserID', 'UserID');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'TransactionID', 'TransactionID');
    }

    // ----- Computed attributes for operation type -----
    public function getTypeKeyAttribute(): string
    {
        $fromType = $this->fromAccount->AccountType ?? null;
        $toType   = $this->toAccount->AccountType ?? null;

        $isCashOrBank = fn($t) => in_array($t, ['Cashbox', 'Bank'], true);

        if ($isCashOrBank($fromType) && $isCashOrBank($toType)) {
            return 'transfer'; // تحويل داخلي
        }
        if ($fromType === 'Customer' && $isCashOrBank($toType)) {
            return 'receipt'; // سند قبض
        }
        if ($isCashOrBank($fromType) && in_array($toType, ['Supplier', 'Employee', 'Expenses'], true)) {
            return 'disbursement'; // سند صرف
        }
        return 'journal'; // قيد عام / غير مصنّف
    }

    public function getTypeLabelAttribute(): string
    {
        return [
            'receipt' => 'سند قبض',
            'disbursement' => 'سند صرف',
            'transfer' => 'تحويل داخلي',
            'journal' => 'قيد',
        ][$this->type_key] ?? 'قيد';
    }

    public function getTypeBadgeClassAttribute(): string
    {
        return [
            'receipt' => 'success',
            'disbursement' => 'warning',
            'transfer' => 'primary',
            'journal' => 'secondary',
        ][$this->type_key] ?? 'secondary';
    }

    public function getTypeIconAttribute(): string
    {
        return [
            'receipt' => 'fas fa-hand-holding-usd',
            'disbursement' => 'fas fa-cash-register',
            'transfer' => 'fas fa-exchange-alt',
            'journal' => 'fas fa-book',
        ][$this->type_key] ?? 'fas fa-book';
    }
}