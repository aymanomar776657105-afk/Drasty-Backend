<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use Illuminate\Validation\Rule;
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

    // 2. تعديل قسم موجود (Update)
    public function update(Request $request, $id)
    {
        $department = Department::find($id);

        if (!$department) {
            return response()->json([
                'status'  => false,
                'message' => 'القسم المطلوب غير موجود.'
            ], 404);
        }

        // استثناء المعرف الحالي من فحص الفرادة للسماح بحفظ التعديل دون خطأ تكرار
        $validated = $request->validate([
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('departments', 'name')->ignore($id, 'department_id')
            ],
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:10',
                Rule::unique('departments', 'code')->ignore($id, 'department_id')
            ],
        ], [
            'name.unique' => 'اسم هذا القسم مستخدم بالفعل لقسم آخر.',
            'code.unique' => 'رمز القسم مستخدم بالفعل لقسم آخر.',
        ]);

        $department->update($validated);

        return response()->json([
            'status'  => true,
            'message' => 'تم تحديث بيانات القسم بنجاح.',
            'data'    => $department
        ], 200);
    }

    // 3. حذف قسم (Destroy)
    public function destroy($id)
    {
        $department = Department::find($id);

        if (!$department) {
            return response()->json([
                'status'  => false,
                'message' => 'القسم المطلوب غير موجود.'
            ], 404);
        }

        try {
            // التحقق البرمجي لمنع خرق التكامل المرجعي (Integrity Constraint Violation)
            if (method_exists($department, 'levels') && $department->levels()->exists()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'لا يمكن حذف القسم نظراً لوجود مستويات دراسية تابعة له ومسجلة في النظام.'
                ], 409);
            }

            $department->delete();

            return response()->json([
                'status'  => true,
                'message' => 'تم حذف القسم بنجاح.'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'تعذر حذف القسم نظراً لارتباطه ببيانات أكاديمية أخرى.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
