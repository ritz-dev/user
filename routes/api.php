<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\APIs\StudentController;
use App\Http\Controllers\APIs\TeacherController;
use App\Http\Controllers\APIs\EmployeeController;
use App\Http\Controllers\APIs\PersonalController;

Route::prefix('personals')->group(function(){
    Route::post('list',[PersonalController::class,'list']);
});

Route::prefix('students')->group(function(){
    Route::post('list',[StudentController::class,'list']);
    // Route::post('create',[StudentController::class,'create']);
    // Route::post('detail',[StudentController::class,'detail']);
    // Route::put('update',[StudentController::class,'update']);
    // Route::post('delete',[StudentController::class,'delete']);
    // Route::post('by-section',[StudentController::class,'bySection']);
});

Route::prefix('employees')->group(function(){
    Route::post('list',[EmployeeController::class,'list']);
    Route::post('create',[EmployeeController::class,'create']);
    Route::post('detail',[EmployeeController::class,'detail']);
    Route::put('update',[EmployeeController::class,'update']);
    Route::post('delete',[EmployeeController::class,'delete']);
});

Route::prefix('teachers')->group(function(){
    Route::post('list',[TeacherController::class,'list']);
    Route::post('create',[TeacherController::class,'create']);
    Route::post('detail',[TeacherController::class,'detail']);
    Route::put('update',[TeacherController::class,'update']);
    Route::post('delete',[TeacherController::class,'delete']);
});