<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Level;
use Illuminate\Support\Facades\DB;
use Exception;

class LevelController extends Controller
{
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
}