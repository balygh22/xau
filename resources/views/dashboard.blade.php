@extends('layouts.app')
@section('title', 'لوحة التحكم')
@push('head')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;900&display=swap');
    
    :root{
        /* ألوان ذهبية متدرجة */
        --gold-1: #d4af37;      /* ذهبي فاتح */
        --gold-2: #b8941f;      /* ذهبي متوسط */
        --gold-3: #997515;      /* ذهبي غامق */
        --gold-light: #f0d78c;  /* ذهبي شاحب جداً */
        --gold-pale: #fef3c7;   /* ذهبي باهت */
        --gold-dark: #7a5f0a;   /* ذهبي داكن */
        
        /* ألوان داعمة */
        --ink: #0f172a;         /* أسود مائل للزرقة */
        --bg: #0a0e1a;          /* خلفية داكنة */
        --card: #0f1729;        /* خلفية البطاقات */
        --text: #e5e7eb;        /* نص فاتح */
        --text-secondary: #94a3b8; /* نص ثانوي */
        --muted: #6b7280;       /* نص باهت */
        
        /* ألوان الحالة */
        --success: #10b981;     /* أخضر للنجاح */
        --warning: #f59e0b;     /* برتقالي للتحذير */
        --danger: #ef4444;      /* أحمر للخطر */
        
        --sidebar-width: 260px;
        --header-height: 80px;
    }
    
    /* تحسينات عامة */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    body {
        font-family: 'Tajawal', sans-serif;
        background-color: var(--bg);
        color: var(--text);
        overflow-x: hidden;
        position: relative;
    }
    
    /* خلفية متحركة */
    body::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle at 20% 50%, rgba(212, 175, 55, 0.05) 0%, transparent 50%),
                    radial-gradient(circle at 80% 80%, rgba(212, 175, 55, 0.07) 0%, transparent 50%),
                    radial-gradient(circle at 40% 20%, rgba(212, 175, 55, 0.05) 0%, transparent 50%);
        z-index: -1;
    }
    
    .container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
    }
    
    /* تحسين رأس الصفحة */
    .page-header {
        background: linear-gradient(135deg, var(--gold-1), var(--gold-3));
        color: var(--ink);
        padding: 30px;
        border-radius: 16px;
        box-shadow: 0 15px 35px rgba(212, 175, 55, 0.25);
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(212, 175, 55, 0.2);
        transform: translateY(0);
        transition: all 0.4s ease;
    }
    
    .page-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%);
        opacity: 0;
        transition: opacity 0.5s ease;
    }
    
    .page-header:hover::before {
        opacity: 1;
    }
    
    .page-header:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(212, 175, 55, 0.35);
    }
    
    .header-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 25px;
        position: relative;
        z-index: 1;
    }
    
    .header-content h1 {
        margin: 0;
        font-size: 32px;
        font-weight: 900;
        display: flex;
        align-items: center;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .header-content h1 i {
        margin-left: 15px;
        font-size: 36px;
        filter: drop-shadow(0 2px 3px rgba(0,0,0,0.2));
    }
    
    /* تحسين شبكة الإحصائيات */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 25px;
        position: relative;
        z-index: 1;
    }
    
    /* تحسين بطاقات الإحصائيات */
    .stat-card {
        background: linear-gradient(135deg, rgba(15, 23, 41, 0.9), rgba(15, 23, 41, 0.7));
        backdrop-filter: blur(10px);
        border: 1px solid rgba(212, 175, 55, 0.3);
        border-radius: 16px;
        padding: 25px;
        text-align: center;
        box-shadow: 0 10px 25px rgba(0,0,0,.2);
        transition: all 0.4s ease;
        position: relative;
        overflow: hidden;
        transform: translateY(0);
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100%;
        height: 5px;
        background: linear-gradient(90deg, var(--gold-1), var(--gold-3));
        transform: scaleX(0);
        transform-origin: right;
        transition: transform 0.5s ease;
    }
    
    .stat-card:hover::before {
        transform: scaleX(1);
        transform-origin: left;
    }
    
    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 35px rgba(0,0,0,.3);
        border-color: var(--gold-1);
    }
    
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        background: linear-gradient(135deg, var(--gold-1), var(--gold-2));
        box-shadow: 0 6px 12px rgba(212, 175, 55, 0.4);
        position: relative;
        z-index: 1;
    }
    
    .stat-icon::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: rgba(255,255,255,0.3);
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .stat-card:hover .stat-icon::after {
        opacity: 1;
    }
    
    .stat-icon i {
        font-size: 28px;
        color: var(--ink);
        position: relative;
        z-index: 1;
    }
    
    .stat-value {
        font-size: 36px;
        font-weight: 900;
        margin-bottom: 10px;
        color: var(--gold-1);
        text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        position: relative;
        z-index: 1;
    }
    
    .stat-label {
        font-size: 16px;
        color: var(--text-secondary);
        font-weight: 500;
        position: relative;
        z-index: 1;
    }
    
    /* تحسين الجداول */
    .table-responsive {
        overflow: auto;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        border: 1px solid rgba(212, 175, 55, 0.1);
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
    }
    
    th, td {
        padding: 18px;
        text-align: right;
        border-bottom: 1px solid rgba(212, 175, 55, 0.1);
    }
    
    thead tr {
        background: linear-gradient(to right, var(--gold-pale), rgba(240, 215, 140, 0.3));
        color: var(--ink);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 14px;
    }
    
    tbody tr {
        transition: all 0.3s ease;
        background: rgba(15, 23, 41, 0.5);
    }
    
    tbody tr:hover {
        background-color: rgba(212, 175, 55, 0.1);
        transform: scale(1.01);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    /* تحسين الأزرار */
    .btn {
        border: none;
        border-radius: 30px;
        padding: 12px 24px;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
        box-shadow: 0 6px 12px rgba(0,0,0,0.2);
        position: relative;
        overflow: hidden;
        z-index: 1;
    }
    
    .btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255,255,255,0.2);
        transform: translateX(-100%);
        transition: transform 0.5s ease;
        z-index: -1;
    }
    
    .btn:hover::before {
        transform: translateX(0);
    }
    
    .btn-gold {
        color: var(--ink);
        background: linear-gradient(135deg, var(--gold-1), var(--gold-2));
    }
    
    .btn-gold:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(212, 175, 55, 0.4);
    }
    
    /* تحسين شارات الحالة */
    .status-badge {
        padding: 8px 16px;
        border-radius: 30px;
        font-size: 13px;
        font-weight: 700;
        display: inline-block;
        letter-spacing: 1px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .status-completed {
        background: rgba(16, 185, 129, 0.15);
        color: var(--success);
        border: 1px solid rgba(16, 185, 129, 0.3);
    }
    
    .status-pending {
        background: rgba(245, 158, 11, 0.15);
        color: var(--warning);
        border: 1px solid rgba(245, 158, 11, 0.3);
    }
    
    .status-cancelled {
        background: rgba(239, 68, 68, 0.15);
        color: var(--danger);
        border: 1px solid rgba(239, 68, 68, 0.3);
    }
    
    /* تحسين المنتجات */
    .product-item {
        display: flex;
        align-items: center;
        padding: 20px;
        margin-bottom: 15px;
        background: linear-gradient(135deg, rgba(15, 23, 41, 0.8), rgba(15, 23, 41, 0.6));
        border-radius: 12px;
        transition: all 0.4s ease;
        border-left: 5px solid transparent;
        position: relative;
        overflow: hidden;
    }
    
    .product-item::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle at center, rgba(212, 175, 55, 0.1) 0%, transparent 70%);
        opacity: 0;
        transition: opacity 0.4s ease;
    }
    
    .product-item:hover::before {
        opacity: 1;
    }
    
    .product-item:hover {
        background: linear-gradient(135deg, rgba(15, 23, 41, 0.9), rgba(15, 23, 41, 0.7));
        border-left-color: var(--gold-1);
        transform: translateX(8px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    }
    
    /* تحسين منطقة المحتوى */
    .content-area {
        transition: all 0.5s ease;
    }
    
    /* تحسين الأقسام */
    .form-section {
        border-radius: 16px;
        overflow: hidden;
        border: 2px solid rgba(212, 175, 55, 0.3);
        background: linear-gradient(135deg, rgba(15, 23, 41, 0.8), rgba(15, 23, 41, 0.6));
        box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        transition: all 0.5s ease;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }
    
    .form-section::after {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(212, 175, 55, 0.05) 0%, transparent 70%);
        opacity: 0;
        transition: opacity 0.5s ease;
    }
    
    .form-section:hover::after {
        opacity: 1;
    }
    
    .form-section:hover {
        box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        transform: translateY(-5px);
    }
    
    .head {
        background: rgba(212, 175, 55, 0.1);
        padding: 25px;
        border-bottom: 1px solid rgba(212, 175, 55, 0.2);
        position: relative;
        z-index: 1;
    }
    
    .head h3 {
        margin: 0;
        color: var(--gold-1);
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 22px;
        font-weight: 800;
        text-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    
    /* تأثيرات حركية */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    
    .fade-in {
        animation: fadeIn 0.8s ease forwards;
    }
    
    .pulse {
        animation: pulse 2s infinite;
    }
    
    /* تحسين أيقونات المنتج */
    .product-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-left: 15px;
        background: linear-gradient(135deg, var(--gold-1), var(--gold-2));
        box-shadow: 0 6px 12px rgba(212, 175, 55, 0.4);
        flex-shrink: 0;
    }
    
    /* تحسين نصوص الأسعار */
    .price {
        color: var(--gold-1);
        font-weight: 800;
        font-size: 18px;
    }
    
    /* تحسين الفوتر */
    .footer-muted {
        text-align: center;
        color: var(--text-secondary);
        padding: 25px;
        font-size: 14px;
    }
    
    /* تحسين تخطيط المحتوى */
    .dashboard-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    @media (min-width: 992px) {
        .dashboard-grid {
            grid-template-columns: 2fr 1fr;
        }
    }
    
    /* تحسينات إضافية */
    .glow {
        text-shadow: 0 0 10px rgba(212, 175, 55, 0.7);
    }
    
    .header-actions {
        display: flex;
        align-items: center;
        gap: 20px;
    }
    
    .date-time {
        display: flex;
        gap: 20px;
        align-items: center;
        color: var(--ink);
        font-weight: 600;
    }
    
    .date-time span {
        display: flex;
        align-items: center;
        gap: 8px;
        background: rgba(255,255,255,0.2);
        padding: 8px 15px;
        border-radius: 30px;
        backdrop-filter: blur(5px);
    }
    
    /* تأثيرات للجوال */
    @media (max-width: 768px) {
        .header-content {
            flex-direction: column;
            gap: 20px;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .dashboard-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush
@section('content')
<div class="content-area">
    <div class="container">
        <!-- رأس الصفحة مع الإحصائيات -->
        <div class="page-header fade-in">
            <div class="header-content">
                <h1 class="glow">
                    <i class="fas fa-gem"></i>
                    لوحة التحكم الرئيسية
                </h1>
                <div class="header-actions">
                    <div class="date-time">
                        <span><i class="fas fa-calendar-alt"></i><span class="date-display">{{ \Carbon\Carbon::now()->format('Y/m/d') }}</span></span>
                        <span><i class="fas fa-clock"></i><span id="live-time" class="time">{{ \Carbon\Carbon::now()->format('H:i') }}</span></span>
                    </div>
                </div>
            </div>
            
            <!-- شبكة الإحصائيات -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon pulse">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div class="stat-value">{{ number_format(($stats['total_sales'] ?? 0)) }}</div>
                    <div class="stat-label">إجمالي مبيعات الشهر</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon pulse">
                        <i class="fas fa-gem"></i>
                    </div>
                    <div class="stat-value">{{ number_format(($stats['total_products'] ?? 0)) }}</div>
                    <div class="stat-label">عدد المنتجات</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon pulse">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value">{{ number_format(($stats['total_customers'] ?? 0)) }}</div>
                    <div class="stat-label">عدد العملاء</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon pulse">
                        <i class="fas fa-warehouse"></i>
                    </div>
                    <div class="stat-value">{{ number_format(($stats['total_inventory'] ?? 0)) }}</div>
                    <div class="stat-label">إجمالي المخزون</div>
                </div>
            </div>
        </div>
        
        <!-- تخطيط الشبكة للمحتوى -->
        <div class="dashboard-grid">
            <!-- قسم أحدث المعاملات -->
            <div class="form-section fade-in" style="animation-delay: 0.2s">
                <div class="head">
                    <h3>
                        <span style="display:flex;align-items:center">
                            <i class="fas fa-receipt" style="margin-left:15px"></i> 
                            أحدث المعاملات
                        </span>
                        <span class="actions">
                            <a class="btn btn-gold" href="{{ route('transactions.index') }}">
                                <i class="fas fa-list"></i> عرض المعاملات
                            </a>
                        </span>
                    </h3>
                </div>
                <div style="padding:25px;background:rgba(15, 23, 41, 0.3)">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>رقم الفاتورة</th>
                                    <th>العميل</th>
                                    <th>التاريخ</th>
                                    <th>المبلغ الإجمالي</th>
                                    <th>الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                            @php($recent_sales = $recent_sales ?? [])
                            @forelse($recent_sales as $sale)
                                <tr>
                                    <td style="color:var(--gold-1);font-weight:700">#{{ $sale->InvoiceNumber ?? '' }}</td>
                                    <td>{{ optional($sale->customer)->CustomerName ?? 'عميل غير مسجل' }}</td>
                                    <td>{{ isset($sale->SaleDate) ? \Carbon\Carbon::parse($sale->SaleDate)->format('Y-m-d') : '' }}</td>
                                    <td class="price">${{ number_format(($sale->GrandTotal ?? 0), 2) }}</td>
                                    <td>
                                        @switch($sale->Status ?? '')
                                            @case('Completed')
                                                <span class="status-badge status-completed">مكتملة</span>
                                                @break
                                            @case('Pending')
                                                <span class="status-badge status-pending">معلقة</span>
                                                @break
                                            @default
                                                <span class="status-badge status-cancelled">ملغاة</span>
                                        @endswitch
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="footer-muted">لا توجد معاملات حديثة</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- قسم المنتجات الأكثر مبيعاً -->
            <div class="form-section fade-in" style="animation-delay: 0.4s">
                <div class="head" style="background:rgba(212, 175, 55, 0.15);border-bottom:1px solid var(--gold-1)">
                    <h3 style="color:var(--gold-1)">
                        <span style="display:flex;align-items:center">
                            <i class="fas fa-star" style="margin-left:15px"></i> 
                            المنتجات الأكثر مبيعاً
                        </span>
                        <span class="actions">
                            <a class="btn" style="color:var(--gold-1);background:rgba(212, 175, 55, 0.15);border:1px solid rgba(212, 175, 55, 0.3)" href="{{ route('inventory.index') }}">
                                عرض الكل <i class="fas fa-arrow-left" style="margin-right:8px"></i>
                            </a>
                        </span>
                    </h3>
                </div>
                <div style="padding:25px;background:rgba(15, 23, 41, 0.3)">
                    @php($top_products = $top_products ?? [])
                    @if(!empty($top_products) && count($top_products) > 0)
                        @foreach($top_products as $item)
                            <div class="product-item">
                                <div class="product-icon">
                                    <i class="fas fa-gem" style="color:var(--ink)"></i>
                                </div>
                                <div style="flex:1">
                                    <div style="font-weight:700;color:var(--text);font-size:18px">{{ optional($item->product)->ProductName ?? 'منتج' }}</div>
                                    <div style="font-size:14px;color:var(--text-secondary);margin-top:5px">الكمية: {{ number_format(($item->total_quantity ?? 0)) }}</div>
                                </div>
                                <div class="price">{{ number_format(($item->total_revenue ?? 0)) }}</div>
                            </div>
                        @endforeach
                    @else
                        <div class="footer-muted">لا توجد بيانات</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
    // تحديث الوقت كل 15 ثانية
    setInterval(function(){
        var el = document.getElementById('live-time');
        if(!el) return;
        var d = new Date();
        var hh = (''+d.getHours()).padStart(2,'0');
        var mm = (''+d.getMinutes()).padStart(2,'0');
        el.textContent = hh + ':' + mm;
    }, 1000*15);
    
    // إضافة تأثيرات حركية عند التمرير
    document.addEventListener('DOMContentLoaded', function() {
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);
        
        document.querySelectorAll('.form-section').forEach(el => {
            observer.observe(el);
        });
    });
</script>
@endpush