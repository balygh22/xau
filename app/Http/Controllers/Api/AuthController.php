<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    // POST /api/login
    public function login(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // ابحث المستخدم (اسم الحقل legacy: UserName)
        $user = User::where('UserName', $data['username'])->first();

        // إن كانت كلمات المرور legacy غير bcrypt، استبدل الشرط التالي بمنطقك القديم
        if (!$user || !Hash::check($data['password'], $user->PasswordHash)) {
            return response()->json(['message' => 'بيانات الدخول غير صحيحة'], 401);
        }

        if (isset($user->IsActive) && (int)$user->IsActive === 0) {
            return response()->json(['message' => 'المستخدم غير فعّال'], 403);
        }

        $token = JWTAuth::fromUser($user, [
            // Claims اختيارية
            'uid'  => $user->UserID,
            'name' => $user->FullName,
        ]);

        return response()->json(['token' => $token], 200);
    }

    // POST /api/logout
    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken()); // requires blacklist enabled
        return response()->json(['message' => 'تم تسجيل الخروج'], 200);
    }

    // POST /api/refresh
    public function refresh()
    {
        return response()->json(['token' => JWTAuth::refresh()]);
    }

    // GET /api/me
    public function me()
    {
        $user = JWTAuth::user();
        return response()->json([
            'UserID'   => $user->UserID,
            'FullName' => $user->FullName,
            'UserName' => $user->UserName ?? null,
            'Email'    => $user->Email ?? null,
            'GroupID'  => $user->GroupID ?? null,
        ]);
    }
}
