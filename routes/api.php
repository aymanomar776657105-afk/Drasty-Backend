<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\CourseController;

use App\Http\Controllers\CourseOfferingController;
use App\Http\Controllers\CourseDoctorController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get("/hello",function(){
    return "Hi from API";
});

Route::post('/check-user', [UserController::class, 'checkUser']);

// مسار مباشر لإضافة الأدمن تجريبياً  
Route::post('/add-admin', [UserController::class, 'addAdmin']);

//نفسه بس لل contexrmangment
Route::post('/add-contextMangment', [UserController::class, 'addContextMangmet']);


// مسارات إدارة الأقسام
// هذا ال API  يعرض الاقسام
Route::get('/departments', [DepartmentController::class, 'index']);

// هذا على اساس يضيف اقسام في ال DB
// Route::post('/departments', [DepartmentController::class, 'store']);

// اضافة الدكتور لبين ال DB
Route::post('/doctors', [DoctorController::class, 'store']);

//  اضافة مادة
Route::put('/courses', [CourseController::class, 'store']);


// مسارات ربط الفصول والدكاترة
//  يربط الكورس و السنه والترم
Route::post('/course-offerings', [CourseOfferingController::class, 'store']);
//  تعيين دكتور لتدريس مادة 
Route::post('/course-doctors', [CourseDoctorController::class, 'store']);


use App\Http\Controllers\LevelController;
use App\Http\Controllers\SemesterController;

// مسارات المستويات والفصول الدراسية
Route::get('/levels', [LevelController::class, 'index']);
Route::post('/levels', [LevelController::class, 'store']);
Route::post('/semesters', [SemesterController::class, 'store']);