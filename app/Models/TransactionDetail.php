<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionDetail extends Model
{
    // Match DB: table name is `transactiondetails`
    protected $table = 'transactiondetails';
    public $timestamps = true;

    protected $fillable = [
        'TransactionID',
        'ProductID',
        'Quantity',      // قطع أو وزن
        'UnitPrice',
        'LineTotal',
        'Weight',        // عمود مطابق للقاعدة
    ];

    protected $casts = [
        'Quantity'  => 'decimal:3',
        'Weight'    => 'decimal:3',
        'UnitPrice' => 'decimal:2',
        'LineTotal' => 'decimal:2',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'TransactionID', 'TransactionID');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'ProductID', 'ProductID');
    }
}
