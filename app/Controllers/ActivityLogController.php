<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Models\ActivityLog;

final class ActivityLogController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $per  = 25;

        $total = (new ActivityLog())->count();
        $logs  = Database::query(
            'SELECT l.*, u.fullname AS user_name
               FROM activity_logs l
               LEFT JOIN users u ON u.id = l.user_id
              ORDER BY l.created_at DESC
              LIMIT ' . $per . ' OFFSET ' . (($page - 1) * $per)
        )->fetchAll();

        $this->view('pages/activity_logs', [
            'title'  => 'Log Aktivitas - ' . \App\Config\App::NAME,
            'logs'   => $logs,
            'page'   => $page,
            'pages'  => max(1, (int) ceil($total / $per)),
            'total'  => $total,
        ]);
    }

    public function clear(): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();

        Database::query('DELETE FROM activity_logs');
        (new ActivityLog())->log(Auth::id(), 'Membersihkan seluruh log aktivitas');

        Session::flash('success', 'Log aktivitas berhasil dibersihkan.');
        $this->redirect('/activity-logs');
    }
}
