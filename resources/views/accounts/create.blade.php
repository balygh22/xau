@extends('layouts.app')
@section('title','إضافة حساب')
@section('content')
<div class="page-header" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);color:#fff;padding:16px;border-radius:12px">
    <h1 style="margin:0"><i class="fas fa-user-plus" style="margin-left:8px"></i> إضافة حساب جديد</h1>
</div>

<div class="card mt-3" style="border-radius:12px;border:1px solid #e5e7eb">
    <div class="card-body">
        <form method="POST" action="{{ route('accounts.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">اسم الحساب</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">نوع الحساب</label>
                    <select name="account_type" class="form-select @error('account_type') is-invalid @enderror" required>
                        <option value="">-- اختر --</option>
                        @foreach($types as $t)
                            <option value="{{ $t }}" @selected(old('account_type')===$t)>{{ $t }}</option>
                        @endforeach
                    </select>
                    @error('account_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">المعرّف (اختياري)</label>
                    <input type="text" name="identifier" value="{{ old('identifier') }}" class="form-control @error('identifier') is-invalid @enderror">
                    @error('identifier')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active',1) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">نشط</label>
                    </div>
                </div>
            </div>
            <div class="mt-3 d-flex gap-2">
                <button class="btn btn-primary"><i class="fas fa-save"></i> حفظ</button>
                <a href="{{ route('accounts.index') }}" class="btn btn-outline-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection