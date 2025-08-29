<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountBalance extends Model
{
    use HasFactory;

    protected $table = 'account_balances';
    public $incrementing = false; // composite PK
    protected $primaryKey = null; // not used by Eloquent
    public $timestamps = true; // timestamps now present on table

    // Allow both legacy and new column names for compatibility
    protected $fillable = [
        'account_id', 'AccountID',
        'currency_id', 'CurrencyID',
        'current_balance', 'CurrentBalance',
    ];

    public function account()
    {
        // Support both schemas: account_id (new) or AccountID (legacy)
        $foreignKey = \Illuminate\Support\Facades\Schema::hasColumn('account_balances', 'account_id') ? 'account_id' : 'AccountID';
        return $this->belongsTo(Account::class, $foreignKey, 'AccountID');
    }

    public function currency()
    {
        // Support both schemas for currency: currency_id (new) or CurrencyID (legacy)
        $foreignKey = \Illuminate\Support\Facades\Schema::hasColumn('account_balances', 'currency_id') ? 'currency_id' : 'CurrencyID';
        // Currencies owner key can be 'id' (new) or 'CurrencyID' (legacy)
        $ownerKey = \Illuminate\Support\Facades\Schema::hasColumn('currencies', 'id') ? 'id' : 'CurrencyID';
        return $this->belongsTo(Currency::class, $foreignKey, $ownerKey);
    }
}