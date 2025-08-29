@extends('layouts.app')
@section('title', 'المدفوعات')
@section('content')
<div class="page-header" style="background:linear-gradient(135deg,#22c55e,#16a34a);color:#fff;padding:16px;border-radius:12px">
    <h1 style="margin:0"><i class="fas fa-money-bill-wave" style="margin-left:8px"></i> المدفوعات</h1>
</div>

<div class="d-flex justify-content-between align-items-center mt-3">
    <div>
        <a href="{{ route('payments.receipt.create') }}" class="btn btn-success me-2"><i class="fas fa-hand-holding-usd"></i> سند قبض</a>
        <a href="{{ route('payments.disbursement.create') }}" class="btn btn-warning me-2"><i class="fas fa-cash-register"></i> سند صرف</a>
        <a href="{{ route('payments.transfer.create') }}" class="btn btn-primary"><i class="fas fa-exchange-alt"></i> تحويل داخلي</a>
    </div>
</div>

<div class="card mt-3" style="border-radius:12px;border:1px solid #e5e7eb">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>رقم الإيصال</th>
                        <th>التاريخ</th>
                        <th>العملية</th>
                        <th>الوصف</th>
                        <th>المبلغ</th>
                        <th>العملة</th>
                        <th>المستخدم</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $p)
                        <tr>
                            <td>{{ $p->payment_number }}</td>
                            <td>{{ optional($p->payment_date)->format('Y-m-d H:i') }}</td>
                            <td>
                                <span class="badge bg-{{ $p->type_badge_class }}">
                                    <i class="{{ $p->type_icon }}"></i>
                                    {{ $p->type_label }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $from = $p->fromAccount->AccountName ?? '';
                                    $to = $p->toAccount->AccountName ?? '';
                                @endphp
                                {{ $p->description ?? "حركة من [$from] إلى [$to]" }}
                            </td>
                            <td>{{ number_format($p->amount, 2) }}</td>
                            <td>{{ $p->currency->name ?? $p->currency->CurrencyName ?? '' }}</td>
                            <td>{{ $p->user->name ?? '' }}</td>
                            <td>
                                <form method="POST" action="{{ route('payments.destroy', $p->PaymentID) }}" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف السند؟ سيتم عكس تأثيره.');">
                                      @csrf
                                      @method('DELETE')
                                      <button type="submit" class="btn btn-sm btn-outline-danger">حذف</button>
                                    </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">لا توجد مدفوعات بعد</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $payments->links() }}</div>
    </div>
</div>
@endsection