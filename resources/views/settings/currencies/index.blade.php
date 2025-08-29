@extends('layouts.app')
@section('title','إدارة العملات')

@push('head')
<style>
    .page-header {background:linear-gradient(135deg,#d4af37,#997515);color:#0f172a;padding:16px;border-radius:12px;margin-bottom:12px}
    .card {background:#0f1729;border:1px solid rgba(212,175,55,.25);border-radius:12px;padding:16px;color:#e5e7eb}
    .btn-gold {background:linear-gradient(135deg,#d4af37,#b8941f);color:#0f172a;border:none;border-radius:999px;padding:8px 14px;font-weight:700}
    .table thead th{background:rgba(240,215,140,.3);color:#0f172a;border-bottom-color:rgba(212,175,55,.3)}
    .badge-default{background:rgba(16,185,129,.15);color:#10b981;border:1px solid rgba(16,185,129,.3)}
</style>
@endpush

@section('content')
<div class="page-header">
    <div style="display:flex;align-items:center;justify-content:space-between">
        <h1 style="margin:0"><i class="fas fa-money-bill-wave" style="margin-left:8px"></i> إدارة العملات</h1>
        <a class="btn-gold" href="{{ route('settings.currencies.create') }}">
            <i class="fas fa-plus"></i> إضافة عملة جديدة
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if ($errors->any())
    <div class="alert alert-danger">
        <ul style="margin:0; padding-right:18px">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width:20%">الرمز</th>
                    <th style="width:50%">اسم العملة</th>
                    <th style="width:15%">افتراضية</th>
                    <th style="width:15%">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($currencies as $c)
                    <tr>
                        {{-- تم التحديث هنا --}}
                        <td class="fw-bold">{{ $c->CurrencyCode }}</td>
                        {{-- تم التحديث هنا --}}
                        <td>{{ $c->CurrencyName }}</td>
                        <td>
                            {{-- تم التحديث هنا --}}
                            @if($c->IsDefault)
                                <span class="badge badge-default">افتراضية</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                {{-- لا تحتاج هذه الأسطر لتعديل لأن الربط يتم بالمفتاح الأساسي الذي حددناه في النموذج --}}
                                <a href="{{ route('settings.currencies.edit', $c) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> تعديل</a>
                                <form method="POST" action="{{ route('settings.currencies.destroy', $c) }}" onsubmit="return confirm('تأكيد الحذف؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">لا توجد عملات حتى الآن.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
