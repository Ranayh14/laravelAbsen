<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\FaceNetController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\HelpController;
use App\Http\Controllers\Api\AttendanceNoteController;
use App\Http\Controllers\Api\EmployeeWorkScheduleController;
use App\Http\Controllers\Api\ManualHolidayController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware(['legacy.auth', 'require.auth'])->group(function () {
    // Auth & User Profile
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Attendance API
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn']);
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut']);
    Route::get('/attendance/today', [AttendanceController::class, 'today']);
    Route::apiResource('attendance', AttendanceController::class)->except(['create', 'edit']);

    // FaceNet API
    Route::post('/face/verify', [FaceNetController::class, 'verify']);
    Route::post('/face/register', [FaceNetController::class, 'registerFace']);
    Route::get('/face', [FaceNetController::class, 'index']);
    Route::get('/face/{id}', [FaceNetController::class, 'show']);
    Route::delete('/face/{id}', [FaceNetController::class, 'destroy']);

    // User CRUD API
    Route::get('/users/{id}/photo', [UserController::class, 'getPhoto']);
    Route::apiResource('users', UserController::class)->except(['create', 'edit']);

    // Reports API - Daily
    Route::get('/reports/daily', [ReportController::class, 'indexDaily']);
    Route::post('/reports/daily', [ReportController::class, 'storeDaily']);
    Route::get('/reports/daily/{id}', [ReportController::class, 'showDaily']);
    Route::put('/reports/daily/{id}', [ReportController::class, 'updateDaily']);
    Route::delete('/reports/daily/{id}', [ReportController::class, 'destroyDaily']);
    Route::put('/reports/daily/{id}/status', [ReportController::class, 'updateDailyStatus']); 

    // Reports API - Monthly
    Route::get('/reports/monthly', [ReportController::class, 'indexMonthly']);
    Route::post('/reports/monthly', [ReportController::class, 'storeMonthly']);
    Route::get('/reports/monthly/{id}', [ReportController::class, 'showMonthly']);
    Route::put('/reports/monthly/{id}', [ReportController::class, 'updateMonthly']);
    Route::delete('/reports/monthly/{id}', [ReportController::class, 'destroyMonthly']);
    Route::put('/reports/monthly/{id}/status', [ReportController::class, 'updateMonthlyStatus']);

    // Settings API
    Route::get('/settings', [SettingController::class, 'index']);
    Route::post('/settings', [SettingController::class, 'store']);
    Route::get('/settings/{key}', [SettingController::class, 'show']);
    Route::put('/settings', [SettingController::class, 'update']); 
    Route::delete('/settings/{key}', [SettingController::class, 'destroy']);

    // Admin Help Requests API
    Route::put('/help/{id}/status', [HelpController::class, 'updateStatus']); 
    Route::apiResource('help', HelpController::class)->except(['create', 'edit']);

    // Attendance Notes API
    Route::apiResource('attendance-notes', AttendanceNoteController::class)->except(['create', 'edit']);

    // Employee Work Schedule API
    Route::apiResource('employee-work-schedules', EmployeeWorkScheduleController::class)->except(['create', 'edit']);

    // Manual Holidays API
    Route::apiResource('manual-holidays', ManualHolidayController::class)->except(['create', 'edit']);
});