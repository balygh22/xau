@extends('layouts.app')
@section('title', 'تقرير المبيعات')
@section('content')
<div class="page-header" style="background:linear-gradient(135deg,#10b981,#059669);color:#fff;padding:16px;border-radius:12px">
    <h1 style="margin:0"><i class="fas fa-receipt" style="margin-left:8px"></i> تقرير المبيعات</h1>
</div>

<form method="get" class="mt-3" style="display:flex;gap:8px;align-items:center">
    <input type="date" name="from" value="{{ $from }}" class="form-control" style="max-width:200px">
    <input type="date" name="to" value="{{ $to }}" class="form-control" style="max-width:200px">
    <button class="btn btn-primary">تصفية</button>
</form>

<div class="table-responsive mt-3">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>التاريخ</th>
                <th>العملة</th>
                <th>عدد الفواتير</th>
                <th>الإجمالي</th>
            </tr>
        </thead>
        <tbody>
        @forelse($rows as $r)
            <tr>
                <td>{{ $r->d }}</td>
                <td>{{ $currencies[$r->CurrencyID] ?? $r->CurrencyID }}</td>
                <td>{{ number_format($r->cnt) }}</td>
                <td>{{ number_format($r->total, 2) }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center">لا توجد بيانات</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection