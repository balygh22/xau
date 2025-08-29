@extends('layouts.app')
@section('title', 'سند دفع جديد')
@section('content')
<div class="page-header" style="background:linear-gradient(135deg,#22c55e,#16a34a);color:#fff;padding:16px;border-radius:12px">
    <h1 style="margin:0">
        @if($type==='receipt') <i class="fas fa-hand-holding-usd"></i> سند قبض
        @elseif($type==='disbursement') <i class="fas fa-cash-register"></i> سند صرف
        @else <i class="fas fa-exchange-alt"></i> تحويل داخلي @endif
    </h1>
</div>

<div class="card mt-3" style="border-radius:12px;border:1px solid #e5e7eb">
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('payments.store') }}">
            @csrf
            <input type="hidden" name="type" value="{{ $type }}">

            <div class="row g-3">
                <!-- ترتيب الحقول حسب النوع: الحساب أولاً ثم الصندوق/البنك ثم المبلغ/العملة/التاريخ -->
                <div class="col-md-6">
                    @if($type==='receipt')
                        <label class="form-label">اسم الحساب (العميل)</label>
                        <select name="from_account_id" class="form-select" required>
                            <option value="">— اختر العميل —</option>
                            @foreach($customers as $a)
                                <option value="{{ $a->AccountID }}" @selected(old('from_account_id')==$a->AccountID)>{{ $a->AccountName }}</option>
                            @endforeach
                        </select>
                    @elseif($type==='disbursement')
                        <label class="form-label">اسم الحساب (المورد)</label>
                        <select name="to_account_id" class="form-select" required>
                            <option value="">— اختر المورد —</option>
                            @foreach($suppliers as $a)
                                <option value="{{ $a->AccountID }}" @selected(old('to_account_id')==$a->AccountID)>{{ $a->AccountName }}</option>
                            @endforeach
                        </select>
                    @else
                        <label class="form-label">من الصندوق/البنك</label>
                        <select name="from_account_id" class="form-select" required>
                            <option value="">— اختر الصندوق/البنك —</option>
                            @foreach($cashAndBanks as $a)
                                <option value="{{ $a->AccountID }}" @selected(old('from_account_id')==$a->AccountID)>{{ $a->AccountName }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
                <div class="col-md-6">
                    @if($type==='receipt')
                        <label class="form-label">إلى الصندوق/البنك</label>
                        <select name="to_account_id" class="form-select" required>
                            <option value="">— اختر الصندوق/البنك —</option>
                            @foreach($cashAndBanks as $a)
                                <option value="{{ $a->AccountID }}" @selected(old('to_account_id')==$a->AccountID)>{{ $a->AccountName }}</option>
                            @endforeach
                        </select>
                    @elseif($type==='disbursement')
                        <label class="form-label">من الصندوق/البنك</label>
                        <select name="from_account_id" class="form-select" required>
                            <option value="">— اختر الصندوق/البنك —</option>
                            @foreach($cashAndBanks as $a)
                                <option value="{{ $a->AccountID }}" @selected(old('from_account_id')==$a->AccountID)>{{ $a->AccountName }}</option>
                            @endforeach
                        </select>
                    @else
                        <label class="form-label">إلى الصندوق/البنك</label>
                        <select name="to_account_id" class="form-select" required>
                            <option value="">— اختر الصندوق/البنك —</option>
                            @foreach($cashAndBanks as $a)
                                <option value="{{ $a->AccountID }}" @selected(old('to_account_id')==$a->AccountID)>{{ $a->AccountName }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <div class="col-md-4">
                    <label class="form-label">المبلغ</label>
                    <input type="number" name="amount" step="0.01" min="0.01" class="form-control" value="{{ old('amount') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">العملة</label>
                    @if(!empty($linkedTransaction))
                        <input type="hidden" name="currency_id" value="{{ $linkedTransaction['currency_id'] }}">
                        <input type="text" class="form-control" value="{{ $linkedTransaction['currency_label'] }} (مقفلة حسب الفاتورة)" disabled>
                    @else
                        <select name="currency_id" class="form-select" required>
                            <option value="">— اختر —</option>
                            @foreach($currencies as $c)
                                @php $id = $c->id ?? $c->CurrencyID; $name = $c->name ?? $c->CurrencyName; @endphp
                                <option value="{{ $id }}" @selected(old('currency_id')==$id)>{{ $name }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
                <div class="col-md-4">
                    <label class="form-label">التاريخ</label>
                    <input type="datetime-local" name="payment_date" class="form-control" value="{{ old('payment_date', now()->format('Y-m-d\TH:i')) }}" required>
                </div>

                <div class="col-12">
                    <label class="form-label">الوصف (اختياري)</label>
                    <input type="text" name="description" class="form-control" value="{{ old('description') }}" placeholder="مثال: دفعة من العميل أحمد إلى الصندوق">
                </div>

                <div class="col-12">
                    <label class="form-label">ربط بفاتورة (اختياري)</label>
                    <input type="number" name="transaction_id" id="transaction_id" class="form-control" value="{{ old('transaction_id', request('transaction_id')) }}" placeholder="اكتب رقم الفاتورة أو سيُستخدم رقمها تلقائيًا من الرابط إن وُجد">
                    <small class="text-muted">عند ربط فاتورة سيتم فرض مطابقة العملة تلقائيًا.</small>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4">
                <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-right"></i> رجوع</a>
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> حفظ</button>
            </div>
        </form>
    </div>
</div>
@endsection