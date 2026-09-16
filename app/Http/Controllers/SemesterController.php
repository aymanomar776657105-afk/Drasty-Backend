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
}