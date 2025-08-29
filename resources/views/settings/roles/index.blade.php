@extends('layouts.app')
@section('title','إدارة المجموعات والصلاحيات')

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
        <h1 style="margin:0"><i class="fas fa-user-shield" style="margin-left:8px"></i> إدارة المجموعات والصلاحيات</h1>
        <a class="btn-gold" href="{{ route('settings.roles.create') }}">
            <i class="fas fa-plus"></i> إضافة مجموعة جديدة
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width:15%">المعرف</th>
                    <th style="width:55%">اسم المجموعة</th>
                    <th style="width:30%">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($roles as $role)
                    <tr>
                        <td>{{ $role->GroupID }}</td>
                        <td class="fw-bold">{{ $role->GroupName }}</td>
                        <td>
                            <a href="{{ route('settings.roles.edit', $role) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-cog"></i> تعديل الصلاحيات
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted">لا توجد مجموعات حتى الآن.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection