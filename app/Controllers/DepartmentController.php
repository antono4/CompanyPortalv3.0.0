<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Input;
use App\Core\Session;
use App\Models\ActivityLog;
use App\Models\Department;

final class DepartmentController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $this->view('pages/departments', [
            'title'       => 'Departemen - ' . \App\Config\App::NAME,
            'departments' => (new Department())->withCounts(),
        ]);
    }

    public function create(): void
    {
        $this->requireAdmin();

        $this->view('pages/department_form', [
            'title'      => 'Tambah Departemen - ' . \App\Config\App::NAME,
            'department' => null,
        ]);
    }

    public function store(): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();

        $errors = $this->validate([
            'department_name' => 'required|min:2|max:100',
            'status'          => 'required|in:active,disabled',
        ]);

        $model = new Department();
        if ($model->exists('department_name', Input::str('department_name'))) {
            $errors['department_name'] = 'Nama departemen sudah ada.';
        }

        if ($errors !== []) {
            Session::setOld($_POST);
            Session::flash('error', implode(' ', $errors));
            $this->redirect('/departments/create');
        }

        $id = $model->create([
            'department_name' => Input::str('department_name'),
            'description'     => Input::str('description'),
            'status'          => Input::str('status', 'active'),
        ]);

        (new ActivityLog())->log(Auth::id(), 'Menambahkan departemen #' . $id);
        Session::flash('success', 'Departemen berhasil ditambahkan.');
        $this->redirect('/departments');
    }

    public function edit(string $id): void
    {
        $this->requireAdmin();

        $department = (new Department())->find((int) $id);
        if ($department === null) {
            $this->abort(404);
        }

        $this->view('pages/department_form', [
            'title'      => 'Edit Departemen - ' . \App\Config\App::NAME,
            'department' => $department,
        ]);
    }

    public function update(string $id): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();

        $departmentId = (int) $id;
        $model        = new Department();
        if ($model->find($departmentId) === null) {
            $this->abort(404);
        }

        $errors = $this->validate([
            'department_name' => 'required|min:2|max:100',
            'status'          => 'required|in:active,disabled',
        ]);

        if ($model->exists('department_name', Input::str('department_name'), $departmentId)) {
            $errors['department_name'] = 'Nama departemen sudah ada.';
        }

        if ($errors !== []) {
            Session::setOld($_POST);
            Session::flash('error', implode(' ', $errors));
            $this->redirect('/departments/' . $departmentId . '/edit');
        }

        $model->update($departmentId, [
            'department_name' => Input::str('department_name'),
            'description'     => Input::str('description'),
            'status'          => Input::str('status', 'active'),
        ]);

        (new ActivityLog())->log(Auth::id(), 'Memperbarui departemen #' . $departmentId);
        Session::flash('success', 'Departemen berhasil diperbarui.');
        $this->redirect('/departments');
    }

    public function destroy(string $id): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();

        $departmentId = (int) $id;
        $model        = new Department();
        $department   = $model->find($departmentId);
        if ($department === null) {
            $this->abort(404);
        }

        $employees = new \App\Models\Employee();
        if ($employees->count('department_id = ?', [$departmentId]) > 0) {
            Session::flash('error', 'Departemen masih memiliki karyawan. Pindahkan karyawan terlebih dahulu.');
            $this->redirect('/departments');
        }

        $model->delete($departmentId);
        (new ActivityLog())->log(Auth::id(), 'Menghapus departemen #' . $departmentId);
        Session::flash('success', 'Departemen berhasil dihapus.');
        $this->redirect('/departments');
    }
}
