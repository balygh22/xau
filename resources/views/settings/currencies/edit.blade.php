@extends('layouts.app')
@section('title','تعديل عملة')

@push('head')
<style>
    .page-header {background:linear-gradient(135deg,#d4af37,#997515);color:#0f172a;padding:16px;border-radius:12px;margin-bottom:12px}
    .card {background:#0f1729;border:1px solid rgba(212,175,55,.25);border-radius:12px;padding:16px;color:#e5e7eb;max-width:720px}
    .btn-gold {background:linear-gradient(135deg,#d4af37,#b8941f);color:#0f172a;border:none;border-radius:999px;padding:8px 14px;font-weight:700}
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 style="margin:0"><i class="fas fa-edit" style="margin-left:8px"></i> تعديل عملة</h1>
</div>

@if ($errors->any())
    <div class="alert alert-danger" style="max-width: 720px; margin-bottom: 1rem;">
        <ul style="margin:0; padding-right:18px">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    {{-- المسار صحيح ولا يحتاج تعديل لأن الربط يتم تلقائيًا --}}
    <form action="{{ route('settings.currencies.update', $currency) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="form-label">اسم العملة</label>
            {{-- تم التحديث هنا: name و value --}}
            <input type="text" name="CurrencyName" class="form-control" value="{{ old('CurrencyName', $currency->CurrencyName) }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">رمز العملة</label>
            {{-- تم التحديث هنا: name و value --}}
            <input type="text" name="CurrencyCode" class="form-control" value="{{ old('CurrencyCode', $currency->CurrencyCode) }}" maxlength="5" required>
        </div>
        <div class="form-check mb-3">
            {{-- تم التحديث هنا: name و old() و checked --}}
            <input class="form-check-input" type="checkbox" value="1" id="IsDefault" name="IsDefault" {{ old('IsDefault', $currency->IsDefault) ? 'checked' : '' }}>
            <label class="form-check-label" for="IsDefault">تعيين كعملة افتراضية</label>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('settings.currencies.index') }}" class="btn btn-secondary">رجوع</a>
            <button type="submit" class="btn-gold"><i class="fas fa-save"></i> حفظ</button>
        </div>
    </form>
</div>
@endsection
