@extends('layouts.app')
@section('title', 'إضافة منتج جديد')
@section('content')
<div class="page-header" style="background:linear-gradient(135deg,#10b981,#047857);color:#fff;padding:16px;border-radius:12px">
    <h1 style="margin:0"><i class="fas fa-plus-circle" style="margin-left:8px"></i> إضافة منتج جديد</h1>
</div>

<div class="card mt-3" style="border-radius:12px;border:1px solid #e5e7eb">
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('inventory.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">اسم المنتج</label>
                    <input type="text" name="ProductName" class="form-control" value="{{ old('ProductName') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">كود المنتج (SKU) - اختياري</label>
                    <input type="text" name="ProductCode" class="form-control" value="{{ old('ProductCode') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">الفئة</label>
                    <select name="CategoryID" class="form-select" required>
                        <option value="">— اختر —</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->CategoryID }}" @selected(old('CategoryID') == $c->CategoryID)>{{ $c->CategoryName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">العيار</label>
                    <select name="Purity" class="form-select" required>
                        <option value="">— اختر —</option>
                        @foreach($karats as $k)
                            <option value="{{ $k }}" @selected(old('Purity') == $k)>{{ $k }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">وزن الذهب (جم)</label>
                    <input type="number" name="GoldWeight" step="0.001" min="0" class="form-control" value="{{ old('GoldWeight') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">وزن الأحجار (جم)</label>
                    <input type="number" name="StoneWeight" step="0.001" min="0" class="form-control" value="{{ old('StoneWeight') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">أجرة الصنعة</label>
                    <input type="number" name="LaborCost" step="0.01" min="0" class="form-control" value="{{ old('LaborCost', 0) }}">
                    <small class="text-muted">هذا الرقم بدون عملة، سيتم تحديد العملة في الفاتورة.</small>
                </div>

                <div class="col-md-12">
                    <label class="form-label d-block">المخزون الابتدائي (رصيد افتتاحي)</label>
                    <div class="row g-2">
                        <div class="col">
                            <input type="number" name="StockByUnit" step="1" min="0" class="form-control" placeholder="عدد القطع" value="{{ old('StockByUnit', 0) }}">
                        </div>
                        <div class="col">
                            <input type="number" name="StockByWeight" step="0.001" min="0" class="form-control" placeholder="الوزن بالجرام" value="{{ old('StockByWeight', 0) }}">
                        </div>
                    </div>
                </div>

                {{-- تم حذف حقل الصورة مؤقتاً لعدم وجود عمود له في قاعدة البيانات --}}
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4">
                <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-right"></i> رجوع</a>
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> حفظ</button>
            </div>
        </form>
    </div>
</div>
@endsection
