<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Input;
use App\Core\Session;
use App\Models\ActivityLog;
use App\Models\Setting;

final class SettingController extends Controller
{
    private const FIELDS = [
        'company_name', 'company_email', 'company_phone',
        'company_website', 'company_address', 'timezone',
    ];

    public function index(): void
    {
        $this->requireAdmin();

        $settings = array_merge(Setting::defaults(), (new Setting())->allKeyed());

        $this->view('pages/settings', [
            'title'    => 'Pengaturan - ' . \App\Config\App::NAME,
            'settings' => $settings,
        ]);
    }

    public function update(): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();

        $errors = $this->validate([
            'company_name'  => 'required|min:2|max:120',
            'company_email' => 'email|max:150',
            'company_phone' => 'max:40',
            'company_website' => 'max:180',
            'timezone'      => 'required|in:Asia/Jakarta,Asia/Makassar,Asia/Jayapura,UTC',
        ]);

        if ($errors !== []) {
            Session::setOld($_POST);
            Session::flash('error', implode(' ', $errors));
            $this->redirect('/settings');
        }

        $model = new Setting();
        foreach (self::FIELDS as $field) {
            $model->set($field, Input::str($field));
        }

        (new ActivityLog())->log(Auth::id(), 'Memperbarui pengaturan perusahaan');
        Session::flash('success', 'Pengaturan berhasil disimpan.');
        $this->redirect('/settings');
    }
}
