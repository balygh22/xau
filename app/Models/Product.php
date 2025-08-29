<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    /**
     * اسم الجدول والمفتاح الأساسي متوافق مع قاعدة البيانات.
     */
    protected $table = 'products';
    protected $primaryKey = 'ProductID';

    /**
     * قاعدة البيانات لا تستخدم created_at/updated_at.
     */
    public $timestamps = false;

    /**
     * الأهم: قائمة الأعمدة الفعلية التي يمكن تعبئتها في جدول `products`.
     * تم إزالة الحقول الافتراضية (name, sku, etc.) من هنا لأنها ستُترجم عبر الـ Mutators.
     */
    protected $fillable = [
        'ProductCode',
        'ProductName',
        'CategoryID',
        'GoldWeight',
        'Purity',
        'StoneWeight',
        'LaborCost',
        'CurrencyID',
        'StockByWeight',
        'StockByUnit',
    ];

    /**
     * تحويل أنواع البيانات للأعمدة عند الحاجة.
     */
    protected $casts = [
        'GoldWeight' => 'decimal:3',
        'StoneWeight' => 'decimal:3',
        'LaborCost' => 'decimal:2',
        'StockByWeight' => 'decimal:3',
        'StockByUnit' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Accessors & Mutators (المترجمات)
    |--------------------------------------------------------------------------
    | هذه الدوال تترجم بين أسماء الحقول الحديثة (المستخدمة في الواجهات)
    | وأسماء الأعمدة القديمة (الموجودة في قاعدة البيانات).
    */

    // 'name' <-> 'ProductName'
    public function setNameAttribute($value): void { $this->attributes['ProductName'] = $value; }
    public function getNameAttribute(): ?string { return $this->attributes['ProductName'] ?? null; }

    // 'sku' <-> 'ProductCode'
    public function setSkuAttribute($value): void { $this->attributes['ProductCode'] = $value; }
    public function getSkuAttribute(): ?string { return $this->attributes['ProductCode'] ?? null; }

    // 'category_id' <-> 'CategoryID'
    public function setCategoryIdAttribute($value): void { $this->attributes['CategoryID'] = $value; }
    public function getCategoryIdAttribute(): ?int { return $this->attributes['CategoryID'] ?? null; }

    // 'net_gold_weight' <-> 'GoldWeight'
    public function setNetGoldWeightAttribute($value): void { $this->attributes['GoldWeight'] = $value; }
    public function getNetGoldWeightAttribute(): ?string { return $this->attributes['GoldWeight'] ?? null; }

    // 'stone_weight' <-> 'StoneWeight'
    public function setStoneWeightAttribute($value): void { $this->attributes['StoneWeight'] = $value; }
    public function getStoneWeightAttribute(): ?string { return $this->attributes['StoneWeight'] ?? null; }

    // 'making_cost' <-> 'LaborCost'
    public function setMakingCostAttribute($value): void { $this->attributes['LaborCost'] = $value; }
    public function getMakingCostAttribute(): ?string { return $this->attributes['LaborCost'] ?? null; }
    
    // 'karat' <-> 'Purity'
    public function setKaratAttribute($value): void { $this->attributes['Purity'] = $value; }
    public function getKaratAttribute(): ?string { return $this->attributes['Purity'] ?? null; }

    // 'quantity' هو حقل افتراضي يتم توجيهه إلى المخزون بالقطعة أو بالوزن
    public function setQuantityAttribute($value): void
    {
        // نحتاج لمعرفة 'unit' لتحديد أين نضع الكمية.
        // هذا المنطق يتم التعامل معه الآن في دالة 'booted' التي لديها السياق الكامل.
        $this->attributes['quantity_virtual'] = $value; // نخزنها مؤقتًا
    }
    public function getQuantityAttribute()
    {
        // من الصعب معرفة ما إذا كانت الكمية بالقطعة أو بالوزن عند القراءة.
        // للتبسيط، نعطي الأولوية للقطعة، ثم الوزن.
        return $this->StockByUnit ?? $this->StockByWeight ?? 0;
    }


    /**
     * يتم تشغيل هذا الكود تلقائيًا عند إنشاء أو تحديث المنتج.
     */
    protected static function booted(): void
    {
        $syncLegacy = function (Product $product) {
            // ترجمة الحقول الافتراضية إلى حقول قاعدة البيانات الفعلية
            if (isset($product->attributes['unit']) && isset($product->attributes['quantity_virtual'])) {
                if ($product->attributes['unit'] === 'piece') {
                    $product->StockByUnit = $product->attributes['quantity_virtual'];
                } elseif ($product->attributes['unit'] === 'gram') {
                    $product->StockByWeight = $product->attributes['quantity_virtual'];
                }
            }
        };

        static::saving($syncLegacy); // نستخدم 'saving' لتشمل عمليتي الإنشاء والتحديث
    }

    /*
    |--------------------------------------------------------------------------
    | العلاقات (Relationships)
    |--------------------------------------------------------------------------
    */

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'CategoryID', 'CategoryID');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'CurrencyID', 'CurrencyID');
    }

    public function inventoryLogs(): HasMany
    {
        return $this->hasMany(InventoryLog::class, 'ProductID', 'ProductID');
    }
}
