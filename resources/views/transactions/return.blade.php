@extends('layouts.app')
@section('title', $type==='SaleReturn' ? 'مرتجع بيع' : 'مرتجع شراء')
@section('content')
<div class="page-header d-flex align-items-center justify-content-between" style="background:linear-gradient(135deg,#f59e0b,#b45309);color:#fff;padding:16px;border-radius:12px">
  <h1 style="margin:0">
    <i class="fas fa-undo-alt"></i>
    {{ $type==='SaleReturn' ? 'إنشاء مرتجع بيع' : 'إنشاء مرتجع شراء' }}
  </h1>
  <a href="{{ route('transactions.show',$original->TransactionID) }}" class="btn btn-light"><i class="fas fa-file-invoice"></i> الفاتورة الأصلية</a>
</div>

<div class="card mt-3" style="border-radius:12px;border:1px solid #e5e7eb">
  <div class="card-body">

    <div class="alert alert-info">
      <div><strong>الفاتورة الأصلية:</strong> {{ $original->TransactionNumber }} — {{ $original->type_label }} — {{ optional($original->TransactionDate)->format('Y-m-d H:i') }}</div>
      <div><strong>الحساب:</strong> {{ $original->account->AccountName ?? '' }}</div>
      <div><strong>العملة:</strong> {{ $original->currency->CurrencyName ?? '' }}</div>
      <div><strong>الإجمالي:</strong> {{ number_format($original->TotalAmount,2) }} — <strong>المدفوع:</strong> {{ number_format($original->PaidAmount,2) }}</div>
    </div>

    @if ($errors->any())
      <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ route('transactions.return.store',$original->TransactionID) }}">
      @csrf

      <div class="table-responsive">
        <table class="table table-sm align-middle">
          <thead>
            <tr>
              <th>المنتج</th>
              <th class="text-center">الكمية الأصل</th>
              <th class="text-center">الوزن الأصل</th>
              <th class="text-center">سعر الوحدة</th>
              <th class="text-center">كمية مرتجع</th>
              <th class="text-center">وزن مرتجع</th>
              <th class="text-center">الإجمالي للسطر</th>
            </tr>
          </thead>
          <tbody id="returnLines">
            @foreach($original->details as $i=>$d)
              <tr>
                <td>
                  <div class="fw-bold">{{ $d->product->ProductName ?? ('#'.$d->ProductID) }}</div>
                  <input type="hidden" name="lines[{{ $i }}][DetailID]" value="{{ $d->DetailID }}">
                </td>
                <td class="text-center">{{ $d->Quantity }}</td>
                <td class="text-center">{{ $d->Weight }}</td>
                <td class="text-center">
                  <input type="number" step="0.01" min="0" class="form-control form-control-sm text-center unit-price" name="lines[{{ $i }}][UnitPrice]" value="{{ $d->UnitPrice }}">
                </td>
                <td class="text-center">
                  <input type="number" step="0.001" min="0" class="form-control form-control-sm text-center qty" name="lines[{{ $i }}][Quantity]" value="0" max="{{ $d->Quantity }}">
                </td>
                <td class="text-center">
                  <input type="number" step="0.001" min="0" class="form-control form-control-sm text-center weight" name="lines[{{ $i }}][Weight]" value="0" max="{{ $d->Weight }}">
                </td>
                <td class="text-center line-total">0.00</td>
              </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr>
              <th colspan="6" class="text-end">الإجمالي:</th>
              <th class="text-center" id="grandTotal">0.00</th>
            </tr>
          </tfoot>
        </table>
      </div>

      <div class="row g-3 mt-3">
        <div class="col-md-8">
          <input type="text" name="notes" class="form-control" placeholder="ملاحظات (اختياري)">
        </div>
        <div class="col-md-4 form-check form-switch d-flex align-items-center">
          <input class="form-check-input" type="checkbox" id="create_cash_voucher" name="create_cash_voucher" value="1">
          <label class="form-check-label ms-2" for="create_cash_voucher">
            {{ $type==='SaleReturn' ? 'إنشاء سند صرف للعميل' : 'إنشاء سند قبض من المورد' }} (اختياري)
          </label>
        </div>
      </div>

      <div class="d-flex justify-content-between align-items-center mt-4">
        <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-right"></i> رجوع</a>
        <button type="submit" class="btn btn-warning"><i class="fas fa-undo"></i> حفظ المرتجع</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
(function(){
  const format2 = v => (Number(v||0)).toFixed(2);
  const tbody = document.getElementById('returnLines');
  const grand = document.getElementById('grandTotal');

  function recalc(){
    let g=0;
    tbody.querySelectorAll('tr').forEach(tr => {
      const qty = parseFloat(tr.querySelector('.qty')?.value || 0);
      const weight = parseFloat(tr.querySelector('.weight')?.value || 0);
      const price = parseFloat(tr.querySelector('.unit-price')?.value || 0);
      const lt = (qty>0?qty:0)*price + (weight>0?weight:0)*price;
      tr.querySelector('.line-total').textContent = format2(lt);
      g += lt;
    });
    grand.textContent = format2(g);
  }

  tbody.addEventListener('input', function(e){
    if(e.target.matches('.qty, .weight, .unit-price')) recalc();
  });

  recalc();
})();
</script>
@endpush
@endsection