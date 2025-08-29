<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول | نظام إدارة محل الذهب</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root{
            --gold: #d4af37;
            --gold-dark: #b8941f;
            --gold-light: #f0d78c;
            --ink: #0f172a;
            --bg: #0a0e1a;
            --card: #0f1729;
            --text: #e5e7eb;
            --text-secondary: #94a3b8;
            --error: #ef4444;
            --success: #10b981;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Cairo', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(212, 175, 55, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 90% 80%, rgba(212, 175, 55, 0.07) 0%, transparent 50%);
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
            background: linear-gradient(145deg, var(--card), #0d1320);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(212, 175, 55, 0.2);
        }
        
        .login-header {
            background: linear-gradient(90deg, var(--gold), var(--gold-dark));
            padding: 25px 20px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .login-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.2) 0%, transparent 70%);
            transform: rotate(45deg);
        }
        
        .logo {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 1;
        }
        
        .logo-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #fff, #f0d78c 30%, #d4af37 70%, #b8941f 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 20px rgba(212, 175, 55, 0.5), inset 0 0 10px rgba(0, 0, 0, 0.2);
            margin-bottom: 15px;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
        
        .logo-icon i {
            font-size: 28px;
            color: var(--ink);
        }
        
        .logo-text {
            font-size: 20px;
            font-weight: 700;
            color: var(--ink);
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        
        .login-body {
            padding: 30px 25px;
        }
        
        .login-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 10px;
            text-align: center;
            color: var(--text);
        }
        
        .login-subtitle {
            font-size: 14px;
            color: var(--text-secondary);
            text-align: center;
            margin-bottom: 25px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 14px;
            color: var(--text);
        }
        
        .form-control {
            position: relative;
        }
        
        .form-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gold);
            font-size: 16px;
        }
        
        .form-input {
            width: 100%;
            padding: 14px 15px 14px 45px;
            background: rgba(15, 22, 38, 0.7);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 10px;
            color: var(--text);
            font-size: 15px;
            font-family: 'Cairo', sans-serif;
            transition: all 0.3s ease;
            outline: none;
        }
        
        .form-input::placeholder {
            color: var(--text-secondary);
        }
        
        .form-input:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 4px rgba(212, 175, 55, 0.2);
            background: rgba(15, 22, 38, 0.9);
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(90deg, var(--gold), var(--gold-dark));
            color: var(--ink);
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 15px rgba(212, 175, 55, 0.3);
            margin-top: 10px;
            font-family: 'Cairo', sans-serif;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 20px rgba(212, 175, 55, 0.4);
        }
        
        .btn-login:active {
            transform: translateY(1px);
        }
        
        .error-message {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fecaca;
            padding: 12px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .login-footer {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid rgba(148, 163, 184, 0.1);
            font-size: 13px;
            color: var(--text-secondary);
        }
        
        /* تأثيرات حركية */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .login-container {
            animation: fadeIn 0.6s ease forwards;
        }
        
        /* للشاشات الصغيرة */
        @media (max-width: 480px) {
            .login-container {
                max-width: 100%;
            }
            
            .login-body {
                padding: 25px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-gem"></i>
                </div>
                <div class="logo-text">نظام إدارة محل الذهب</div>
            </div>
        </div>
        
        <div class="login-body">
            <h1 class="login-title">تسجيل الدخول</h1>
            <p class="login-subtitle">أدخل بيانات حسابك للوصول إلى لوحة التحكم</p>
            
            @if($errors->any())
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}
                </div>
            @endif
            
            <form method="POST" action="{{ route('login.post') }}" novalidate>
                @csrf
                <div class="form-group">
                    <label for="username" class="form-label">اسم المستخدم</label>
                    <div class="form-control">
                        <i class="fas fa-user form-icon"></i>
                        <input 
                            id="username" 
                            type="text" 
                            name="username" 
                            value="{{ old('username') }}" 
                            placeholder="أدخل اسم المستخدم" 
                            class="form-input"
                            required 
                            autofocus
                        >
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">كلمة المرور</label>
                    <div class="form-control">
                        <i class="fas fa-lock form-icon"></i>
                        <input 
                            id="password" 
                            type="password" 
                            name="password" 
                            placeholder="أدخل كلمة المرور" 
                            class="form-input"
                            required
                        >
                    </div>
                </div>
                
                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt" style="margin-left: 8px;"></i>
                    دخول إلى النظام
                </button>
            </form>
            
            <div class="login-footer">
                © {{ date('Y') }} نظام إدارة محل الذهب — جميع الحقوق محفوظة
            </div>
        </div>
    </div>
</body>
</html>