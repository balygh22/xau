@extends('layouts.app')
@section('title','العملات')

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
        <button class="btn-gold" data-bs-toggle="modal" data-bs-target="#addCurrencyModal">
            <i class="fas fa-plus"></i> إضافة عملة جديدة
        </button>
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
                    <th style="width:25%">الرمز</th>
                    <th style="width:55%">اسم العملة</th>
                    <th style="width:20%">افتراضية</th>
                </tr>
            </thead>
            <tbody>
                @forelse(($currencies ?? []) as $c)
                    <tr>
                        <td class="fw-bold">{{ $c->code }}</td>
                        <td>{{ $c->name }}</td>
                        <td>
                            @if($c->is_default)
                                <span class="badge badge-default">افتراضية</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted">لا توجد عملات حتى الآن.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Add Currency -->
<div class="modal fade" id="addCurrencyModal" tabindex="-1" aria-labelledby="addCurrencyLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content" style="background:#0f1729;color:#e5e7eb;border:1px solid rgba(212,175,55,.25)">
      <div class="modal-header" style="border-bottom-color:rgba(212,175,55,.2)">
        <h5 class="modal-title" id="addCurrencyLabel"><i class="fas fa-plus-circle" style="margin-left:8px"></i> إضافة عملة</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="{{ route('settings.currencies.store') }}">
        @csrf
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">اسم العملة</label>
                <input type="text" name="name" class="form-control" placeholder="مثال: ريال يمني" value="{{ old('name') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">رمز العملة</label>
                <input type="text" name="code" class="form-control" placeholder="مثال: YER" value="{{ old('code') }}" maxlength="5" required>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="1" id="is_default" name="is_default" {{ old('is_default') ? 'checked' : '' }}>
                <label class="form-check-label" for="is_default">تعيين كعملة افتراضية</label>
            </div>
        </div>
        <div class="modal-footer" style="border-top-color:rgba(212,175,55,.2)">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
          <button type="submit" class="btn-gold"><i class="fas fa-save"></i> حفظ</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection