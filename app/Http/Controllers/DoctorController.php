<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Factories\UserFactory;
use Illuminate\Support\Str;


class DoctorController extends Controller
{
    public function index()
    {
        $doctors = Doctor::with('user:user_id,full_name,email')->get();

        return response()->json([
            'status' => true,
            'count'  => $doctors->count(),
            'data'   => $doctors
        ], 200);
    }

    public function store(Request $request)
    {
        // 1. توحيد الأسماء مع ما هو موجود في قاعدة البيانات والـ Factory
        $validated = $request->validate([
            'name_ar' => 'required|string|max:150|unique:users,name_ar',
            'name_en' => 'required|string|max:150|unique:users,name_en',
        ], [
            'name_ar.unique' => 'اسم الدكتور بالعربي مسجل مسبقاً',
            'name_en.unique' => 'اسم الدكتور بالإنجليزي مسجل مسبقاً',
        ]);

        try {
            $doctor = DB::transaction(function () use ($validated) {

                // 2. تنظيف الاسم الإنجليزي من الفراغات وتوليد إيميل صالح وفريد
                $cleanSlug = Str::slug($validated['name_en'], '.');
                $generatedEmail = $cleanSlug . '.' . '@drasty.com';
                $defaultPassword = 'Drasty.pass';

                // 3. إنشاء حساب المستخدم في جدول users
                $user = UserFactory::create([
                    'full_name' => $validated['name_ar'],
                    'name_ar'  => $validated['name_ar'],
                    'name_en'  => $validated['name_en'],
                    'email'    => $generatedEmail,
                    'password' => $defaultPassword,
                ], 'Doctor');

                // 4. ربط السجل بجدول doctors
                return Doctor::create([
                    'user_id' => $user->user_id,
                ]);
            });

            return response()->json([
                'status'  => true,
                'message' => 'تم تسجيل الدكتور وإنشاء حسابه بنجاح',
                'data'    => $doctor->load('user')
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'فشلت عملية إضافة الدكتور',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // 1. تعديل بيانات الدكتور وبيانات حسابه (Update)
    // 1. تعديل بيانات الدكتور وبيانات حسابه (Update)
    public function update(Request $request, int $id)
    {
        $doctor = Doctor::with('user')->find($id);

        if (!$doctor) {
            return response()->json([
                'status'  => false,
                'message' => 'سجل الدكتور المطلوب غير موجود.'
            ], 404);
        }

        $userId = $doctor->user_id;

        // التحقق من المدخلات وجعل full_name فريداً مع استثناء الحساب الحالي
        $validated = $request->validate([
            'full_name' => [
                'sometimes',
                'required',
                'string',
                'max:150',
                Rule::unique('users', 'full_name')->ignore($userId, 'user_id')
            ],
            'name_ar'       => 'sometimes|nullable|string|max:150',
            'name_en'       => 'sometimes|nullable|string|max:150',
            'department_id' => 'sometimes|required|exists:departments,department_id',
            'password'      => 'sometimes|nullable|string|min:6',
        ], [
            'full_name.required'   => 'الاسم الكامل مطلوب.',
            'full_name.unique'     => 'هذا الاسم مسجل مسبقاً لمستخدم آخر.',
            'department_id.exists' => 'القسم المحدد غير موجود بالنظام.',
            'password.min'         => 'كلمة المرور يجب ألا تقل عن 6 أحرف.',
        ]);

        try {
            DB::transaction(function () use ($request, $doctor, $userId) {
                // 1. تحديث القسم في جدول doctors إن وُجد
                if ($request->filled('department_id')) {
                    $doctor->update(['department_id' => $request->department_id]);
                }

                // 2. تجهيز بيانات المستخدم في جدول users
                $userData = [];

                if ($request->has('full_name')) {
                    $userData['full_name'] = $request->full_name;
                }

                if ($request->has('name_ar')) {
                    $userData['name_ar'] = $request->name_ar;
                }

                // تحديث الاسم الإنجليزي واشتقاق الإيميل تلقائياً على أساسه
                if ($request->filled('name_en')) {
                    $userData['name_en'] = $request->name_en;

                    // تحويل الاسم الإنجليزي إلى صيغة إيميل: (Ahmed Ali -> ahmed.ali@drasty.com)
                    $cleanPrefix = Str::lower(preg_replace('/[^a-zA-Z0-9]+/', '.', trim($request->name_en)));
                    $cleanPrefix = trim($cleanPrefix, '.');
                    $newEmail = $cleanPrefix . '@drasty.com';

                    // التأكد من عدم تكرار الإيميل لمستخدم آخر
                    $emailExists = User::where('email', $newEmail)
                        ->where('user_id', '!=', $userId)
                        ->exists();

                    if ($emailExists) {
                        $newEmail = $cleanPrefix . '.' . $doctor->doctor_id . '@drasty.com';
                    }

                    $userData['email'] = $newEmail;
                }

                // تشفير وتحديث كلمة المرور إذا تم إرسالها
                if ($request->filled('password')) {
                    $userData['password'] = Hash::make($request->password);
                }

                // تنفيذ التحديث في جدول users
                if (!empty($userData) && $doctor->user) {
                    $doctor->user->update($userData);
                }
            });

            return response()->json([
                'status'  => true,
                'message' => 'تم تحديث بيانات الدكتور وحسابه بنجاح.',
                'data'    => $doctor->fresh([
                    'user:user_id,full_name,name_ar,name_en,email,status',
                    'department:department_id,name'
                ])
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'فشلت عملية تحديث بيانات الدكتور.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // 2. حذف الدكتور مع حماية التكامل المرجعي (Destroy)
    // داخل DoctorController.php
    public function destroy(int $id)
    {
        $doctor = Doctor::find($id);

        if (!$doctor) {
            return response()->json([
                'status'  => false,
                'message' => 'سجل الدكتور المطلوب غير موجود.'
            ], 404);
        }

        try {
            DB::transaction(function () use ($doctor) {
                $doctorId = $doctor->doctor_id;
                $userId   = $doctor->user_id;

                // 1. حذف ارتباطات المواد المسندة لتدريسها من جدول course_doctors
                DB::table('course_doctors')->where('doctor_id', $doctorId)->delete();

                // 2. حذف سجل الدكتور من جدول doctors
                $doctor->delete();

                // 3. حذف حساب المستخدم المرتبط به من جدول users
                if ($userId) {
                    DB::table('users')->where('user_id', $userId)->delete();
                }
            });

            return response()->json([
                'status'  => true,
                'message' => 'تم حذف الدكتور وحسابه وجميع ارتباطاته الأكاديمية بنجاح.',
                'data'    => [
                    'doctor_id' => $id
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'تعذر حذف الدكتور وارتباطاته.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
