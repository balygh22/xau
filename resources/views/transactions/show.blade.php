@extends('layouts.app')
@section('title', $trx->type_label . ' ' . ($trx->TransactionNumber ?? ('#'.$trx->TransactionID)))

@push('head')
<style>
  .invoice-container {
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    background: #fff;
  }
  .invoice-header {
    background: linear-gradient(135deg, #8b5cf6, #6d28d9);
    color: #fff;
    padding: 16px;
    border-radius: 12px;
  }
  .badge-type { font-size: 0.9rem; }
  .print-hidden { display: inline-flex; }
  @media print {
    body { background: #fff !important; }
    .sidebar-container, .sidebar-open-btn, .print-hidden, .alert { display: none !important; }
    .main-content { margin: 0 !important; padding: 0 !important; }
    .invoice-container { border: none !important; }
    a[href]:after { content: "" !important; }
  }
</style>
@endpush

@section('content')
<div class="invoice-header d-flex justify-content-between align-items-center">
  <div>
    <h1 class="m-0">
      <i class="fas fa-file-invoice"></i>
      {{ $trx->type_label }}
    </h1>
    <div class="mt-2">
      <span class="me-3"><strong>رقم:</strong> {{ $trx->TransactionNumber ?? ('#'.$trx->TransactionID) }}</span>
      <span class="me-3"><strong>التاريخ:</strong> {{ optional($trx->TransactionDate)->format('Y-m-d H:i') }}</span>
      <span class="badge badge-type bg-{{ $trx->payment_badge_class }}">{{ $trx->payment_status }}</span>
    </div>
  </div>
  <div class="print-hidden">
    <a href="{{ route('transactions.index') }}" class="btn btn-light me-2"><i class="fas fa-arrow-right"></i> رجوع</a>
    <button class="btn btn-dark" onclick="window.print()"><i class="fas fa-print"></i> طباعة</button>
  </div>
</div>

<div class="invoice-container card mt-3">
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-6">
        <div class="border rounded p-3 h-100">
          <div class="fw-bold mb-2">الطرف</div>
          <div><strong>الحساب:</strong> {{ $trx->account->AccountName ?? '' }}</div>
          <div><strong>المستخدم:</strong> {{ $trx->user->name ?? '' }}</div>
          @if(!empty($trx->OriginalTransactionID))
            <div class="mt-1"><strong>مرتبط بالأصل:</strong> {{ $trx->OriginalTransactionID }}</div>
          @endif
        </div>
      </div>
      <div class="col-md-6">
        <div class="border rounded p-3 h-100">
          <div class="fw-bold mb-2">تفاصيل</div>
          <div><strong>العملة:</strong> {{ $trx->currency->CurrencyName ?? '' }}</div>
          <div><strong>ملاحظات:</strong> {{ $trx->Notes ?: '—' }}</div>
        </div>
      </div>
    </div>

    <div class="table-responsive mt-3">
      <table class="table table-sm align-middle table-bordered">
        <thead class="table-light">
          <tr>
            <th>الصنف</th>
            <th class="text-center" style="width:120px">الكمية</th>
            <th class="text-center" style="width:140px">سعر الوحدة</th>
            <th class="text-center" style="width:140px">الوزن</th>
            <th class="text-center" style="width:160px">الإجمالي</th>
          </tr>
        </thead>
        <tbody>
          @forelse($trx->details as $d)
            <tr>
              <td>
                <div class="fw-bold">{{ $d->product->ProductName ?? ('#'.$d->ProductID) }}</div>
              </td>
              <td class="text-center">{{ rtrim(rtrim(number_format((float)$d->Quantity, 3, '.', ''), '0'), '.') }}</td>
              <td class="text-center">{{ number_format((float)$d->UnitPrice, 2) }}</td>
              <td class="text-center">{{ rtrim(rtrim(number_format((float)($d->Weight ?? 0), 3, '.', ''), '0'), '.') }}</td>
              <td class="text-center">{{ number_format((float)$d->LineTotal, 2) }}</td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center text-muted">لا توجد أسطر</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @php
      $total = (float)($trx->TotalAmount ?? 0);
      $paid  = (float)($trx->PaidAmount ?? 0);
      $remaining = $total - $paid;
    @endphp

    <div class="row g-3 mt-3">
      <div class="col-lg-6 order-lg-2">
        <div class="border rounded p-3">
          <div class="d-flex justify-content-between">
            <div>الإجمالي</div>
            <div class="fw-bold">{{ number_format($total, 2) }}</div>
          </div>
          <div class="d-flex justify-content-between">
            <div>المدفوع</div>
            <div class="fw-bold">{{ number_format($paid, 2) }}</div>
          </div>
          <hr class="my-2">
          <div class="d-flex justify-content-between">
            <div>المتبقي</div>
            <div class="fw-bold {{ $remaining <= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($remaining, 2) }}</div>
          </div>
        </div>
      </div>
      <div class="col-lg-6 order-lg-1">
        <div class="border rounded p-3 h-100">
          <div class="fw-bold mb-2">توقيع</div>
          <div style="height:80px;border:1px dashed #ddd;border-radius:8px"></div>
        </div>
      </div>
    </div>

    <div class="text-muted small mt-3">
      تم إنشاء المستند بواسطة نظام المتجر — التاريخ: {{ now()->format('Y-m-d H:i') }}
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  // يمكن لاحقًا إضافة طباعة تلقائية عند فتح الصفحة عبر بارامتر query
  // مثال: if (new URLSearchParams(location.search).get('print')==='1') window.print();
</script>
@endpush