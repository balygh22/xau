@extends('layouts.app')
@section('title', 'المخزون')
@section('content')
<div class="page-header" style="background:linear-gradient(135deg,#10b981,#047857);color:#fff;padding:16px;border-radius:12px">
    <h1 style="margin:0"><i class="fas fa-boxes-stacked" style="margin-left:8px"></i> إدارة المخزون</h1>
</div>
<div class="mt-3" style="background:#fff;border-radius:12px;border:1px solid #e5e7eb;padding:16px">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <form method="GET" action="{{ route('inventory.index') }}" class="d-flex gap-2">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="ابحث باسم أو كود المنتج">
            <button class="btn btn-outline-success"><i class="fas fa-search"></i></button>
        </form>
        <a class="btn btn-success" href="{{ route('inventory.create') }}"><i class="fas fa-plus"></i> منتج جديد</a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    {{-- <th>الصورة</th> --}} {{-- تم التعطيل مؤقتاً لعدم وجود حقل للصورة في قاعدة البيانات --}}
                    <th>الاسم</th>
                    <th>الكود</th>
                    <th>الفئة</th>
                    <th class="text-center">المخزون بالوحدة</th>
                    <th class="text-end">المخزون بالوزن (جم)</th>
                    <th>العيار</th>
                    <th class="text-end">أجرة الصنعة</th>
                    {{-- <th>العملة</th> --}} {{-- تم التعطيل لعدم وجود علاقة --}}
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse($products as $p)
                <tr>
                    {{-- تم التحديث هنا --}}
                    <td>{{ $p->ProductID }}</td>
                    {{-- <td>
                        @if($p->photo_path)
                            <img src="{{ Storage::disk('public')->url($p->photo_path) }}" class="rounded" style="width:48px;height:48px;object-fit:cover" alt="img">
                        @else
                            <div class="text-muted">—</div>
                        @endif
                    </td> --}}
                    {{-- تم التحديث هنا --}}
                    <td><a href="{{ route('inventory.show', $p) }}">{{ $p->ProductName }}</a></td>
                    {{-- تم التحديث هنا --}}
                    <td>{{ $p->ProductCode }}</td>
                    {{-- العلاقة مع الفئة صحيحة --}}
                    <td>{{ $p->category->CategoryName ?? '—' }}</td>
                    {{-- تم التحديث هنا: عرض المخزون بالقطعة --}}
                    <td class="text-center">{{ $p->StockByUnit }}</td>
                    {{-- تم التحديث هنا: عرض المخزون بالوزن --}}
                    <td class="text-end">{{ number_format($p->StockByWeight, 3) }}</td>
                    {{-- تم التحديث هنا --}}
                    <td>{{ $p->Purity }}</td>
                    {{-- تم التحديث هنا --}}
                    <td class="text-end">{{ number_format((float)$p->LaborCost, 2) }}</td>
                    {{-- <td>—</td> --}} {{-- لا يوجد حقل للعملة في جدول المنتجات --}}
                    <td>
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('inventory.show', $p) }}"><i class="fas fa-eye"></i></a>
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('inventory.edit', $p) }}"><i class="fas fa-pen"></i></a>
                        <form method="POST" action="{{ route('inventory.destroy', $p) }}" class="d-inline" onsubmit="return confirm('حذف المنتج؟');">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="11" class="text-center text-muted">لا توجد منتجات</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $products->withQueryString()->links() }}
    </div>
</div>
@endsection
