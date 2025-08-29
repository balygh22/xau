<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Currency;
use App\Models\Category;
// use App\Models\InventoryLog; // تم التعطيل لعدم توفر الموديل حالياً
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InventoryController extends Controller
{
    public function index()
    {
        $q = request('q');
        $products = Product::with(['category'])
            ->when($q, function ($query) use ($q) {
                $query->where('ProductName', 'like', "%$q%")
                      ->orWhere('ProductCode', 'like', "%$q%");
            })
            ->orderByDesc('ProductID')
            ->paginate(15);

        return view('inventory.index', compact('products'));
    }

    public function create()
    {
        // تم حذف $currencies من هنا لأنها لم تعد مطلوبة في هذه الواجهة
        $categories = Category::orderBy('CategoryName')->get(['CategoryID', 'CategoryName']);
        $karats = ['24', '22', '21', '18', '14'];
        
        // تم حذف $units لأن الواجهة الجديدة لا تستخدمها
        
        return view('inventory.create', compact('categories', 'karats'));
    }

    public function store(Request $request)
    {
        // =================== تم التحديث هنا ===================
        // تم حذف CurrencyID من قواعد التحقق
        $data = $request->validate([
            'ProductName' => ['required', 'string', 'max:255'],
            'ProductCode' => ['nullable', 'string', 'max:50', 'unique:products,ProductCode'],
            'CategoryID' => ['required', 'integer', 'exists:categories,CategoryID'],
            'GoldWeight' => ['nullable', 'numeric', 'min:0'],
            'Purity' => ['required', 'string', 'in:24,22,21,18,14'],
            'StoneWeight' => ['nullable', 'numeric', 'min:0'],
            'LaborCost' => ['nullable', 'numeric', 'min:0'],
            'StockByUnit' => ['nullable', 'integer', 'min:0'],
            'StockByWeight' => ['nullable', 'numeric', 'min:0'],
        ], [], [
            'ProductName' => 'اسم المنتج',
            'ProductCode' => 'كود المنتج',
            'CategoryID' => 'الفئة',
            'GoldWeight' => 'وزن الذهب',
            'Purity' => 'العيار',
            'StoneWeight' => 'وزن الأحجار',
            'LaborCost' => 'أجرة الصنعة',
            'StockByUnit' => 'المخزون بالقطعة',
            'StockByWeight' => 'المخزون بالوزن',
        ]);

        if (empty($data['ProductCode'])) {
            $data['ProductCode'] = strtoupper(Str::slug(mb_substr($data['ProductName'], 0, 10))) . '-' . strtoupper(Str::random(6));
        }

        DB::transaction(function () use ($data) {
            // =================== تم التحديث هنا ===================
            // تم حذف CurrencyID من مصفوفة البيانات
            $productData = [
                'ProductName' => $data['ProductName'],
                'ProductCode' => $data['ProductCode'],
                'CategoryID' => $data['CategoryID'],
                'GoldWeight' => $data['GoldWeight'] ?? 0.000,
                'Purity' => $data['Purity'],
                'StoneWeight' => $data['StoneWeight'] ?? 0.000,
                'LaborCost' => $data['LaborCost'] ?? 0.00,
                'StockByUnit' => $data['StockByUnit'] ?? 0,
                'StockByWeight' => $data['StockByWeight'] ?? 0.000,
            ];

            Product::create($productData);

            // يمكنك تفعيل سجل الحركات هنا لاحقًا
        });

        return redirect()->route('inventory.index')->with('success', 'تمت إضافة المنتج بنجاح');
    }

    public function show(Product $product)
    {
        $product->load(['category']);
        return view('inventory.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('CategoryName')->get(['CategoryID','CategoryName']);
        $karats = ['24','22','21','18','14'];
        return view('inventory.edit', compact('product','categories','karats'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'ProductName' => ['required','string','max:255'],
            'ProductCode' => ['required','string','max:50','unique:products,ProductCode,'.$product->ProductID.',ProductID'],
            'CategoryID' => ['required','integer','exists:categories,CategoryID'],
            'GoldWeight' => ['nullable','numeric','min:0'],
            'Purity' => ['required','string','in:24,22,21,18,14'],
            'StoneWeight' => ['nullable','numeric','min:0'],
            'LaborCost' => ['nullable','numeric','min:0'],
            'StockByUnit' => ['nullable','integer','min:0'],
            'StockByWeight' => ['nullable','numeric','min:0'],
        ]);

        $product->update($data);
        return redirect()->route('inventory.show', $product)->with('success','تم تحديث المنتج');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('inventory.index')->with('success','تم حذف المنتج');
    }
}
