<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Factories\UserFactory; // استدعاء الفاكتوري
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function checkUser(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'بيانات الدخول غير صحيحة.'
            ], 401);
        }

        $isMatch = false;

        // التحقق مما إذا كانت كلمة المرور في الداتابيز مشفرة أم نصاً صريحاً
        $passwordInfo = password_get_info($user->password);

        if ($passwordInfo['algo'] !== null && $passwordInfo['algo'] !== 0) {
            // مشفرة: المقارنة عبر Bcrypt
            $isMatch = Hash::check($request->password, $user->password);
        } else {
            // نص صريح (Plain Text): مقارنة نصية عادية
            $isMatch = ($request->password === $user->password);

            // ترقية أمنية تلقائية: تشفيرها وحفظها كـ Hash لمنع تكرار الخطأ
            if ($isMatch) {
                $user->password = Hash::make($request->password);
                $user->save();
            }
        }

        if (!$isMatch) {
            return response()->json([
                'status'  => false,
                'message' => 'بيانات الدخول غير صحيحة.'
            ], 401);
        }

        return response()->json([
            'status'  => true,
            'message' => 'تم التحقق بنجاح',
            'data'    => [
                'user_id'   => $user->user_id,
                'full_name' => $user->full_name,
                'email'     => $user->email,
                'role'      => $user->role ?? null,
            ]
        ], 200);
    }
    public function addAdmin(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:150',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:8',
        ]);

        try {
            // تفويض إنشاء الكائن لمصنع المستخدمين
            $admin = UserFactory::create($validated, 'Admin');

            return response()->json([
                'status'  => true,
                'message' => 'تم إنشاء حساب الأدمن بنجاح باستخدام Factory Pattern',
                'data'    => $admin
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'حدث خطأ أثناء عملية الإنشاء',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    public function addContextMangmet(Request $request)
    {
        $validated = $request->validate(
            [
                'full_name' => 'required|string|max:150',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:4',
            ]
        );


        try {
            $contextmangment = UserFactory::create($validated, 'content_manager');

            return response()->json([
                'status'  => true,
                'massage' => 'جاهز ال  context mangment',
                'data' => $contextmangment
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'حدث خطأ أثناء عملية الإنشاء',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
