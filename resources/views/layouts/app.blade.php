<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'نظام إدارة المتجر')</title>
    
    <!-- إضافة Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- إضافة Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- إضافة خطوط عربية -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    
    @stack('head')
    
    <style>
        :root {
            --sidebar-width: 280px;
            --purple-1: #667eea;
            --purple-2: #764ba2;
        }
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
            direction: rtl;
        }
        .main-content {
            margin-right: var(--sidebar-width);
            transition: all 0.3s ease;
            min-height: 100vh;
            padding: 16px; /* مساحة داخلية حتى لا يلتصق المحتوى بحافة الشاشة */
        }
        @media (max-width: 992px) {
            .main-content {
                margin-right: 0;
            }
        }
        /* زر فتح الشريط الجانبي للشاشات الصغيرة */
        .sidebar-open-btn {
            position: fixed;
            top: 15px;
            right: 15px;
            z-index: 1001;
            background: var(--purple-1);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .sidebar-open-btn:hover {
            background: var(--purple-2);
            transform: scale(1.05);
        }
        @media (min-width: 993px) {
            .sidebar-open-btn { display: none; }
        }
    </style>
</head>
<body>
    <!-- الشريط الجانبي -->
    @include('includes.sidebar')
    
    <!-- زر فتح الشريط الجانبي للشاشات الصغيرة -->
    <button class="sidebar-open-btn d-lg-none" id="sidebarOpenBtn">
        <i class="fas fa-bars"></i>
    </button>
    
    <!-- المحتوى الرئيسي -->
    <div class="main-content">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @yield('content')
    </div>
    
    <!-- إضافة Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @stack('scripts')
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // فتح الشريط الجانبي للشاشات الصغيرة
            const sidebarOpenBtn = document.getElementById('sidebarOpenBtn');
            const sidebarContainer = document.querySelector('.sidebar-container');
            
            if (sidebarOpenBtn && sidebarContainer) {
                sidebarOpenBtn.addEventListener('click', function() {
                    sidebarContainer.classList.add('active');
                });
            }
            
            // إغلاق الشريط الجانبي عند النقر خارجه
            document.addEventListener('click', function(event) {
                if (window.innerWidth <= 992) {
                    if (!sidebarContainer.contains(event.target) && 
                        !sidebarOpenBtn.contains(event.target) && 
                        sidebarContainer.classList.contains('active')) {
                        sidebarContainer.classList.remove('active');
                    }
                }
            });
        });
    </script>
</body>
</html>