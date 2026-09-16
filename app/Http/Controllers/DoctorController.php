<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Doctor;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Factories\UserFactory;
use Illuminate\Support\Str;


class DoctorController extends Controller
{
    
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
}