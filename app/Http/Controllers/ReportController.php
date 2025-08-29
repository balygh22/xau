<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ReportController extends Controller
{
    // تقرير المبيعات (إجماليات حسب اليوم والعملة)
    public function sales(Request $request): View
    {
        $from = $request->query('from');
        $to   = $request->query('to');

        $q = DB::table('transactions')
            ->selectRaw("DATE(TransactionDate) as d, CurrencyID, SUM(TotalAmount) as total, COUNT(*) as cnt")
            ->whereIn('TransactionType', ['Sale','SaleReturn'])
            ->groupBy(DB::raw('DATE(TransactionDate)'), 'CurrencyID')
            ->orderBy('d','desc');

        if ($from) { $q->whereDate('TransactionDate', '>=', $from); }
        if ($to)   { $q->whereDate('TransactionDate', '<=', $to); }

        $rows = $q->get();

        // جلب أسماء العملات لعرضها
        $currencies = DB::table('currencies')->pluck('CurrencyName', 'CurrencyID');

        return view('reports.sales', compact('rows', 'currencies', 'from', 'to'));
    }

    // تقرير المخزون (إجماليات الوزن والعدد حسب الصنف)
    public function inventory(): View
    {
        $hasWeight = Schema::hasColumn('products', 'StockByWeight');

        $rows = DB::table('products')
            ->join('categories', 'products.CategoryID', '=', 'categories.CategoryID')
            ->select([
                'categories.CategoryName as category',
                DB::raw('SUM(products.StockByUnit) as units'),
            ])
            ->when($hasWeight, function($q){
                $q->addSelect(DB::raw('SUM(products.StockByWeight) as weight'));
            })
            ->groupBy('categories.CategoryName')
            ->orderBy('categories.CategoryName')
            ->get();

        return view('reports.inventory', compact('rows'));
    }
}