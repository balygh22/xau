@extends('layouts.app')
@section('title','تعديل منتج')
@section('content')
<div class="page-header" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);color:#fff;padding:16px;border-radius:12px">
  <h1 style="margin:0"><i class="fas fa-pen-to-square" style="margin-left:8px"></i> تعديل منتج</h1>
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

    <form method="POST" action="{{ route('inventory.update', $product) }}">
      @csrf
      @method('PUT')

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">اسم المنتج</label>
          <input type="text" name="ProductName" class="form-control" value="{{ old('ProductName', $product->ProductName) }}" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">كود المنتج</label>
          <input type="text" name="ProductCode" class="form-control" value="{{ old('ProductCode', $product->ProductCode) }}" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">الفئة</label>
          <select name="CategoryID" class="form-select" required>
            <option value="">— اختر —</option>
            @foreach($categories as $c)
              <option value="{{ $c->CategoryID }}" @selected(old('CategoryID',$product->CategoryID)==$c->CategoryID)>{{ $c->CategoryName }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-3">
          <label class="form-label">العيار</label>
          <select name="Purity" class="form-select" required>
            @foreach($karats as $k)
              <option value="{{ $k }}" @selected(old('Purity',$product->Purity)==$k)>{{ $k }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">أجرة الصنعة</label>
          <input type="number" step="0.01" name="LaborCost" class="form-control" value="{{ old('LaborCost', $product->LaborCost) }}">
        </div>

        <div class="col-md-3">
          <label class="form-label">المخزون بالوحدة</label>
          <input type="number" step="1" min="0" name="StockByUnit" class="form-control" value="{{ old('StockByUnit', $product->StockByUnit) }}">
        </div>
        <div class="col-md-3">
          <label class="form-label">المخزون بالوزن (جم)</label>
          <input type="number" step="0.001" min="0" name="StockByWeight" class="form-control" value="{{ old('StockByWeight', $product->StockByWeight) }}">
        </div>
        <div class="col-md-3">
          <label class="form-label">وزن الذهب (جم)</label>
          <input type="number" step="0.001" min="0" name="GoldWeight" class="form-control" value="{{ old('GoldWeight', $product->GoldWeight) }}">
        </div>
        <div class="col-md-3">
          <label class="form-label">وزن الأحجار (جم)</label>
          <input type="number" step="0.001" min="0" name="StoneWeight" class="form-control" value="{{ old('StoneWeight', $product->StoneWeight) }}">
        </div>
      </div>

      <div class="d-flex justify-content-between align-items-center mt-4">
        <a href="{{ route('inventory.show', $product) }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-right"></i> رجوع</a>
        <div class="d-flex gap-2">
          <button class="btn btn-primary"><i class="fas fa-save"></i> حفظ</button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection