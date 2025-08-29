@extends('layouts.app')
@section('title', $type==='Sale' ? 'فاتورة بيع' : 'فاتورة شراء')
@section('content')
<div class="page-header" style="background:linear-gradient(135deg,#10b981,#047857);color:#fff;padding:16px;border-radius:12px">
    <h1 style="margin:0">
        <i class="fas fa-file-invoice-dollar"></i>
        {{ $type==='Sale' ? 'فاتورة بيع جديدة' : 'فاتورة شراء جديدة' }}
    </h1>
</div>

<div class="card mt-3" style="border-radius:12px;border:1px solid #e5e7eb">
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif

        <form method="POST" action="{{ route('transactions.store') }}">
            @csrf
            <input type="hidden" name="type" value="{{ $type }}"/>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">{{ $type==='Sale' ? 'العميل' : 'المورد' }}</label>
                    <select name="AccountID" class="form-select" required>
                        <option value="">— اختر —</option>
                        @foreach($accounts as $acc)
                          <option value="{{ $acc->AccountID }}">{{ $acc->AccountName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">التاريخ</label>
                    <input type="datetime-local" name="TransactionDate" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">العملة</label>
                    <select name="CurrencyID" class="form-select" required>
                        <option value="">— اختر —</option>
                        @foreach($currencies as $cur)
                          <option value="{{ $cur->CurrencyID }}" @selected( (old('CurrencyID') !== null) ? old('CurrencyID') == $cur->CurrencyID : ($cur->IsDefault ?? false) )>{{ $cur->CurrencyName }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label d-block">الأصناف</label>
                    <div id="itemsContainer">
                        <div class="row g-2 align-items-end item-row">
                            <div class="col-md-5">
                                <label class="form-label">المنتج</label>
                                <select name="items[0][ProductID]" class="form-select" required>
                                    <option value="">— اختر —</option>
                                    @foreach($products as $p)
                                      <option value="{{ $p->ProductID }}" data-stock-unit="{{ (float)($p->StockByUnit ?? 0) }}" data-stock-weight="{{ (float)($p->StockByWeight ?? 0) }}">{{ $p->ProductName }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text text-muted stock-hint d-none"></div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">الكمية</label>
                                <input type="number" step="0.001" min="0" name="items[0][Quantity]" class="form-control" required>
                                <div class="form-text text-danger qty-warning d-none"></div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">السعر</label>
                                <input type="number" step="0.01" min="0" name="items[0][UnitPrice]" class="form-control" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">وزن الذهب (اختياري)</label>
                                <input type="number" step="0.001" min="0" name="items[0][GoldWeight]" class="form-control">
                                <div class="form-text text-danger weight-warning d-none"></div>
                            </div>
                            <div class="col-md-1 d-grid">
                                <button type="button" class="btn btn-outline-danger remove-row" disabled>حذف</button>
                            </div>
                        </div>
                    </div>
                    <button type="button" id="addRowBtn" class="btn btn-sm btn-outline-primary mt-2"><i class="fas fa-plus"></i> إضافة صنف</button>
                </div>

                <div class="col-12 mt-3">
                    <label class="form-label">ملاحظات</label>
                    <input type="text" name="Notes" class="form-control" placeholder="اختياري">
                </div>

                <div class="col-12 mt-3">
                    <label class="form-label d-block">دفعة مقدّمة (اختياري)</label>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <input type="number" step="0.01" min="0" name="upfront[amount]" class="form-control" placeholder="المبلغ">
                        </div>
                        <div class="col-md-4">
                            <select name="upfront[cash_account_id]" class="form-select" >
                                <option value="">— اختر الصندوق/البنك —</option>
                                @foreach($cashAndBanks as $a)
                                  <option value="{{ $a->AccountID }}">{{ $a->AccountName }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

            </div>

            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted">
                    <strong>الإجمالي:</strong>
                    <span id="invoiceTotal">0.00</span>
                </div>
                <div>
                    <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-right"></i> رجوع</a>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> حفظ</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
(function(){
  let idx = 1;
  const itemsContainer = document.getElementById('itemsContainer');
  const totalEl = document.getElementById('invoiceTotal');

  function recalcTotal(){
    let total = 0;
    document.querySelectorAll('.item-row').forEach(row => {
      const qty = parseFloat(row.querySelector('input[name$="[Quantity]"]')?.value || '0');
      const price = parseFloat(row.querySelector('input[name$="[UnitPrice]"]')?.value || '0');
      total += (qty * price);
    });
    if (totalEl) totalEl.textContent = total.toFixed(2);
  }

  function checkRowStock(row){
    // يعمل فقط في فواتير البيع
    const type = document.querySelector('input[name="type"]').value;
    if (type !== 'Sale') return;
    const select = row.querySelector('select[name$="[ProductID]"]');
    const opt = select?.selectedOptions?.[0];
    const availableQty = parseFloat(opt?.dataset?.stockUnit || '0');
    const availableW = parseFloat(opt?.dataset?.stockWeight || '0');
    const qtyInput = row.querySelector('input[name$="[Quantity]"]');
    const wInput   = row.querySelector('input[name$="[GoldWeight]"]');
    const qty = parseFloat(qtyInput?.value || '0');
    const w   = parseFloat(wInput?.value || '0');

    const hint = row.querySelector('.stock-hint');
    const qtyWarn = row.querySelector('.qty-warning');
    const wWarn = row.querySelector('.weight-warning');

    // إظهار المتاح
    if (hint) {
      hint.textContent = `المتاح: كمية ${isNaN(availableQty)?0:availableQty} | وزن ${isNaN(availableW)?0:availableW}`;
      hint.classList.toggle('d-none', false);
    }

    // تحذير إذا تجاوز
    const overQty = qty > availableQty && !isNaN(qty) && !isNaN(availableQty);
    const overW   = w > availableW && !isNaN(w) && !isNaN(availableW);

    if (qtyWarn) {
      qtyWarn.textContent = overQty ? 'تجاوزت الكمية المتاحة في المخزون' : '';
      qtyWarn.classList.toggle('d-none', !overQty);
    }
    if (wWarn) {
      wWarn.textContent = overW ? 'تجاوزت الوزن المتاح في المخزون' : '';
      wWarn.classList.toggle('d-none', !overW);
    }

    // تعطيل زر الحفظ إن كان هناك أي تجاوز
    const hasOver = overQty || overW;
    document.querySelector('button[type="submit"]').disabled = hasOver;
  }

  // Recalc and stock check when inputs change
  itemsContainer.addEventListener('input', function(e){
    if (e.target.matches('input[name$="[Quantity]"]') || e.target.matches('input[name$="[UnitPrice]"]') || e.target.matches('input[name$="[GoldWeight]"]')) {
      recalcTotal();
      checkRowStock(e.target.closest('.item-row'));
    }
  });
  itemsContainer.addEventListener('change', function(e){
    if (e.target.matches('select[name$="[ProductID]"]')) {
      checkRowStock(e.target.closest('.item-row'));
    }
  });

  document.getElementById('addRowBtn').addEventListener('click', function(){
    const c = itemsContainer;
    const row = document.createElement('div');
    row.className = 'row g-2 align-items-end item-row mt-2';
    row.innerHTML = `
      <div class="col-md-5">
        <label class="form-label">المنتج</label>
        <select name="items[${idx}][ProductID]" class="form-select" required>
          <option value="">— اختر —</option>
          @foreach($products as $p)
            <option value="{{ $p->ProductID }}" data-stock-unit="{{ (float)($p->StockByUnit ?? 0) }}" data-stock-weight="{{ (float)($p->StockByWeight ?? 0) }}">{{ $p->ProductName }}</option>
          @endforeach
        </select>
        <div class="form-text text-muted stock-hint d-none"></div>
      </div>
      <div class="col-md-2">
        <label class="form-label">الكمية</label>
        <input type="number" step="0.001" min="0" name="items[${idx}][Quantity]" class="form-control" required>
        <div class="form-text text-danger qty-warning d-none"></div>
      </div>
      <div class="col-md-2">
        <label class="form-label">السعر</label>
        <input type="number" step="0.01" min="0" name="items[${idx}][UnitPrice]" class="form-control" required>
      </div>
      <div class="col-md-2">
        <label class="form-label">وزن الذهب (اختياري)</label>
        <input type="number" step="0.001" min="0" name="items[${idx}][GoldWeight]" class="form-control">
        <div class="form-text text-danger weight-warning d-none"></div>
      </div>
      <div class="col-md-1 d-grid">
        <button type="button" class="btn btn-outline-danger remove-row">حذف</button>
      </div>`;
    c.appendChild(row);
    idx++;
    recalcTotal();
  });

  itemsContainer.addEventListener('click', function(e){
    if(e.target.classList.contains('remove-row')) {
      const rows = document.querySelectorAll('.item-row');
      if (rows.length > 1) {
        e.target.closest('.item-row').remove();
        recalcTotal();
      }
    }
  });

  // Initial calc on page load
  recalcTotal();
})();
</script>
@endpush
@endsection
