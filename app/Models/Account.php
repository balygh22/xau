<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Account extends Model
{
    use HasFactory;

    protected $table = 'accounts';
    protected $primaryKey = 'AccountID';
    public $timestamps = false; // جدول accounts لا يحتوي على طوابع زمنية

    // السماح بالتعبئة الجماعية لكلٍ من الأسماء الحديثة والقديمة
    protected $fillable = [
        // legacy
        'AccountName', 'AccountType', 'Identifier', 'IsActive',
        // modern
        'name', 'account_type', 'identifier', 'is_active',
    ];

    protected $casts = [
        'IsActive' => 'boolean',
        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Accessors & Mutators (المترجمات)
    |--------------------------------------------------------------------------
    | هذا القسم يترجم بين أسماء الحقول الحديثة (المستخدمة في الواجهات)
    | وأسماء الأعمدة القديمة (الموجودة في قاعدة البيانات).
    */

    // 'id' -> 'AccountID' (للقراءة فقط)
    protected function id(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => $attributes['AccountID'] ?? null,
        );
    }

    // 'name' <-> 'AccountName'
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => $attributes['AccountName'] ?? null,
            set: fn ($value) => ['AccountName' => $value],
        );
    }

    // 'account_type' <-> 'AccountType'
    protected function accountType(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => $attributes['AccountType'] ?? null,
            set: fn ($value) => ['AccountType' => $value],
        );
    }

    // 'identifier' <-> 'Identifier'
    protected function identifier(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => $attributes['Identifier'] ?? null,
            set: fn ($value) => ['Identifier' => $value],
        );
    }

    // 'is_active' <-> 'IsActive'
    protected function isActive(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => (bool)($attributes['IsActive'] ?? false),
            set: fn ($value) => ['IsActive' => (bool)$value],
        );
    }

    // Scope: only active accounts (handles legacy/new column names)
    public function scopeActive($query)
    {
        $hasPascal = Schema::hasColumn('accounts','IsActive');
        $hasSnake  = Schema::hasColumn('accounts','is_active');
        if ($hasPascal) {
            return $query->where('IsActive', 1);
        }
        if ($hasSnake) {
            return $query->where('is_active', 1);
        }
        return $query; // no-op if neither column exists
    }

    /**
     * تسمية نوع الحساب بالعربية
     */
    public function getTypeLabelAttribute(): string
    {
        $type = $this->account_type;
        return [
            'Cashbox' => 'صندوق',
            'Bank'    => 'بنك',
            'Customer'=> 'عميل',
            'Supplier'=> 'مورد',
        ][$type] ?? (string)$type;
    }

    /**
     * علاقة الحساب مع أرصدته.
     */
    public function balances(): HasMany
    {
        return $this->hasMany(AccountBalance::class, 'AccountID', 'AccountID');
    }
    
    /**
     * علاقة الحساب مع العملات من خلال جدول الأرصدة.
     */
    public function currencies()
    {
        return $this->belongsToMany(Currency::class, 'account_balances', 'AccountID', 'CurrencyID')
                    ->withPivot('CurrentBalance');
    }
}
