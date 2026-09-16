<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use Exception;

class DepartmentController extends Controller
{
    // جلب قائمة الأقسام لتغذية القوائم المنسدلة في الفرونت إند
    public function index()
    {
        $departments = Department::select('department_id', 'name', 'code') ->get();

        return response()->json([
            'status' => true,
            'count'  => $departments->count(),
            'data'   => $departments
        ], 200);
    }

    // مسار اختياري لإنشاء قسم جديد
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:150|unique:departments,name',
            'code'        => 'required|string|max:50|unique:departments,code',
        ]);

        try {
            $department = Department::create($validated);

            return response()->json([
                'status'  => true,
                'message' => 'تم إنشاء القسم بنجاح',
                'data'    => $department
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'فشلت إضافة القسم',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}