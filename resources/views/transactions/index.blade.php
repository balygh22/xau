@extends('layouts.app')
@section('title','المعاملات')
@section('content')
<div class="page-header d-flex align-items-center justify-content-between" style="background:linear-gradient(135deg,#8b5cf6,#6d28d9);color:#fff;padding:16px;border-radius:12px">
    <h1 style="margin:0"><i class="fas fa-file-invoice"></i> المعاملات (الفواتير)</h1>
    <div>
        <a href="{{ route('transactions.create.sale') }}" class="btn btn-light me-2"><i class="fas fa-cart-plus"></i> فاتورة بيع جديدة</a>
        <a href="{{ route('transactions.create.purchase') }}" class="btn btn-light"><i class="fas fa-truck-loading"></i> فاتورة شراء جديدة</a>
    </div>
</div>

<div class="card mt-3" style="border-radius:12px;border:1px solid #e5e7eb">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>رقم الفاتورة</th>
                        <th>التاريخ</th>
                        <th>النوع</th>
                        <th>الحساب</th>
                        <th>الإجمالي</th>
                        <th>المدفوع</th>
                        <th>المتبقي</th>
                        <th>حالة الدفع</th>
                        <th>المستخدم</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $t)
                        @php
                          $remaining = (float)$t->TotalAmount - (float)$t->PaidAmount;
                        @endphp
                        <tr>
                            <td>{{ $t->TransactionNumber }}</td>
                            <td>{{ optional($t->TransactionDate)->format('Y-m-d H:i') }}</td>
                            <td><span class="badge bg-secondary">{{ $t->type_label }}</span></td>
                            <td>{{ $t->account->AccountName ?? '' }}</td>
                            <td>{{ number_format($t->TotalAmount, 2) }}</td>
                            <td>{{ number_format($t->PaidAmount, 2) }}</td>
                            <td>{{ number_format($remaining, 2) }}</td>
                            <td><span class="badge bg-{{ $t->payment_badge_class }}">{{ $t->payment_status }}</span></td>
                            <td>{{ $t->user->name ?? '' }}</td>
                            <td class="d-flex gap-2">
                                <a href="{{ route('transactions.show',$t->TransactionID) }}" class="btn btn-sm btn-outline-dark">عرض/طباعة</a>
                                @if($remaining <= 0.00001)
                                  <button class="btn btn-sm btn-outline-success" disabled title="الفاتورة مدفوعة بالكامل">إضافة دفعة</button>
                                @else
                                  <a href="{{ route('payments.receipt.create', ['transaction_id'=>$t->TransactionID]) }}" class="btn btn-sm btn-outline-success">إضافة دفعة</a>
                                @endif
                                @if(in_array($t->TransactionType, ['Sale','Purchase']))
                                  <a href="{{ route('transactions.return.create', $t->TransactionID) }}" class="btn btn-sm btn-outline-warning">إنشاء مرتجع</a>
                                @endif
                                <form method="POST" action="{{ route('transactions.destroy', $t->TransactionID) }}" class="d-inline" onsubmit="return confirm('هل تريد حذف هذه الفاتورة؟ سيتم عكس أثر المخزون.');">
                                  @csrf
                                  @method('DELETE')
                                  <button type="submit" class="btn btn-sm btn-outline-danger">حذف</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="text-center text-muted">لا توجد معاملات</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $transactions->links() }}</div>
    </div>
</div>
@endsection
