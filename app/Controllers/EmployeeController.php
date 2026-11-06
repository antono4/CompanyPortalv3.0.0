<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Input;
use App\Core\Session;
use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Employee;

final class EmployeeController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $this->view('pages/employees', [
            'title'     => 'Karyawan - ' . \App\Config\App::NAME,
            'employees' => (new Employee())->withDepartment(),
        ]);
    }

    public function create(): void
    {
        $this->requireAdmin();

        $this->view('pages/employee_form', [
            'title'       => 'Tambah Karyawan - ' . \App\Config\App::NAME,
            'employee'    => null,
            'departments' => (new Department())->options(),
        ]);
    }

    public function store(): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();

        $errors = $this->validate([
            'fullname'     => 'required|min:3|max:120',
            'email'        => 'email|max:150',
            'employee_code' => 'max:40',
            'joining_date' => 'date',
            'status'       => 'required|in:active,disabled',
        ]);

        $model = new Employee();
        $code  = Input::str('employee_code');
        if ($code !== '' && $model->exists('employee_code', $code)) {
            $errors['employee_code'] = 'Kode karyawan sudah digunakan.';
        }

        if ($errors !== []) {
            Session::setOld($_POST);
            Session::flash('error', implode(' ', $errors));
            $this->redirect('/employees/create');
        }

        $id = $model->create($this->payload());

        (new ActivityLog())->log(Auth::id(), 'Menambahkan karyawan #' . $id);
        Session::flash('success', 'Karyawan berhasil ditambahkan.');
        $this->redirect('/employees');
    }

    public function edit(string $id): void
    {
        $this->requireAdmin();

        $employee = (new Employee())->findWithDepartment((int) $id);
        if ($employee === null) {
            $this->abort(404);
        }

        $this->view('pages/employee_form', [
            'title'       => 'Edit Karyawan - ' . \App\Config\App::NAME,
            'employee'    => $employee,
            'departments' => (new Department())->options(),
        ]);
    }

    public function update(string $id): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();

        $employeeId = (int) $id;
        $model      = new Employee();
        if ($model->find($employeeId) === null) {
            $this->abort(404);
        }

        $errors = $this->validate([
            'fullname'      => 'required|min:3|max:120',
            'email'         => 'email|max:150',
            'employee_code' => 'max:40',
            'joining_date'  => 'date',
            'status'        => 'required|in:active,disabled',
        ]);

        $code = Input::str('employee_code');
        if ($code !== '' && $model->exists('employee_code', $code, $employeeId)) {
            $errors['employee_code'] = 'Kode karyawan sudah digunakan.';
        }

        if ($errors !== []) {
            Session::setOld($_POST);
            Session::flash('error', implode(' ', $errors));
            $this->redirect('/employees/' . $employeeId . '/edit');
        }

        $model->update($employeeId, $this->payload());

        (new ActivityLog())->log(Auth::id(), 'Memperbarui karyawan #' . $employeeId);
        Session::flash('success', 'Karyawan berhasil diperbarui.');
        $this->redirect('/employees');
    }

    public function destroy(string $id): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();

        $employeeId = (int) $id;
        $model      = new Employee();
        if ($model->find($employeeId) === null) {
            $this->abort(404);
        }

        $model->delete($employeeId);
        (new ActivityLog())->log(Auth::id(), 'Menghapus karyawan #' . $employeeId);
        Session::flash('success', 'Karyawan berhasil dihapus.');
        $this->redirect('/employees');
    }

    private function payload(): array
    {
        return [
            'employee_code' => Input::str('employee_code') ?: null,
            'fullname'      => Input::str('fullname'),
            'email'         => Input::str('email') ?: null,
            'phone'         => Input::str('phone') ?: null,
            'designation'   => Input::str('designation') ?: null,
            'department_id' => Input::int('department_id') ?: null,
            'joining_date'  => Input::str('joining_date') ?: null,
            'address'       => Input::str('address') ?: null,
            'status'        => Input::str('status', 'active'),
        ];
    }
}
