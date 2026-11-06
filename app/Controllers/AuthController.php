<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Input;
use App\Core\Session;
use App\Models\ActivityLog;
use App\Models\User;

final class AuthController extends Controller
{
    public function home(): void
    {
        $this->redirect(Auth::check() ? '/dashboard' : '/login');
    }

    public function showLogin(): void
    {
        if (Auth::check()) {
            $this->redirect('/dashboard');
        }

        $error = Session::pullFlash('error');

        $this->view('auth/login', [
            'title' => 'Masuk - ' . \App\Config\App::NAME,
            'error' => $error,
        ]);
    }

    public function login(): void
    {
        if (!Csrf::verify(Input::post('csrf_token'))) {
            Session::flash('error', 'Sesi formulir kedaluwarsa. Silakan coba lagi.');
            $this->redirect('/login');
        }

        $username = Input::str('username');
        $password = (string) (Input::post('password') ?? '');

        if ($username === '' || $password === '') {
            Session::flash('error', 'Username dan password wajib diisi.');
            $this->redirect('/login');
        }

        $user = Auth::attempt($username, $password);

        if ($user === null) {
            Session::flash('error', 'Username atau password salah, atau akun tidak aktif.');
            $this->redirect('/login');
        }

        if (password_needs_rehash((string) $user['password'], PASSWORD_DEFAULT)) {
            (new User())->update((int) $user['id'], [
                'password' => password_hash($password, PASSWORD_DEFAULT),
            ]);
        }

        Auth::login($user);
        (new User())->updateLastLogin((int) $user['id']);
        (new ActivityLog())->log((int) $user['id'], 'Login ke sistem');

        $target = (string) Session::get('redirect_after_login', '/dashboard');
        Session::forget('redirect_after_login');

        if (!str_starts_with($target, '/') || str_starts_with($target, '//')) {
            $target = '/dashboard';
        }

        $this->redirect($target);
    }

    public function logout(): void
    {
        if (Auth::check()) {
            (new ActivityLog())->log(Auth::id(), 'Logout dari sistem');
        }
        Auth::logout();
        $this->redirect('/login');
    }
}
