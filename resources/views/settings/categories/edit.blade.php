@extends('layouts.app')
@section('title','تعديل فئة')

@push('head')
<style>
    .page-header {background:linear-gradient(135deg,#d4af37,#997515);color:#0f172a;padding:16px;border-radius:12px;margin-bottom:12px}
    .card {background:#0f1729;border:1px solid rgba(212,175,55,.25);border-radius:12px;padding:16px;color:#e5e7eb;max-width:720px}
    .btn-gold {background:linear-gradient(135deg,#d4af37,#b8941f);color:#0f172a;border:none;border-radius:999px;padding:8px 14px;font-weight:700}
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 style="margin:0"><i class="fas fa-edit" style="margin-left:8px"></i> تعديل فئة</h1>
</div>

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
    <form action="{{ route('settings.categories.update', $category) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="form-label">اسم الفئة</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $category->CategoryName) }}" required>
        </div>
        
        <div class="d-flex gap-2">
            <a href="{{ route('settings.categories.index') }}" class="btn btn-secondary">رجوع</a>
            <button type="submit" class="btn-gold"><i class="fas fa-save"></i> حفظ</button>
        </div>
    </form>
</div>
@endsection