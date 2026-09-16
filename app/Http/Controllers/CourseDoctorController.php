<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CourseDoctor;
use Illuminate\Validation\Rule;
use Exception;

class CourseDoctorController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'offering_id' => 'required|exists:course_offerings,offering_id',
            'doctor_id'   => [
                'required',
                'exists:doctors,doctor_id',
                // منع تعيين نفس الدكتور أكثر من مرة لنفس المادة في نفس الفصل
                Rule::unique('course_doctors')->where(function ($query) use ($request) {
                    return $query->where('offering_id', $request->offering_id);
                }),
            ],
        ], [
            'doctor_id.unique' => 'هذا الدكتور مسند بالفعل لهذه المادة في هذا الفصل.',
        ]);

        try {
            $courseDoctor = CourseDoctor::create($validated);

            return response()->json([
                'status'  => true,
                'message' => 'تم تعيين الدكتور للمادة بنجاح',
                'data'    => $courseDoctor->load(['doctor.user', 'offering.course'])
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'فشلت عملية تعيين الدكتور للمادة',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}