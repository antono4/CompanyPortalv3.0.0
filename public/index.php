<?php
declare(strict_types=1);

/**
 * Company Portal - front controller.
 */

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Config\App;
use App\Controllers\ActivityLogController;
use App\Controllers\AnnouncementController;
use App\Controllers\AttendanceController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\DepartmentController;
use App\Controllers\DocumentController;
use App\Controllers\EmployeeController;
use App\Controllers\ProfileController;
use App\Controllers\SettingController;
use App\Controllers\UploadController;
use App\Controllers\UserController;
use App\Core\Router;

$router = new Router();

/* ---------------------------------------------------------------
 | Public / authentication
 * --------------------------------------------------------------- */
$router->get('/', [AuthController::class, 'home']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);

/* ---------------------------------------------------------------
 | Dashboard
 * --------------------------------------------------------------- */
$router->get('/dashboard', [DashboardController::class, 'index']);

/* ---------------------------------------------------------------
 | Users (admin only)
 * --------------------------------------------------------------- */
$router->get('/users', [UserController::class, 'index']);
$router->get('/users/create', [UserController::class, 'create']);
$router->post('/users', [UserController::class, 'store']);
$router->get('/users/{id}/edit', [UserController::class, 'edit']);
$router->post('/users/{id}', [UserController::class, 'update']);
$router->post('/users/{id}/delete', [UserController::class, 'destroy']);

/* ---------------------------------------------------------------
 | Departments
 * --------------------------------------------------------------- */
$router->get('/departments', [DepartmentController::class, 'index']);
$router->get('/departments/create', [DepartmentController::class, 'create']);
$router->post('/departments', [DepartmentController::class, 'store']);
$router->get('/departments/{id}/edit', [DepartmentController::class, 'edit']);
$router->post('/departments/{id}', [DepartmentController::class, 'update']);
$router->post('/departments/{id}/delete', [DepartmentController::class, 'destroy']);

/* ---------------------------------------------------------------
 | Employees
 * --------------------------------------------------------------- */
$router->get('/employees', [EmployeeController::class, 'index']);
$router->get('/employees/create', [EmployeeController::class, 'create']);
$router->post('/employees', [EmployeeController::class, 'store']);
$router->get('/employees/{id}/edit', [EmployeeController::class, 'edit']);
$router->post('/employees/{id}', [EmployeeController::class, 'update']);
$router->post('/employees/{id}/delete', [EmployeeController::class, 'destroy']);

/* ---------------------------------------------------------------
 | Attendance (realtime schedule + check-in/out)
 * --------------------------------------------------------------- */
$router->get('/attendance', [AttendanceController::class, 'index']);
$router->get('/attendance/status', [AttendanceController::class, 'status']);
$router->post('/attendance/check-in', [AttendanceController::class, 'checkIn']);
$router->post('/attendance/check-out', [AttendanceController::class, 'checkOut']);
$router->get('/attendance/schedules', [AttendanceController::class, 'schedules']);
$router->get('/attendance/schedules/create', [AttendanceController::class, 'scheduleCreate']);
$router->post('/attendance/schedules', [AttendanceController::class, 'scheduleStore']);
$router->get('/attendance/schedules/{id}/edit', [AttendanceController::class, 'scheduleEdit']);
$router->post('/attendance/schedules/{id}', [AttendanceController::class, 'scheduleUpdate']);
$router->post('/attendance/schedules/{id}/delete', [AttendanceController::class, 'scheduleDestroy']);

/* ---------------------------------------------------------------
 | Announcements
 * --------------------------------------------------------------- */$router->get('/announcements', [AnnouncementController::class, 'index']);
$router->get('/announcements/create', [AnnouncementController::class, 'create']);
$router->get('/announcements/{id}', [AnnouncementController::class, 'show']);
$router->get('/announcements/{id}/image', [AnnouncementController::class, 'image']);
$router->post('/announcements', [AnnouncementController::class, 'store']);
$router->get('/announcements/{id}/edit', [AnnouncementController::class, 'edit']);
$router->post('/announcements/{id}', [AnnouncementController::class, 'update']);
$router->post('/announcements/{id}/delete', [AnnouncementController::class, 'destroy']);

/* ---------------------------------------------------------------
 | Documents
 * --------------------------------------------------------------- */
$router->get('/documents', [DocumentController::class, 'index']);
$router->get('/documents/create', [DocumentController::class, 'create']);
$router->post('/documents', [DocumentController::class, 'store']);
$router->get('/documents/{id}/download', [DocumentController::class, 'download']);
$router->post('/documents/{id}/delete', [DocumentController::class, 'destroy']);

/* ---------------------------------------------------------------
 | Upload center
 * --------------------------------------------------------------- */
$router->get('/uploads', [UploadController::class, 'index']);
$router->get('/uploads/create', [UploadController::class, 'create']);
$router->post('/uploads', [UploadController::class, 'store']);
$router->get('/uploads/{id}/download', [UploadController::class, 'download']);
$router->post('/uploads/{id}/delete', [UploadController::class, 'destroy']);

/* ---------------------------------------------------------------
 | Settings (admin only)
 * --------------------------------------------------------------- */
$router->get('/settings', [SettingController::class, 'index']);
$router->post('/settings', [SettingController::class, 'update']);

/* ---------------------------------------------------------------
 | Activity logs (admin only)
 * --------------------------------------------------------------- */
$router->get('/activity-logs', [ActivityLogController::class, 'index']);
$router->post('/activity-logs/clear', [ActivityLogController::class, 'clear']);

/* ---------------------------------------------------------------
 | Profile
 * --------------------------------------------------------------- */
$router->get('/profile', [ProfileController::class, 'index']);
$router->post('/profile', [ProfileController::class, 'update']);
$router->post('/profile/photo', [ProfileController::class, 'updatePhoto']);
$router->post('/profile/photo/delete', [ProfileController::class, 'removePhoto']);
$router->get('/avatar/{id}', [ProfileController::class, 'avatar']);
$router->get('/change-password', [ProfileController::class, 'showPassword']);
$router->post('/change-password', [ProfileController::class, 'updatePassword']);

try {
    $router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', App::path());
} catch (\Throwable $e) {
    error_log((string) $e);
    http_response_code(500);
    $message = $e->getMessage();
    require APP_VIEW_PATH . '/errors/500.php';
}
