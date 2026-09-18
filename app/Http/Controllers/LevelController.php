<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Level;
use Illuminate\Support\Facades\DB;
use Exception;

class LevelController extends Controller
{
    // 1. عرض جميع المستويات مع الأقسام التابعة لها (Index)
public function index(Request $request)
{
    $query = Level::with(['department:department_id,name,code']);

    // فلترة اختيارية: عرض مستويات قسم معين عبر Query Parameter (?department_id=1)
    if ($request->filled('department_id')) {
        $query->where('department_id', $request->department_id);
    }

    $levels = $query->orderBy('level_number', 'asc')->get();

    // تسطيح بنية الـ JSON لتسهيل التعامل معها في الفرونت إند
    $flattened = $levels->map(function ($level) {
        return [
            'level_id'        => $level->level_id,
            'level_name'      => $level->name,
            'level_number'    => $level->level_number,
            'department_id'   => $level->department_id,
            'department_name' => $level->department->name ?? 'غير محدد',
            'department_code' => $level->department->code ?? null,
        ];
    });

    return response()->json([
        'status' => true,
        'count'  => $flattened->count(),
        'data'   => $flattened
    ], 200);
}
    public function store(Request $request)
    {
        // 1. التحقق من صحة المصفوفة والأقسام المحددة
        $validated = $request->validate([
            'department_ids'   => 'required|array|min:1',
            'department_ids.*' => 'required|integer|exists:departments,department_id',
            'name'             => 'required|string|max:100',
            'level_number'     => 'required|integer|min:1|max:6',
        ], [
            'department_ids.required' => 'يجب اختيار قسم واحد على الأقل.',
            'department_ids.*.exists' => 'أحد الأقسام المحددة غير موجود في النظام.',
            'name.required'           => 'اسم المستوى مطلوب.',
            'level_number.required'   => 'رقم المستوى مطلوب.',
        ]);

        try {
            // 2. تنفيذ الإدخال المجمع داخل Transaction لضمان الموثوقية التامة
            $createdLevels = DB::transaction(function () use ($validated) {
                $now = now();
                $insertData = [];

                foreach ($validated['department_ids'] as $deptId) {
                    $insertData[] = [
                        'department_id' => $deptId,
                        'name'          => $validated['name'],
                        'level_number'  => $validated['level_number'],
                        'created_at'    => $now,
                    ];
                }

                // تنفيذ استعلام واحد INSERT INTO levels (...) VALUES (...), (...)
                Level::insert($insertData);

                // جلب السجلات المنشأة مع بيانات الأقسام لإعادتها للفرونت إند
                return Level::whereIn('department_id', $validated['department_ids'])
                    ->where('level_number', $validated['level_number'])
                    ->with('department')
                    ->get();
            });

            return response()->json([
                'status'  => true,
                'message' => 'تم إنشاء المستوى وربطه بجميع الأقسام المحددة بنجاح',
                'data'    => $createdLevels
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'فشلت عملية إنشاء المستويات المجمعة',
                'error'   => $e->getMessage()
            ], 500);
        }
    }



    // 1. تعديل بيانات المستوى (Update)
    public function update(Request $request, int $id)
    {
        $level = Level::find($id);

        if (!$level) {
            return response()->json([
                'status'  => false,
                'message' => 'المستوى الدراسي المطلوب غير موجود.'
            ], 404);
        }

        $validated = $request->validate([
            'department_id' => 'sometimes|required|exists:departments,department_id',
            'name'          => 'sometimes|required|string|max:100',
            'level_number'  => 'sometimes|required|integer|min:1|max:10',
        ], [
            'department_id.exists' => 'القسم المحدد غير موجود في النظام.',
            'name.required'        => 'اسم المستوى مطلوب.',
            'level_number.integer' => 'رقم المستوى يجب أن يكون عدداً صحيحاً.',
            'level_number.min'     => 'رقم المستوى لا يمكن أن يقل عن 1.',
        ]);

        $level->update($validated);

        return response()->json([
            'status'  => true,
            'message' => 'تم تحديث بيانات المستوى الدراسي بنجاح.',
            'data'    => $level
        ], 200);
    }

    // 2. حذف المستوى (Destroy)
    public function Delet(int $id)
    {
        $level = Level::find($id);

        if (!$level) {
            return response()->json([
                'status'  => false,
                'message' => 'المستوى الدراسي المطلوب غير موجود.'
            ], 404);
        }

        try {
            // حماية التكامل المرجعي: منع حذف المستوى إذا كان يتبعه فصول دراسية (Semesters)
            if (method_exists($level, 'semesters') && $level->semesters()->exists()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'لا يمكن حذف المستوى الدراسي لوجود فصول دراسية ومقررات مرتبطة به.'
                ], 409);
            }

            $level->delete();

            return response()->json([
                'status'  => true,
                'message' => 'تم حذف المستوى الدراسي بنجاح.'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'تعذر حذف المستوى نظراً لارتباطه ببيانات أكاديمية مسجلة.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
