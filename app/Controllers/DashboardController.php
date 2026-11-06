<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\ActivityLog;
use App\Models\Announcement;
use App\Models\Department;
use App\Models\Document;
use App\Models\Employee;
use App\Models\Upload;
use App\Models\User;

final class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $employees   = new Employee();
        $documents   = new Document();
        $uploads     = new Upload();
        $departments = new Department();

        $totalEmployees    = $employees->count();
        $activeEmployees   = $employees->countByStatus('active');
        $inactiveEmployees = $employees->countByStatus('disabled');

        $this->view('pages/dashboard', [
            'title'              => 'Dashboard - ' . \App\Config\App::NAME,
            'totalEmployees'     => $totalEmployees,
            'activeEmployees'    => $activeEmployees,
            'inactiveEmployees'  => $inactiveEmployees,
            'totalDepartments'   => $departments->count(),
            'totalDocuments'     => $documents->count(),
            'totalUploads'       => $uploads->count(),
            'totalUsers'         => (new User())->count(),
            'totalAnnouncements' => (new Announcement())->count(),
            'storageUsed'        => $documents->totalSize() + $uploads->totalSize(),
            'recentActivities'   => (new ActivityLog())->recent(6),
            'latestAnnouncements' => (new Announcement())->latest(5),
            'recentDocuments'    => array_slice($documents->withUploader(), 0, 5),
        ]);
    }
}
