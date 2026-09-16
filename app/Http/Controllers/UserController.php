<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Factories\UserFactory; // استدعاء الفاكتوري
use App\Models\User;
use Exception;

class UserController extends Controller
{
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
                'full_name' =>'required|string|max:150',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:4',
            ]
        );

        
        try
        {
            $contextmangment = UserFactory::create($validated , 'content_manager');

            return response()->json([
               'status'  => true,
               'massage' => 'جاهز ال  context mangment',
               'data' => $contextmangment
            ],200);
        }

       catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'حدث خطأ أثناء عملية الإنشاء',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}