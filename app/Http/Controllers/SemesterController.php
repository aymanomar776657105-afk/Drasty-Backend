<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Semester;
use Exception;

class SemesterController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'level_id'        => 'required|exists:levels,level_id',
            'name'            => 'required|string|max:100',
            'semester_number' => 'required|integer|min:1|max:3',
            'academic_year'   => 'required|string|max:20', // مثال: 2024/2025
        ]);

        try {
            $semester = Semester::create($validated);

            return response()->json([
                'status'  => true,
                'message' => 'تمت إضافة الفصل الدراسي بنجاح',
                'data'    => $semester->load('level')
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'فشلت إضافة الفصل الدراسي',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request)
    {
        $query = Semester::with([
            'level:level_id,department_id,name',
            'level.department:department_id,name,code'
        ]);

        // فلترة اختيارية حسب المستوى الدراسي أو السنة الأكاديمية
        if ($request->filled('level_id')) {
            $query->where('level_id', $request->level_id);
        }

        if ($request->filled('academic_year')) {
            $query->where('academic_year', $request->academic_year);
        }

        $semesters = $query->orderBy('academic_year', 'desc')
                           ->orderBy('semester_number', 'asc')
                           ->get();

        // تسطيح بنية الـ JSON لتسهيل التعامل معها برمجياً
        $flattened = $semesters->map(function ($sem) {
            return [
                'semester_id'     => $sem->semester_id,
                'semester_name'   => $sem->name,
                'semester_number' => $sem->semester_number,
                'academic_year'   => $sem->academic_year,
                'level_id'        => $sem->level_id,
                'level_name'      => $sem->level->name ?? 'غير محدد',
                'department_name' => $sem->level->department->name ?? 'غير محدد',
                'department_code' => $sem->level->department->code ?? null,
            ];
        });

        return response()->json([
            'status' => true,
            'count'  => $flattened->count(),
            'data'   => $flattened
        ], 200);
    }

    // 2. تعديل بيانات الفصل الدراسي (Update)
    public function update(Request $request, int $id)
    {
        $semester = Semester::find($id);

        if (!$semester) {
            return response()->json([
                'status'  => false,
                'message' => 'الفصل الدراسي المطلوب غير موجود.'
            ], 404);
        }

        $validated = $request->validate([
            'level_id'        => 'sometimes|required|exists:levels,level_id',
            'name'            => 'sometimes|required|string|max:100',
            'semester_number' => 'sometimes|required|integer|in:1,2,3',
            'academic_year'   => 'sometimes|required|string|max:20',
        ], [
            'level_id.exists'         => 'المستوى الدراسي المحدد غير مسجل بالنظام.',
            'semester_number.in'      => 'رقم الفصل يجب أن يكون 1 أو 2 أو 3.',
            'academic_year.string'    => 'صيغة السنة الأكاديمية غير صحيحة.',
        ]);

        $semester->update($validated);

        return response()->json([
            'status'  => true,
            'message' => 'تم تحديث بيانات الفصل الدراسي بنجاح.',
            'data'    => $semester
        ], 200);
    }

    // 3. حذف الفصل الدراسي (Destroy)
    public function destroy(int $id)
    {
        $semester = Semester::find($id);

        if (!$semester) {
            return response()->json([
                'status'  => false,
                'message' => 'الفصل الدراسي المطلوب غير موجود.'
            ], 404);
        }

        try {
            // منع الحذف إذا كانت هناك مواد مطروحة مسجلة في هذا الترم
            if (method_exists($semester, 'courseOfferings') && $semester->courseOfferings()->exists()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'لا يمكن حذف الفصل الدراسي لوجود مواد دراسية مطروحة ومرتبطة به.'
                ], 409);
            }

            $semester->delete();

            return response()->json([
                'status'  => true,
                'message' => 'تم حذف الفصل الدراسي بنجاح.'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'تعذر حذف الفصل الدراسي نظراً لارتباطه بسجلات أخرى.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}