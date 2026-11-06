<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Input;
use App\Core\Session;
use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Media;
use App\Models\User;

final class UserController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();

        $this->view('pages/users', [
            'title' => 'Manajemen User - ' . \App\Config\App::NAME,
            'users' => (new User())->withDepartment(),
        ]);
    }

    public function create(): void
    {
        $this->requireAdmin();

        $this->view('pages/user_form', [
            'title'       => 'Tambah User - ' . \App\Config\App::NAME,
            'user'        => null,
            'departments' => (new Department())->options(),
        ]);
    }

    public function store(): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();

        $errors = $this->validate([
            'fullname' => 'required|min:3|max:120',
            'username' => 'required|min:3|max:60',
            'email'    => 'required|email|max:150',
            'password' => 'required|min:8|max:72',
            'role'     => 'required|in:admin,user',
            'status'   => 'required|in:active,disabled',
        ]);

        $model    = new User();
        $username = Input::str('username');
        $email    = Input::str('email');

        if ($model->exists('username', $username)) {
            $errors['username'] = 'Username sudah digunakan.';
        }
        if ($model->exists('email', $email)) {
            $errors['email'] = 'Email sudah digunakan.';
        }

        if ($errors !== []) {
            Session::setOld($_POST);
            Session::flash('error', implode(' ', $errors));
            $this->redirect('/users/create');
        }

        $id = $model->create([
            'fullname'      => Input::str('fullname'),
            'username'      => $username,
            'email'         => $email,
            'password'      => password_hash((string) Input::post('password'), PASSWORD_DEFAULT),
            'role'          => Input::str('role', 'user'),
            'department_id' => Input::int('department_id') ?: null,
            'status'        => Input::str('status', 'active'),
        ]);

        (new ActivityLog())->log(Auth::id(), 'Menambahkan user #' . $id . ' (' . $username . ')');
        Session::flash('success', 'User berhasil ditambahkan.');
        $this->redirect('/users');
    }

    public function edit(string $id): void
    {
        $this->requireAdmin();

        $user = (new User())->findWithDepartment((int) $id);
        if ($user === null) {
            $this->abort(404);
        }

        $this->view('pages/user_form', [
            'title'       => 'Edit User - ' . \App\Config\App::NAME,
            'user'        => $user,
            'departments' => (new Department())->options(),
        ]);
    }

    public function update(string $id): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();

        $userId = (int) $id;
        $model  = new User();
        $user   = $model->find($userId);
        if ($user === null) {
            $this->abort(404);
        }

        $errors = $this->validate([
            'fullname' => 'required|min:3|max:120',
            'username' => 'required|min:3|max:60',
            'email'    => 'required|email|max:150',
            'role'     => 'required|in:admin,user',
            'status'   => 'required|in:active,disabled',
            'password' => 'max:72',
        ]);

        $username = Input::str('username');
        $email    = Input::str('email');

        if ($model->exists('username', $username, $userId)) {
            $errors['username'] = 'Username sudah digunakan.';
        }
        if ($model->exists('email', $email, $userId)) {
            $errors['email'] = 'Email sudah digunakan.';
        }

        if ($userId === Auth::id() && Input::str('status') !== 'active') {
            $errors['status'] = 'Anda tidak dapat menonaktifkan akun sendiri.';
        }
        if ($userId === Auth::id() && Input::str('role') !== 'admin') {
            $errors['role'] = 'Anda tidak dapat menurunkan role akun sendiri.';
        }

        if ($errors !== []) {
            Session::setOld($_POST);
            Session::flash('error', implode(' ', $errors));
            $this->redirect('/users/' . $userId . '/edit');
        }

        $data = [
            'fullname'      => Input::str('fullname'),
            'username'      => $username,
            'email'         => $email,
            'role'          => Input::str('role', 'user'),
            'department_id' => Input::int('department_id') ?: null,
            'status'        => Input::str('status', 'active'),
        ];

        $newPassword = (string) (Input::post('password') ?? '');
        if ($newPassword !== '') {
            if (mb_strlen($newPassword) < 8) {
                Session::setOld($_POST);
                Session::flash('error', 'Password baru minimal 8 karakter.');
                $this->redirect('/users/' . $userId . '/edit');
            }
            $data['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        $model->update($userId, $data);

        if ($userId === Auth::id()) {
            Auth::login($model->find($userId));
        }

        (new ActivityLog())->log(Auth::id(), 'Memperbarui user #' . $userId . ' (' . $username . ')');
        Session::flash('success', 'User berhasil diperbarui.');
        $this->redirect('/users');
    }

    public function destroy(string $id): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();

        $userId = (int) $id;
        if ($userId === Auth::id()) {
            Session::flash('error', 'Anda tidak dapat menghapus akun sendiri.');
            $this->redirect('/users');
        }

        $model = new User();
        $user  = $model->find($userId);
        if ($user === null) {
            $this->abort(404);
        }

        if (!empty($user['photo_id'])) {
            (new Media())->delete((int) $user['photo_id']);
        }
        $model->delete($userId);
        (new ActivityLog())->log(Auth::id(), 'Menghapus user #' . $userId . ' (' . $user['username'] . ')');
        Session::flash('success', 'User berhasil dihapus.');
        $this->redirect('/users');
    }
}
