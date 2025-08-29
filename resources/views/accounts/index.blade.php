@extends('layouts.app')
@section('title', 'الحسابات')
@section('content')
<div class="page-header d-flex align-items-center justify-content-between" style="background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;padding:16px;border-radius:12px">
    <h1 style="margin:0"><i class="fas fa-wallet" style="margin-left:8px"></i> إدارة الحسابات</h1>
    <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#accountsHelpModal">
        <i class="fas fa-question-circle"></i> مساعدة
    </button>
</div>

<!-- Accounts Help Modal -->
<div class="modal fade" id="accountsHelpModal" tabindex="-1" aria-labelledby="accountsHelpModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="accountsHelpModalLabel">مساعدة: قسم الحسابات</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" style="line-height:1.9">
        <p><strong>ما هو الغرض من هذا القسم؟</strong> قسم "الحسابات" هو "دفتر العناوين المالي" الخاص بك. إنه قسم تشغيلي ويومي لإدارة كل الأطراف التي تتعامل معها مالياً. متاح للموظفين المصرّح لهم.</p>
        <hr>
        <p><strong>ماذا يوجد داخل هذا القسم؟</strong> صفحة واحدة تعرض جدول الحسابات مع فلاتر في الأعلى. يمكنك:</p>
        <ul>
          <li><strong>إضافة عميل جديد:</strong> اختر النوع "عميل" وأدخل البيانات.</li>
          <li><strong>إضافة مورد جديد:</strong> أضفه كنوع "مورد".</li>
          <li><strong>إضافة صندوق جديد:</strong> أضفه كنوع "صندوق" (Cashbox).</li>
          <li><strong>تصفية العرض:</strong> اعرض العملاء فقط أو الموردين أو الصناديق أو البنوك.</li>
          <li><strong>استعراض كشف حساب:</strong> زر "كشف حساب" لكل حساب لعرض الفواتير والدفعات والرصيد.</li>
        </ul>
        <hr>
        <p><strong>الخلاصة:</strong> قسم يومي وتشغيلي لإدارة الكيانات المالية الداعمة للعمليات.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
      </div>
    </div>
  </div>
</div>


<div class="card mt-3" style="border-radius:12px;border:1px solid #e5e7eb">
    <div class="card-body">
        @php
            $type = $type ?? null;
        @endphp
        <div class="d-flex flex-wrap gap-2 mb-3">
            <a href="{{ route('accounts.index') }}" class="btn btn-sm {{ $type ? 'btn-outline-secondary' : 'btn-dark' }}">عرض الكل</a>
            <a href="{{ route('accounts.index', ['type' => 'Customer']) }}" class="btn btn-sm {{ $type==='Customer' ? 'btn-dark' : 'btn-outline-secondary' }}">العملاء</a>
            <a href="{{ route('accounts.index', ['type' => 'Supplier']) }}" class="btn btn-sm {{ $type==='Supplier' ? 'btn-dark' : 'btn-outline-secondary' }}">الموردون</a>
            <a href="{{ route('accounts.index', ['type' => 'Cashbox']) }}" class="btn btn-sm {{ $type==='Cashbox' ? 'btn-dark' : 'btn-outline-secondary' }}">الصناديق</a>
            <a href="{{ route('accounts.index', ['type' => 'Bank']) }}" class="btn btn-sm {{ $type==='Bank' ? 'btn-dark' : 'btn-outline-secondary' }}">البنوك</a>
            <a href="{{ route('accounts.create') }}" class="btn btn-sm btn-primary ms-auto"><i class="fas fa-plus"></i> إضافة حساب جديد</a>
        </div>



        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px">#</th>
                        <th>اسم الحساب</th>
                        <th style="width:140px">النوع</th>
                        <th style="width:200px">المعرّف</th>
                        <th style="width:120px">الحالة</th>
                        <th style="width:260px">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accounts as $acc)
                        <tr>
                            <td>{{ $acc->id }}</td>
                            <td>{{ $acc->name }}</td>
                            <td>{{ $acc->type_label }}</td>
                            <td>{{ $acc->identifier }}</td>
                            <td>
                                @if($acc->is_active)
                                    <span class="badge bg-success">مفعل</span>
                                @else
                                    <span class="badge bg-secondary">موقوف</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('accounts.statement', $acc) }}" class="btn btn-sm btn-outline-dark"><i class="fas fa-file-invoice-dollar"></i> كشف حساب</a>
                                @if($acc->is_active)
                                    <form action="{{ route('accounts.deactivate', $acc) }}" method="post" style="display:inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-warning"><i class="fas fa-ban"></i> تعطيل</button>
                                    </form>
                                @else
                                    <form action="{{ route('accounts.activate', $acc) }}" method="post" style="display:inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success"><i class="fas fa-check"></i> تفعيل</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center">لا توجد حسابات.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $accounts->links() }}
        </div>
    </div>
</div>
@endsection