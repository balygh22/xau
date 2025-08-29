@extends('layouts.app')
@section('title','إدارة فئات المنتجات')

@push('head')
<style>
    .page-header {background:linear-gradient(135deg,#d4af37,#997515);color:#0f172a;padding:16px;border-radius:12px;margin-bottom:12px}
    .card {background:#0f1729;border:1px solid rgba(212,175,55,.25);border-radius:12px;padding:16px;color:#e5e7eb}
    .btn-gold {background:linear-gradient(135deg,#d4af37,#b8941f);color:#0f172a;border:none;border-radius:999px;padding:8px 14px;font-weight:700}
    .table thead th{background:rgba(240,215,140,.3);color:#0f172a;border-bottom-color:rgba(212,175,55,.3)}
</style>
@endpush

@section('content')
<div class="page-header">
    <div style="display:flex;align-items:center;justify-content:space-between">
        <h1 style="margin:0"><i class="fas fa-tags" style="margin-left:8px"></i> إدارة فئات المنتجات</h1>
        <a class="btn-gold" href="{{ route('settings.categories.create') }}">
            <i class="fas fa-plus"></i> إضافة فئة جديدة
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
                    <th style="width:70%">اسم الفئة</th>
                    <th style="width:30%">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $cat)
                    <tr>
                        <td class="fw-bold">{{ $cat->CategoryName }}</td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('settings.categories.edit', $cat) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> تعديل</a>
                                <form method="POST" action="{{ route('settings.categories.destroy', $cat) }}" onsubmit="return confirm('تأكيد الحذف؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">لا توجد فئات حتى الآن.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection