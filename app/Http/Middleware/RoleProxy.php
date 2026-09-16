<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleProxy
{
    /**
     * يعمل كـ Protection Proxy لربط وتصفية الطلبات قبل وصولها للـ Controller
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        // 1. فحص وجود المستخدم (Authentication Check)
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'غير مصرح: يرجى تسجيل الدخول أولاً'
            ], 401);
        }

        // 2. فحص حالة الحساب
        if ($user->status !== 'active') {
            return response()->json([
                'status'  => false,
                'message' => 'وصول مرفوض: هذا الحساب معطل'
            ], 403);
        }

        // 3. فحص الدور (Authorization / RBAC Check)
        $userRole = $user->role ? $user->role->name : null;
        if (!in_array($userRole, $roles)) {
            return response()->json([
                'status'  => false,
                'message' => 'وصول ممنوع: لا تملك الصلاحية لتنفيذ هذا الإجراء'
            ], 403);
        }

        // إمرار الطلب إلى الـ Real Subject (Controller)
        return $next($request);
    }
}