<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\APIs\StudentController;
use App\Http\Controllers\APIs\TeacherController;
use App\Http\Controllers\APIs\EmployeeController;
use App\Http\Controllers\APIs\PersonalController;

Route::prefix('personals')->group(function(){
    Route::post('/',[PersonalController::class,'index']);
});

Route::prefix('students')->group(function(){
    Route::post('/',[StudentController::class,'index']);
    Route::post('store',[StudentController::class,'store']);
    Route::post('show',[StudentController::class,'show']);
    Route::post('update',[StudentController::class,'update']);
    Route::post('action',[StudentController::class,'handleAction']);
    // Route::post('detail',[StudentController::class,'detail']);
    // Route::put('update',[StudentController::class,'update']);
    // Route::post('delete',[StudentController::class,'delete']);
    // Route::post('by-section',[StudentController::class,'bySection']);
});

Route::prefix('teachers')->group(function(){
    Route::post('/',[TeacherController::class,'index']);
    Route::post('create',[TeacherController::class,'create']);
    Route::post('detail',[TeacherController::class,'detail']);
    Route::put('update',[TeacherController::class,'update']);
    Route::post('delete',[TeacherController::class,'delete']);
});

Route::prefix('employees')->group(function(){
    Route::post('list',[EmployeeController::class,'list']);
    Route::post('create',[EmployeeController::class,'create']);
    Route::post('detail',[EmployeeController::class,'detail']);
    Route::put('update',[EmployeeController::class,'update']);
    Route::post('delete',[EmployeeController::class,'delete']);
});
