@extends('layouts.app')
@section('title', 'إعدادات النظام')

@push('head')
<style>
    .settings-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px}
    .card{background:#0f1729;border:1px solid rgba(212,175,55,.25);border-radius:12px;padding:16px;color:#e5e7eb}
    .card h3{margin:0 0 8px;color:#d4af37;font-size:18px}
    .card p{margin:0 0 12px;color:#94a3b8}
    .btn{display:inline-flex;align-items:center;gap:8px;background:linear-gradient(135deg,#d4af37,#b8941f);color:#0f172a;border:none;border-radius:999px;padding:8px 14px;text-decoration:none;font-weight:700}
</style>
@endpush

@section('content')
<div class="page-header d-flex align-items-center justify-content-between" style="background:linear-gradient(135deg,#d4af37,#997515);color:#0f172a;padding:16px;border-radius:12px">
    <h1 style="margin:0"><i class="fas fa-cog" style="margin-left:8px"></i> إعدادات النظام</h1>
    <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#settingsHelpModal">
        <i class="fas fa-question-circle"></i> مساعدة
    </button>
</div>

<!-- Settings Help Modal -->
<div class="modal fade" id="settingsHelpModal" tabindex="-1" aria-labelledby="settingsHelpModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="settingsHelpModalLabel">مساعدة: قسم الإعدادات</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" style="line-height:1.9">
        <p><strong>ما هو الغرض من هذا القسم؟</strong> قسم "الإعدادات" هو "غرفة التحكم" أو "ورشة الصيانة" الخاصة بالنظام. لا ندخل إليه كل يوم، بل فقط عندما نريد تغيير قاعدة أساسية في طريقة عمل البرنامج. إنه قسم مخصص لمدير النظام فقط لأنه يحتوي على خيارات حساسة تؤثر على النظام بأكمله.</p>
        <hr>
        <p><strong>ماذا يوجد داخل هذا القسم؟</strong> عند فتح الإعدادات ستجد بطاقات فرعية، أهمها:</p>
        <ul>
          <li>
            <strong>إدارة المستخدمين (Users Management)</strong>
            <ul>
              <li><strong>ماذا تفعل هنا؟</strong> إضافة الموظفين الجدد الذين سيستخدمون البرنامج وتعيين اسم مستخدم وكلمة مرور.</li>
              <li><strong>لماذا هي مهمة؟</strong> لا يمكن لأي موظف الدخول للنظام بدون حساب يتم إنشاؤه هنا.</li>
            </ul>
          </li>
          <li>
            <strong>المجموعات والصلاحيات (Roles & Permissions)</strong>
            <ul>
              <li><strong>ماذا تفعل هنا؟</strong> تعريف الأدوار وتحديد صلاحيات كل دور (مثال: البائعون، المدراء).</li>
              <li><strong>لماذا هي مهمة؟</strong> لضبط ما يستطيع كل مستخدم رؤيته وفعله داخل النظام.</li>
            </ul>
          </li>
          <li>
            <strong>إدارة العملات (Currencies)</strong>
            <ul>
              <li><strong>ماذا تفعل هنا؟</strong> تعريف العملات المعتمدة وتحديد العملة الافتراضية.</li>
              <li><strong>لماذا هي مهمة؟</strong> لا يمكن إصدار فاتورة بعملة غير معرّفة مسبقاً.</li>
            </ul>
          </li>
          <li>
            <strong>إدارة فئات المنتجات (Categories)</strong>
            <ul>
              <li><strong>ماذا تفعل هنا؟</strong> إنشاء التصنيفات الرئيسية للبضاعة (خواتم، أساور، قلائد، سبائك).</li>
              <li><strong>لماذا هي مهمة؟</strong> لتنظيم المخزون وتسهيل البحث وإعداد التقارير.</li>
            </ul>
          </li>
        </ul>
        <hr>
        <p><strong>الخلاصة:</strong> قسم تأسيسي يُستخدم نادراً ومخصص للمدير لضبط قواعد وهيكل النظام.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
      </div>
    </div>
  </div>
</div>

<div class="settings-grid" style="margin-top:16px">
    <div class="card">
        <h3><i class="fas fa-money-bill-wave" style="margin-left:8px"></i> العملات</h3>
        <p>تعريف وإدارة العملات الافتراضية وأسعار الصرف.</p>
        <a href="{{ route('settings.currencies.index') }}" class="btn"><i class="fas fa-arrow-left"></i> إدارة</a>
    </div>
    <div class="card">
        <h3><i class="fas fa-list" style="margin-left:8px"></i> الفئات</h3>
        <p>تعريف فئات المنتجات المستخدمة عند إضافة المنتجات.</p>
        <a href="{{ route('settings.categories.index') }}" class="btn"><i class="fas fa-arrow-left"></i> إدارة</a>
    </div>
    <div class="card">
        <h3><i class="fas fa-users-cog" style="margin-left:8px"></i> المستخدمون والصلاحيات</h3>
        <p>إدارة المستخدمين والمجموعات والصلاحيات الأساسية.</p>
        <div class="d-flex gap-2">
            <a href="{{ route('settings.users') }}" class="btn"><i class="fas fa-user-plus"></i> إضافة/إدارة المستخدمين</a>
            <a href="{{ route('settings.roles.index') }}" class="btn"><i class="fas fa-shield-alt"></i> الأدوار والصلاحيات</a>
        </div>
    </div>
</div>
@endsection