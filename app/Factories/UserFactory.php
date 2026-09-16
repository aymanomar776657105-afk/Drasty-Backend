<?php

namespace App\Factories;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Exception;

class UserFactory
{

    public static function create(array $data, string $roleName): User
    {
        // 1. جلب سجل الدور من قاعدة البيانات
        $role = Role::where('name', $roleName)->first();

        if (!$role) {
            throw new Exception("الدور المحدّد غير موجود: {$roleName}");
        }

        if ($roleName === 'Doctor') {
            return User::create([
                'full_name' => $data['full_name'],
                'name_ar' => $data['name_ar'],
                'name_en' => $data['name_en'],
                'email'     => $data['email'],
                'password'  => Hash::make($data['password']),
                'role_id'   => $role->role_id,
                'status'    => 'active',
            ]);
        }
        // 2. تجميع البيانات وتشفير كلمة المرور وتخزين الحساب
        return User::create([
            'full_name' => $data['full_name'] ?? $data['name_ar'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'role_id'   => $role->role_id,
            'status'    => 'active',
        ]);
    }
}
