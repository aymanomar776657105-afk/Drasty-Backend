<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\LevelController;

use App\Http\Controllers\CourseOfferingController;
use App\Http\Controllers\CourseDoctorController;

use App\Http\Controllers\ContentTypeController;
use App\Http\Controllers\SemesterController;



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


// مسارات ربط الفصول والدكاترة
//  يربط الكورس و السنه والترم
//  تعيين دكتور لتدريس مادة 
Route::post('/course-doctors', [CourseDoctorController::class, 'store']);




// مسارات المستويات والفصول الدراسية
Route::get('/levels', [LevelController::class, 'index']);





// مسارات العرض والتهيئة للواجهات

// هنا هلهن APIs  العرض
Route::get('/doctors', [DoctorController::class, 'index']);
Route::get('/content-types', [ContentTypeController::class, 'index']);
Route::get('/course-doctors', [CourseDoctorController::class, 'index']);


// هذه  APIs الاضافه والحذف والتعديل للاقسام
Route::post('/departments', [DepartmentController::class, 'store']);
Route::put('/departments/{id}', [DepartmentController::class, 'update']);
Route::delete('/departments/{id}', [DepartmentController::class, 'destroy']);

// هذا حذف وتعديل واضافه للمستوى    Level
Route::post('/levels', [LevelController::class, 'store']);
Route::put('/levels/{id}', [LevelController::class, 'update']);
Route::delete('/levels/{id}', [LevelController::class, 'Delet']);
Route::get('/levels', [LevelController::class, 'index']);


// مسارات الفصول الدراسية (Semesters)
Route::get('/semesters', [SemesterController::class, 'index']);
Route::post('/semesters', [SemesterController::class, 'store']);
Route::put('/semesters/{id}', [SemesterController::class, 'update']);
Route::delete('/semesters/{id}', [SemesterController::class, 'destroy']);



// هنا ياهيثم  API  حق الواد 
Route::get('/courses', [CourseController::class, 'index']);
Route::post('/courses', [CourseController::class, 'store']);
Route::put('/courses/{id}', [CourseController::class, 'update']);
Route::delete('/courses/{id}', [CourseController::class, 'destroy']);



// وهذا حق الكورسات المعروضه
Route::post('/course-offerings', [CourseOfferingController::class, 'store']);
Route::get('/course-offerings', [CourseOfferingController::class, 'index']);
Route::put('/course-offerings/{id}', [CourseOfferingController::class, 'update']);
Route::delete('/course-offerings/{id}', [CourseOfferingController::class, 'destroy']);