<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Factories\UserFactory; // استدعاء الفاكتوري
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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



    // *********************************************************
    public function indexStaff(Request $request)
    {
        // بناء الاستعلام لحصر النتائج في المشرفين (1) ومدراء المحتوى (2)
        $query = User::whereIn('role_id', [1, 2]);

        // إمكانية الفلترة حسب الدور (مثلاً: ?role_id=1 للمشرفين فقط أو ?role_id=2 لمدراء المحتوى)
        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        // إمكانية الفلترة حسب الحالة (active / inactive)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $staff = $query->select([
            'user_id',
            'full_name',
            'name_ar',
            'name_en',
            'email',
            'role_id',
            'status',
            'created_at'
        ])->get();

        return response()->json([
            'status' => true,
            'count'  => $staff->count(),
            'data'   => $staff
        ], 200);
    }

    // 2. تعديل بيانات مشرف أو مدير محتوى (Update / Put)
    public function updateStaff(Request $request, int $id)
    {
        // استرجاع المستخدم والتأكد من أنه ينتمي لفئة الإدارة (role_id 1 أو 2)
        $user = User::whereIn('role_id', [1, 2])->find($id);

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'المستخدم المطلوب غير موجود ضمن المشرفين أو مدراء المحتوى.'
            ], 404);
        }

        $validated = $request->validate([
            'full_name' => [
                'sometimes',
                'required',
                'string',
                'max:150',
                Rule::unique('users', 'full_name')->ignore($id, 'user_id')
            ],
            'name_ar'  => 'sometimes|nullable|string|max:150',
            'name_en'  => 'sometimes|nullable|string|max:150',
            'email'    => [
                'sometimes',
                'required',
                'email',
                'max:191',
                Rule::unique('users', 'email')->ignore($id, 'user_id')
            ],
            'password' => 'sometimes|nullable|string|min:6',
            'role_id'  => 'sometimes|required|in:1,2', // 1: مشرف، 2: مدير محتوى
            'status'   => 'sometimes|required|in:active,inactive',
        ], [
            'full_name.unique' => 'الاسم الكامل مسجل بالفعل لحساب آخر.',
            'email.unique'     => 'البريد الإلكتروني مستخدم بالفعل.',
            'role_id.in'       => 'نوع الصلاحية يجب أن يكون 1 (مشرف) أو 2 (مدير محتوى).',
            'password.min'     => 'كلمة المرور يجب ألا تقل عن 6 خانات.',
        ]);

        try {
            $dataToUpdate = $request->only([
                'full_name',
                'name_ar',
                'name_en',
                'role_id',
                'status'
            ]);

            // تحديث الإيميل يدويًا أو اشتقاقه تلقائيًا عند تعديل name_en إذا لم يرسل إيميل مخصص
            if ($request->filled('email')) {
                $dataToUpdate['email'] = $request->email;
            } elseif ($request->filled('name_en') && !$request->has('email')) {
                $cleanPrefix = Str::lower(preg_replace('/[^a-zA-Z0-9]+/', '.', trim($request->name_en)));
                $cleanPrefix = trim($cleanPrefix, '.');
                $newEmail = $cleanPrefix . '@drasty.com';

                $emailExists = User::where('email', $newEmail)
                    ->where('user_id', '!=', $id)
                    ->exists();

                $dataToUpdate['email'] = $emailExists ? $cleanPrefix . '.' . $id . '@drasty.com' : $newEmail;
            }

            // تشفير كلمة المرور إذا تم إدخالها
            if ($request->filled('password')) {
                $dataToUpdate['password'] = Hash::make($request->password);
            }

            $user->update($dataToUpdate);

            return response()->json([
                'status'  => true,
                'message' => 'تم تحديث بيانات الحساب بنجاح.',
                'data'    => $user->fresh() // استدعاؤها بدون معاملات لإعادة جلب السجل بالكامل
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'فشلت عملية التعديل.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // 3. حذف مشرف أو مدير محتوى (Destroy / Delete)
    // دالة حذف المشرف أو مدير المحتوى بعد تصحيحها
    public function destroyStaff(int $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'الحساب المطلوب غير موجود.'
            ], 404);
        }

        // 1. حماية حساب السوبر أدمن الأساسي من الحذف
        if ($user->role_id === 0) {
            return response()->json([
                'status'  => false,
                'message' => 'محظور: لا يمكن حذف حساب مدير النظام الأساسي (Super Admin).'
            ], 403);
        }

        // 2. التحقق من أن الحساب ينتمي للمشرفين أو مدراء المحتوى
        if (!in_array($user->role_id, [1, 2])) {
            return response()->json([
                'status'  => false,
                'message' => 'هذه الدالة مخصصة لحذف المشرفين ومدراء المحتوى فقط.'
            ], 400);
        }

        try {
            // حذف المستخدم مباشرة
            $user->delete();

            return response()->json([
                'status'  => true,
                'message' => 'تم حذف الحساب بنجاح.',
                'data'    => [
                    'user_id'   => $id,
                    'full_name' => $user->full_name
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'تعذر حذف الحساب لوجود سجلات مرتبطة به في النظام.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
