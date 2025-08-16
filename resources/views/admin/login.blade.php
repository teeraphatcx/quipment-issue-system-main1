{{-- resources/views/admin/login.blade.php --}}
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login - จึ้ง ๆ</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600&display=swap');

    body, html {
        height: 100%;
        margin: 0;
        font-family: 'Sarabun', sans-serif;
        background: linear-gradient(135deg, #ff9a3c, #ffd633, #ff6f3c, #ffe066);
        background-size: 400% 400%;
        animation: gradientBG 15s ease infinite;
        overflow: hidden;
    }

    @keyframes gradientBG {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    /* วงกลมเคลื่อนไหว */
    .circle {
        position: absolute;
        border-radius: 50%;
        opacity: 0.4;
        animation: float 10s linear infinite;
    }

    @keyframes float {
        0% { transform: translateY(0) translateX(0); }
        50% { transform: translateY(-50px) translateX(30px); }
        100% { transform: translateY(0) translateX(0); }
    }

    .circle1 { width: 100px; height: 100px; background: #fff200; top: 10%; left: 15%; animation-duration: 12s; }
    .circle2 { width: 80px; height: 80px; background: #ff6f3c; top: 60%; left: 70%; animation-duration: 15s; }
    .circle3 { width: 120px; height: 120px; background: #ffd633; top: 40%; left: 40%; animation-duration: 18s; }

    .login-card {
        border-radius: 15px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.25);
        padding: 40px 30px;
        background-color: rgba(255,255,255,0.95);
        max-width: 400px;
        width: 100%;
        position: relative;
        z-index: 10;
    }

    .login-title {
        color: #ff6f3c;
        font-weight: 600;
        text-align: center;
        margin-bottom: 25px;
    }

    .form-control:focus {
        border-color: #ff6f3c;
        box-shadow: 0 0 0 0.2rem rgba(255,111,60,.25);
    }

    .btn-primary {
        background-color: #ff6f3c;
        border-color: #ff6f3c;
        font-weight: 500;
    }

    .btn-primary:hover {
        background-color: #e65c2d;
        border-color: #e65c2d;
    }

    .alert {
        border-radius: 8px;
    }

    .login-footer {
        text-align: center;
        margin-top: 15px;
        font-size: 14px;
        color: #6c757d;
    }
</style>
</head>
<body>

{{-- วงกลมขยับ --}}
<div class="circle circle1"></div>
<div class="circle circle2"></div>
<div class="circle circle3"></div>

<div class="d-flex justify-content-center align-items-center h-100">
    <div class="login-card">
        <h3 class="login-title">เข้าสู่ระบบแอดมิน</h3>

        {{-- ข้อความแจ้งเตือน --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                {{ $errors->first() }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label">อีเมล</label>
                <input id="email" type="email" name="email" class="form-control" required autofocus value="{{ old('email') }}">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">รหัสผ่าน</label>
                <input id="password" type="password" name="password" class="form-control" required>
            </div>
            <button class="btn btn-primary w-100 mb-3" type="submit">เข้าสู่ระบบ</button>
        </form>

        <div class="login-footer">
            &copy; {{ date('Y') }} ระบบจัดการแอดมิน
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
