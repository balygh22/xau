@extends('layouts.app')
@section('title','المستخدمون والصلاحيات')
@push('head')
<style>
    /* تعريف الألوان المتسقة */
    :root {
        --gold-1: #d4af37;
        --gold-2: #b8941f;
        --gold-3: #997515;
        --gold-light: #f0d78c;
        --gold-pale: #fef3c7;
        --ink: #0f172a;
        --bg: #0a0e1a;
        --card: #0f1729;
        --text: #e5e7eb;
        --text-secondary: #94a3b8;
        --muted: #6b7280;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
    }

    /* تحسينات عامة */
    body {
        background-color: var(--bg);
        color: var(--text);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    /* تحسين رأس الصفحة */
    .page-header {
        background: linear-gradient(135deg, var(--gold-1), var(--gold-3));
        color: var(--ink);
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(212, 175, 55, 0.25);
        margin-bottom: 20px;
        transition: all 0.3s ease;
        border: 1px solid rgba(212, 175, 55, 0.2);
    }

    .page-header:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 25px rgba(212, 175, 55, 0.35);
    }

    .page-header h1 {
        margin: 0;
        font-size: 24px;
        font-weight: 700;
        display: flex;
        align-items: center;
        text-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }

    .page-header h1 i {
        margin-left: 10px;
        font-size: 20px;
    }

    /* تحسين البطاقات */
    .card {
        background: var(--card);
        border: 1px solid rgba(212, 175, 55, 0.25);
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transition: all 0.3s ease;
        margin-bottom: 20px;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.25);
        border-color: rgba(212, 175, 55, 0.4);
    }

    .card-body {
        padding: 20px;
        color: var(--text);
    }

    /* تحسين النماذج */
    .form-label {
        color: var(--text);
        font-weight: 600;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .form-control, .form-select {
        background-color: rgba(15, 23, 41, 0.7);
        border: 1px solid rgba(212, 175, 55, 0.3);
        color: var(--text);
        border-radius: 8px;
        padding: 10px 12px;
        transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        background-color: rgba(15, 23, 41, 0.9);
        border-color: var(--gold-1);
        color: var(--text);
        box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1);
    }

    .form-control::placeholder {
        color: var(--muted);
    }

    /* تحسين الأزرار */
    .btn {
        border: none;
        border-radius: 8px;
        padding: 10px 20px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-success {
        background: linear-gradient(135deg, var(--success), #059669);
        color: white;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }

    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
    }

    /* تحسين التنبيهات */
    .alert {
        border-radius: 8px;
        padding: 12px 16px;
        margin-bottom: 16px;
        border: none;
    }

    .alert-success {
        background-color: rgba(16, 185, 129, 0.15);
        color: var(--success);
        border: 1px solid rgba(16, 185, 129, 0.3);
    }

    .alert-danger {
        background-color: rgba(239, 68, 68, 0.15);
        color: var(--danger);
        border: 1px solid rgba(239, 68, 68, 0.3);
    }

    .alert ul {
        margin: 0;
        padding-right: 20px;
    }

    /* تحسين النصوص */
    .text-muted {
        color: var(--text-secondary) !important;
    }

    /* تحسين الكود */
    code {
        background-color: rgba(15, 23, 41, 0.7);
        color: var(--gold-light);
        padding: 2px 6px;
        border-radius: 4px;
        font-family: 'Courier New', monospace;
        font-size: 13px;
        border: 1px solid rgba(212, 175, 55, 0.2);
    }

    /* تحسين العناوين */
    h5 {
        color: var(--gold-1);
        font-weight: 700;
        margin-bottom: 16px;
        font-size: 18px;
    }

    /* تحسين الصفحة التعريفية */
    .info-box {
        background: var(--card);
        border: 1px solid rgba(212, 175, 55, 0.25);
        border-radius: 12px;
        padding: 16px;
        color: var(--text);
        margin-bottom: 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .info-box p {
        margin: 0;
        line-height: 1.6;
    }

    /* تحسينات إضافية */
    .mt-3 {
        margin-top: 1rem !important;
    }

    .mb-3 {
        margin-bottom: 1rem !important;
    }

    .mb-0 {
        margin-bottom: 0 !important;
    }

    .g-2 {
        gap: 0.5rem;
    }

    /* تأثيرات حركية */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .fade-in {
        animation: fadeIn 0.5s ease forwards;
    }
</style>
@endpush
@section('content')
<div class="container">
    <!-- رأس الصفحة -->
    <div class="page-header fade-in">
        <h1>
            <i class="fas fa-users-cog"></i>
            المستخدمون والصلاحيات
        </h1>
    </div>

    <!-- الصفحة التعريفية -->
    <div class="info-box fade-in" style="animation-delay: 0.1s">
        <p>صفحة مبدئية لإدارة المستخدمين والمجموعات والصلاحيات. يمكنك من خلال هذه الصفحة إضافة مستخدمين جدد للنظام وتحديد صلاحياتهم ومجموعاتهم.</p>
    </div>

    <!-- نموذج إضافة مستخدم -->
    <div class="card fade-in" style="animation-delay: 0.2s">
        <div class="card-body">
            @if(session('status'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle" style="margin-left: 8px;"></i>
                    {{ session('status') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle" style="margin-left: 8px;"></i>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <h5><i class="fas fa-user-plus" style="margin-left: 8px;"></i> إضافة مستخدم للنظام</h5>
            <form method="POST" action="{{ route('settings.users.store') }}">
                @csrf
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label">
                            <i class="fas fa-user" style="margin-left: 5px;"></i>
                            الاسم الكامل
                        </label>
                        <input type="text" name="FullName" class="form-control" required placeholder="أدخل الاسم الكامل">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">
                            <i class="fas fa-at" style="margin-left: 5px;"></i>
                            اسم المستخدم
                        </label>
                        <input type="text" name="UserName" class="form-control" required placeholder="أدخل اسم المستخدم">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">
                            <i class="fas fa-envelope" style="margin-left: 5px;"></i>
                            البريد الإلكتروني (اختياري)
                        </label>
                        <input type="email" name="Email" class="form-control" placeholder="example@email.com">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">
                            <i class="fas fa-toggle-on" style="margin-left: 5px;"></i>
                            الحالة
                        </label>
                        <select name="IsActive" class="form-select">
                            <option value="1">فعّال</option>
                            <option value="0">غير فعّال</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">
                            <i class="fas fa-lock" style="margin-left: 5px;"></i>
                            كلمة المرور
                        </label>
                        <input type="password" name="Password" class="form-control" required placeholder="أدخل كلمة المرور">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">
                            <i class="fas fa-users" style="margin-left: 5px;"></i>
                            المجموعة (GroupID)
                        </label>
                        <input type="number" name="GroupID" class="form-control" min="1" placeholder="اختياري">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-plus"></i>
                            إضافة المستخدم
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- قسم تجربة API -->
    <div class="card fade-in" style="animation-delay: 0.3s">
        <div class="card-body">
            <h5><i class="fas fa-code" style="margin-left: 8px;"></i> تجربة API (JWT)</h5>
            <p class="text-muted">
                يمكنك اختبار تسجيل الدخول عبر API باستخدام Postman: 
                <code>POST /api/login</code> 
                مع JSON: 
                <code>{"username":"X","password":"Y"}</code> 
                ثم استدعاء 
                <code>GET /api/me</code> 
                باستخدام التوكن.
            </p>
        </div>
    </div>
</div>
@endsection