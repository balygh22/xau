<!-- resources/views/includes/sidebar.blade.php -->
<div class="sidebar-container">
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo-container">
                <div class="logo">
                    <i class="fas fa-gem"></i>
                </div>
                <div class="logo-text">
                    <span class="main-text">نظام إدارة</span>
                    <span class="sub-text">محل الذهب</span>
                </div>
            </div>
            <button class="sidebar-toggle d-lg-none">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="sidebar-menu">
            <ul class="menu-list">
                <li class="menu-item {{ request()->is('dashboard') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>لوحة التحكم</span>
                    </a>
                </li>
                <li class="menu-item {{ request()->is('transactions*') ? 'active' : '' }}">
                    <a href="{{ route('transactions.index') }}">
                        <i class="fas fa-receipt"></i>
                        <span>المعاملات</span>
                    </a>
                </li>
                <li class="menu-item {{ request()->is('payments*') ? 'active' : '' }}">
                    <a href="{{ route('payments.index') }}">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>المدفوعات</span>
                    </a>
                </li>
                <li class="menu-item {{ request()->is('inventory*') ? 'active' : '' }}">
                    <a href="{{ route('inventory.index') }}">
                        <i class="fas fa-ring"></i>
                        <span>المنتجات</span>
                    </a>
                </li>
                <li class="menu-item {{ request()->is('accounts*') ? 'active' : '' }}">
                    <a href="{{ route('accounts.index') }}">
                        <i class="fas fa-wallet"></i>
                        <span>الحسابات</span>
                    </a>
                </li>
                <li class="menu-item {{ request()->is('reports*') ? 'active' : '' }}">
                    <a href="{{ route('reports.index') }}">
                        <i class="fas fa-chart-line"></i>
                        <span>التقارير</span>
                    </a>
                </li>
                <li class="menu-item {{ request()->is('settings*') ? 'active' : '' }}">
                    <a href="{{ route('settings') }}">
                        <i class="fas fa-cog"></i>
                        <span>الإعدادات</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="sidebar-footer">
            <div class="user-profile">
                <div class="user-avatar">
                    <img src="{{ Auth::user()->avatar ?? asset('images/default-avatar.png') }}" alt="User Avatar">
                </div>
                <div class="user-info">
                    <div class="user-name">{{ Auth::user()->name }}</div>
                    <div class="user-role">{{ Auth::user()->role ?? 'مستخدم' }}</div>
                </div>
            </div>
            <div class="logout-section">
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>تسجيل الخروج</span>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    /* ألوان ذهبية متناسقة */
    :root {
        --primary-gold: #d4af37;      /* ذهبي رئيسي */
        --secondary-gold: #b8941f;    /* ذهبي ثانوي */
        --dark-gold: #997515;         /* ذهبي غامق */
        --light-gold: #f0d78c;        /* ذهبي فاتح */
        --pale-gold: #fef3c7;         /* ذهبي باهت */
        
        /* ألوان داعمة */
        --bg-primary: #1a1f2e;        /* خلفية رئيسية */
        --bg-secondary: #151925;      /* خلفية ثانوية */
        --bg-card: #212637;           /* خلفية البطاقات */
        --text-primary: #e5e7eb;      /* نص رئيسي */
        --text-secondary: #9ca3af;    /* نص ثانوي */
        --text-muted: #6b7280;        /* نص باهت */
        
        /* ألوان الحالة */
        --success: #10b981;           /* أخضر */
        --warning: #f59e0b;           /* برتقالي */
        --danger: #ef4444;            /* أحمر */
        --info: #3b82f6;              /* أزرق */
        
        --sidebar-width: 260px;
    }
    
    /* الشريط الجانبي */
    .sidebar-container {
        position: fixed;
        top: 0;
        right: 0;
        height: 100vh;
        width: var(--sidebar-width);
        z-index: 1000;
        transition: all 0.3s ease;
        pointer-events: auto;
    }
    
    .sidebar {
        height: 100%;
        width: 100%;
        background: linear-gradient(180deg, var(--bg-primary) 0%, var(--bg-secondary) 100%);
        color: var(--text-primary);
        display: flex;
        flex-direction: column;
        box-shadow: -5px 0 15px rgba(0, 0, 0, 0.4);
        overflow-y: auto;
        overflow-x: hidden;
        border-left: 1px solid rgba(212, 175, 55, 0.15);
    }
    
    /* رأس الشريط الجانبي */
    .sidebar-header {
        padding: 20px;
        border-bottom: 1px solid rgba(212, 175, 55, 0.15);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: rgba(212, 175, 55, 0.05);
    }
    
    .logo-container {
        display: flex;
        align-items: center;
    }
    
    .logo {
        width: 45px;
        height: 45px;
        background: linear-gradient(135deg, var(--primary-gold), var(--secondary-gold));
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-left: 15px;
        font-size: 20px;
        color: var(--bg-primary);
        box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
    }
    
    .logo-text { 
        display: flex; 
        flex-direction: column; 
    }
    
    .logo-text .main-text { 
        font-weight: 700; 
        font-size: 18px; 
        line-height: 1.2;
        color: var(--primary-gold);
    }
    
    .logo-text .sub-text { 
        font-size: 13px; 
        color: var(--text-secondary);
    }
    
    .sidebar-toggle {
        background: none; 
        border: none; 
        color: var(--text-primary); 
        font-size: 18px; 
        cursor: pointer; 
        padding: 8px; 
        border-radius: 8px; 
        transition: all 0.2s ease;
    }
    
    .sidebar-toggle:hover { 
        background: rgba(212, 175, 55, 0.1); 
        color: var(--primary-gold);
    }
    
    /* قائمة الشريط الجانبي */
    .menu-list { 
        list-style: none; 
        margin: 0; 
        padding: 15px 0;
        flex: 1;
    }
    
    .menu-item { 
        position: relative;
        margin: 2px 12px;
    }
    
    .menu-item a { 
        display: flex; 
        align-items: center; 
        padding: 12px 15px;
        color: var(--text-secondary); 
        text-decoration: none; 
        transition: all 0.3s ease; 
        position: relative; 
        overflow: hidden;
        border-radius: 8px;
    }
    
    .menu-item a::before {
        content: '';
        position: absolute;
        right: 0;
        top: 0;
        height: 100%;
        width: 3px;
        background: var(--primary-gold);
        transform: scaleY(0);
        transition: transform 0.3s ease;
        border-radius: 3px 0 0 3px;
    }
    
    .menu-item:hover a {
        color: var(--text-primary);
        background: rgba(212, 175, 55, 0.08);
        transform: translateX(3px);
    }
    
    .menu-item:hover a::before { 
        transform: scaleY(1); 
    }
    
    .menu-item.active a { 
        background: rgba(212, 175, 55, 0.12); 
        color: var(--primary-gold);
        font-weight: 600;
    }
    
    .menu-item.active a::before { 
        transform: scaleY(1); 
    }
    
    .menu-item i { 
        margin-left: 12px; 
        font-size: 18px; 
        width: 20px; 
        text-align: center;
        transition: all 0.3s ease;
    }
    
    .menu-item:hover i {
        color: var(--primary-gold);
    }
    
    .menu-item.active i {
        color: var(--primary-gold);
    }
    
    .menu-item span { 
        font-weight: 500;
        font-size: 14px;
    }
    
    /* تذييل الشريط الجانبي */
    .sidebar-footer { 
        padding: 20px;
        border-top: 1px solid rgba(212, 175, 55, 0.15);
        background: rgba(212, 175, 55, 0.03);
        margin-top: auto;
    }
    
    .user-profile { 
        display: flex; 
        align-items: center;
        padding: 15px;
        border-radius: 10px;
        background: var(--bg-card);
        transition: all 0.3s ease;
        margin-bottom: 12px;
        border: 1px solid rgba(212, 175, 55, 0.1);
    }
    
    .user-profile:hover {
        background: rgba(212, 175, 55, 0.08);
        border-color: rgba(212, 175, 55, 0.2);
    }
    
    .user-avatar { 
        width: 45px; 
        height: 45px; 
        border-radius: 50%; 
        overflow: hidden; 
        margin-left: 15px; 
        border: 2px solid var(--primary-gold);
        box-shadow: 0 4px 8px rgba(212, 175, 55, 0.3);
        flex-shrink: 0;
    }
    
    .user-avatar img { 
        width: 100%; 
        height: 100%; 
        object-fit: cover;
    }
    
    .user-info { 
        flex: 1; 
        min-width: 0;
    }
    
    .user-name { 
        font-weight: 600; 
        font-size: 15px; 
        line-height: 1.2;
        color: var(--text-primary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .user-role { 
        font-size: 13px; 
        color: var(--text-secondary);
        margin-top: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    /* زر تسجيل الخروج */
    .logout-section {
        display: flex;
        justify-content: center;
    }
    
    .logout-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        padding: 12px 15px;
        background: linear-gradient(135deg, var(--danger), #dc2626);
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3);
        border: 1px solid rgba(239, 68, 68, 0.2);
    }
    
    .logout-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(239, 68, 68, 0.4);
        background: linear-gradient(135deg, #ef4444, #b91c1c);
    }
    
    .logout-btn:active {
        transform: translateY(0);
    }
    
    .logout-btn i {
        margin-left: 8px;
        font-size: 16px;
    }
    
    /* للشاشات الصغيرة */
    @media (max-width: 992px) {
        .sidebar-container { 
            width: 0; 
            overflow: hidden; 
        }
        
        .sidebar-container.active { 
            width: var(--sidebar-width); 
        }
    }
    
    /* تحسين التمرير */
    .sidebar::-webkit-scrollbar {
        width: 6px;
    }
    
    .sidebar::-webkit-scrollbar-track {
        background: rgba(212, 175, 55, 0.05);
        border-radius: 3px;
    }
    
    .sidebar::-webkit-scrollbar-thumb {
        background: var(--primary-gold);
        border-radius: 3px;
    }
    
    .sidebar::-webkit-scrollbar-thumb:hover {
        background: var(--secondary-gold);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // تبديل الشريط الجانبي للشاشات الصغيرة
        const sidebarToggle = document.querySelector('.sidebar-toggle');
        const sidebarContainer = document.querySelector('.sidebar-container');
        
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                sidebarContainer.classList.toggle('active');
            });
        }
        
        // إغلاق الشريط الجانبي عند النقر على رابط
        const menuLinks = document.querySelectorAll('.menu-item a');
        menuLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 992) {
                    sidebarContainer.classList.remove('active');
                }
            });
        });
        
        // إغلاق الشريط الجانبي عند النقر خارجها
        document.addEventListener('click', function(event) {
            if (window.innerWidth <= 992) {
                if (!sidebarContainer.contains(event.target) && 
                    !sidebarToggle.contains(event.target) && 
                    sidebarContainer.classList.contains('active')) {
                    sidebarContainer.classList.remove('active');
                }
            }
        });
    });
</script>