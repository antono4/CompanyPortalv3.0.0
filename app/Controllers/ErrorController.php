<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;

final class ErrorController extends Controller
{
    public function notFound(): void
    {
        http_response_code(404);
        $this->view('errors/404', [
            'title' => 'Halaman Tidak Ditemukan',
            'user'  => Session::get('user'),
        ]);
    }
}
