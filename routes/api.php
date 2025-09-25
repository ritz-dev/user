<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\APIs\StudentController;
use App\Http\Controllers\APIs\TeacherController;
use App\Http\Controllers\APIs\EmployeeController;
use App\Http\Controllers\APIs\PersonalController;

// Route::prefix('personals')->group(function(){
//     Route::post('/',[PersonalController::class,'index']);
// });

Route::prefix('students')->group(function(){
    Route::post('/',[StudentController::class,'index']);
    Route::post('store',[StudentController::class,'store']);
    Route::post('show',[StudentController::class,'show']);
    Route::post('update',[StudentController::class,'update']);
    Route::post('action',[StudentController::class,'handleAction']);
    // Route::post('enrollment',[StudentController::class,'enrollment']);
});

Route::prefix('teachers')->group(function(){
    Route::post('/',[TeacherController::class,'index']);
    Route::post('store',[TeacherController::class,'store']);
    Route::post('show',[TeacherController::class,'show']);
    Route::post('update',[TeacherController::class,'update']);
    Route::post('action',[TeacherController::class,'handleAction']);
});

Route::prefix('employees')->group(function(){
    Route::post('/',[EmployeeController::class,'index']);
    Route::post('store',[EmployeeController::class,'store']);
    Route::post('show',[EmployeeController::class,'show']);
    Route::post('update',[EmployeeController::class,'update']);
    Route::post('action',[EmployeeController::class,'handleAction']);
});