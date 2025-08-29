@extends('layouts.app')
@section('title','تعديل صلاحيات المجموعة')

@push('head')
<style>
    .page-header {background:linear-gradient(135deg,#d4af37,#997515);color:#0f172a;padding:16px;border-radius:12px;margin-bottom:12px}
    .card {background:#0f1729;border:1px solid rgba(212,175,55,.25);border-radius:12px;padding:16px;color:#e5e7eb}
    .perm-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:12px}
    .perm-item{background:#0f1729;border:1px dashed rgba(212,175,55,.25);border-radius:8px;padding:10px}
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 style="margin:0"><i class="fas fa-user-shield" style="margin-left:8px"></i> تعديل صلاحيات مجموعة: {{ $role->GroupName }}</h1>
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
    <form method="POST" action="{{ route('settings.roles.update', $role) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">اسم المجموعة</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $role->GroupName) }}" required>
        </div>

        <div class="mb-2"><strong>الصلاحيات المتاحة</strong></div>
        <div class="perm-grid">
            @foreach($permissions as $perm)
                <label class="perm-item">
                    <input type="checkbox" name="permissions[]" value="{{ $perm->PermissionID }}" {{ in_array($perm->PermissionID, $assigned) ? 'checked' : '' }}>
                    <strong style="margin-right:6px">{{ $perm->PermissionName }}</strong>
                    @if($perm->Description)
                        <div class="text-muted" style="font-size:12px;margin-top:4px">{{ $perm->Description }}</div>
                    @endif
                </label>
            @endforeach
        </div>

        <div class="d-flex gap-2" style="margin-top:16px">
            <a href="{{ route('settings.roles.index') }}" class="btn btn-secondary">رجوع</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> حفظ التغييرات</button>
        </div>
    </form>
</div>
@endsection