<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    protected $table = 'currencies';
    protected $primaryKey = 'CurrencyID';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'CurrencyCode',
        'CurrencyName',
        'IsDefault',
    ];

    protected $casts = [
        'IsDefault' => 'boolean',
    ];

    // Accessors to support legacy + new names in views/controllers
    public function getCodeAttribute(): ?string
    {
        return $this->attributes['CurrencyCode'] ?? null;
    }

    public function getNameAttribute(): ?string
    {
        return $this->attributes['CurrencyName'] ?? null;
    }

    public function getIsDefaultAttribute($value): bool
    {
        // prefer native cast on IsDefault, but allow reading via is_default too
        if (array_key_exists('IsDefault', $this->attributes)) {
            return (bool)$this->attributes['IsDefault'];
        }
        return (bool)$value;
    }
}
