@extends('layouts.app')
@section('title','كشف حساب')
@section('content')
<div class="page-header" style="background:linear-gradient(135deg,#0ea5e9,#0369a1);color:#fff;padding:16px;border-radius:12px">
    <h1 style="margin:0"><i class="fas fa-file-invoice-dollar" style="margin-left:8px"></i> كشف حساب</h1>
</div>

<div class="card mt-3" style="border-radius:12px;border:1px solid #e5e7eb">
    <div class="card-body">
        <h4 class="mb-1">{{ $account->name }}</h4>
        <div class="text-muted mb-3">نوع الحساب: {{ $account->type_label }} | المعرّف: {{ $account->identifier ?? '-' }}</div>

        <div class="row g-3">
            @forelse($account->balances as $bal)
                @php
                  $curCode = $bal->currency->code ?? $bal->currency->CurrencyCode ?? '#';
                  $curBal = $bal->current_balance ?? $bal->CurrentBalance ?? 0;
                @endphp
                <div class="col-md-4">
                    <div class="p-3 border rounded" style="background:#f8fafc">
                        <div class="small text-muted">رصيد {{ $curCode }}</div>
                        <div class="fs-4 fw-bold">{{ number_format((float)$curBal, 2) }}</div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center text-muted">لا توجد أرصدة بعد.</div>
            @endforelse
        </div>

        <hr>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>التاريخ</th>
                        <th>النوع</th>
                        <th>الرقم</th>
                        <th>الوصف</th>
                        <th class="text-end">المبلغ</th>
                        <th>رابط</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entries as $e)
                        <tr>
                            <td>{{ optional($e['date'])->format('Y-m-d H:i') }}</td>
                            <td>
                                @if($e['kind']==='transaction')
                                    <span class="badge bg-secondary">{{ $e['label'] }}</span>
                                @else
                                    <span class="badge bg-info">{{ $e['label'] }}</span>
                                @endif
                            </td>
                            <td>{{ $e['number'] ?? '-' }}</td>
                            <td>{{ $e['description'] ?? '-' }}</td>
                            <td class="text-end">{{ number_format($e['amount'] ?? 0, 2) }}</td>
                            <td><a href="{{ $e['link'] }}" class="btn btn-sm btn-outline-dark">عرض</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">لا توجد حركات</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <a href="{{ route('accounts.index') }}" class="btn btn-outline-secondary">عودة</a>
    </div>
</div>
@endsection