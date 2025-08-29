@extends('layouts.app')
@section('title', 'تفاصيل المنتج')
@section('content')
<div class="page-header" style="background:linear-gradient(135deg,#10b981,#047857);color:#fff;padding:16px;border-radius:12px">
    <h1 style="margin:0"><i class="fas fa-ring" style="margin-left:8px"></i> {{ $product->ProductName }}</h1>
</div>

<div class="row mt-3">
    <div class="col-lg-4">
        <div class="card" style="border-radius:12px;border:1px solid #e5e7eb">
            <div class="card-body">
                <div class="text-center mb-3">
                    @if($product->photo_path)
                        <img src="{{ Storage::disk('public')->url($product->photo_path) }}" class="img-fluid rounded" alt="product">
                    @else
                        <div class="text-muted">لا توجد صورة</div>
                    @endif
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between"><span>الرمز</span><strong>{{ $product->ProductCode }}</strong></li>
                    <li class="list-group-item d-flex justify-content-between"><span>الفئة</span><strong>{{ $product->category->CategoryName ?? '—' }}</strong></li>
                    <li class="list-group-item d-flex justify-content-between"><span>المخزون بالقطعة</span><strong>{{ $product->StockByUnit }}</strong></li>
                    <li class="list-group-item d-flex justify-content-between"><span>المخزون بالوزن (جم)</span><strong>{{ number_format((float)$product->StockByWeight,3) }}</strong></li>
                    <li class="list-group-item d-flex justify-content-between"><span>العيار</span><strong>{{ $product->Purity }}</strong></li>
                    <li class="list-group-item d-flex justify-content-between"><span>وزن الذهب</span><strong>{{ number_format((float)$product->GoldWeight,3) }}</strong></li>
                    <li class="list-group-item d-flex justify-content-between"><span>وزن الأحجار</span><strong>{{ number_format((float)$product->StoneWeight,3) }}</strong></li>
                    <li class="list-group-item d-flex justify-content-between"><span>أجرة الصنعة</span><strong>{{ number_format((float)$product->LaborCost,2) }}</strong></li>
                    <li class="list-group-item d-flex justify-content-between"><span>العملة</span><strong>{{ $product->currency->CurrencyCode ?? '—' }}</strong></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card" style="border-radius:12px;border:1px solid #e5e7eb">
            <div class="card-body">
                <h5 class="mb-3"><i class="fas fa-list"></i> سجل الحركات</h5>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>التاريخ</th>
                                <th>النوع</th>
                                <th class="text-end">التغير</th>
                                <th>مرجع</th>
                                <th>ملاحظة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php($movements = $product->movements ?? collect())
                            @forelse($movements as $m)
                                <tr>
                                    <td>{{ $m->created_at }}</td>
                                    <td>
                                        @switch($m->type)
                                            @case('purchase') شراء @break
                                            @case('sale') بيع @break
                                            @case('return') إرجاع @break
                                            @case('adjustment') تسوية @break
                                            @default —
                                        @endswitch
                                    </td>
                                    <td class="text-end {{ ($m->quantity_change ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $m->quantity_change ?? 0 }}
                                    </td>
                                    <td>{{ $m->reference ?? '—' }}</td>
                                    <td>{{ $m->note ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">لا توجد حركات</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary mt-2"><i class="fas fa-arrow-right"></i> رجوع</a>
            </div>
        </div>
    </div>
</div>
@endsection