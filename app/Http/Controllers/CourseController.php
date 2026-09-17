<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use Exception;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::orderBy('course_name_ar', 'asc')->get();

        return response()->json([
            'status' => true,
            'count'  => $courses->count(),
            'data'   => $courses
        ], 200);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_code'    => 'nullable|string|max:50|unique:courses,course_code',
            'course_name_ar' => 'required|string|max:150',
            'course_name_en' => 'required|string|max:150',
            'credit_hours'   => 'nullable|integer|min:1|max:10',
        ], [
            'course_code.unique'       => 'رمز المادة مسجل مسبقاً',
            'course_code.required'     => 'يرجى إدخال رمز المادة',
            'course_name_ar.required'  => 'اسم المادة بالعربي مطلوب',
            'course_name_en.required'  => 'اسم المادة بالإنجليزي مطلوب',
            'credit_hours.required'    => 'عدد الساعات مطلوب',
        ]);

        try {
            $course = Course::create($validated);

            return response()->json([
                'status'  => true,
                'message' => 'تمت إضافة المادة بنجاح',
                'data'    => $course
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'فشل في إضافة المادة',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

}