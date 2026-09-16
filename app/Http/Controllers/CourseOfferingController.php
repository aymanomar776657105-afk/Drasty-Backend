<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CourseOffering;
use Illuminate\Validation\Rule;
use Exception;

class CourseOfferingController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id'   => 'required|exists:courses,course_id',
            'semester_id' => [
                'required',
                'exists:semesters,semester_id',
                // منع تكرار طرح نفس المادة في نفس الفصل الدراسي
                Rule::unique('course_offerings')->where(function ($query) use ($request) {
                    return $query->where('course_id', $request->course_id);
                }),
            ],
        ], [
            'semester_id.unique' => 'هذه المادة مضافة بالفعل لهذا الفصل الدراسي.',
        ]);

        try {
            $offering = CourseOffering::create($validated);

            return response()->json([
                'status'  => true,
                'message' => 'تم طرح المادة في الفصل الدراسي بنجاح',
                'data'    => $offering->load('course')
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'فشلت عملية إضافة المادة للفصل',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}