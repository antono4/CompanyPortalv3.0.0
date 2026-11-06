<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Input;
use App\Core\Session;
use App\Core\Uploader;
use App\Models\ActivityLog;
use App\Models\Media;
use App\Models\User;
use RuntimeException;

final class ProfileController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $this->view('pages/profile', [
            'title' => 'Profil Saya - ' . \App\Config\App::NAME,
            'user'  => (new User())->findWithDepartment((int) Auth::id()),
        ]);
    }

    public function update(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $userId = (int) Auth::id();
        $model  = new User();
        $user   = $model->find($userId);
        if ($user === null) {
            $this->abort(404);
        }

        $errors = $this->validate([
            'fullname' => 'required|min:3|max:120',
            'email'    => 'required|email|max:150',
        ]);

        $email = Input::str('email');
        if ($model->exists('email', $email, $userId)) {
            $errors['email'] = 'Email sudah digunakan akun lain.';
        }

        if ($errors !== []) {
            Session::setOld($_POST);
            Session::flash('error', implode(' ', $errors));
            $this->redirect('/profile');
        }

        $model->update($userId, [
            'fullname' => Input::str('fullname'),
            'email'    => $email,
        ]);
        Auth::login($model->find($userId));

        (new ActivityLog())->log($userId, 'Memperbarui profil');
        Session::flash('success', 'Profil berhasil diperbarui.');
        $this->redirect('/profile');
    }

    /** Stream the requested user's profile photo to authenticated users. */
    public function avatar(string $id): void
    {
        $this->requireAuth();

        $user = (new User())->find((int) $id);
        if ($user === null || empty($user['photo_id'])) {
            $this->abort(404);
        }

        $media = (new Media())->findWithData((int) $user['photo_id']);
        if ($media === null) {
            $this->abort(404);
        }

        $this->sendBlob(
            (string) $media['data'],
            (string) $media['original_name'],
            (string) $media['mime_type'],
            true
        );
    }

    public function updatePhoto(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $userId = (int) Auth::id();
        $model  = new User();
        $user   = $model->find($userId);
        if ($user === null) {
            $this->abort(404);
        }

        try {
            $file = Uploader::readImage(Input::file('photo') ?? []);
        } catch (RuntimeException $e) {
            Session::flash('error', $e->getMessage());
            $this->redirect('/profile');
        }

        $stored = (new Media())->store($file, 'avatar', $userId);
        $oldId  = !empty($user['photo_id']) ? (int) $user['photo_id'] : null;

        $model->update($userId, ['photo_id' => $stored['id']]);
        if ($oldId !== null) {
            (new Media())->delete($oldId);
        }
        Auth::login($model->find($userId));

        (new ActivityLog())->log($userId, 'Memperbarui foto profil');
        Session::flash('success', 'Foto profil berhasil diperbarui.');
        $this->redirect('/profile');
    }

    public function removePhoto(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $userId = (int) Auth::id();
        $model  = new User();
        $user   = $model->find($userId);
        if ($user === null) {
            $this->abort(404);
        }

        if (!empty($user['photo_id'])) {
            $mediaId = (int) $user['photo_id'];
            $model->update($userId, ['photo_id' => null]);
            (new Media())->delete($mediaId);
            Auth::login($model->find($userId));
            (new ActivityLog())->log($userId, 'Menghapus foto profil');
            Session::flash('success', 'Foto profil berhasil dihapus.');
        }

        $this->redirect('/profile');
    }

    public function showPassword(): void
    {
        $this->requireAuth();

        $this->view('pages/change_password', [
            'title' => 'Ganti Password - ' . \App\Config\App::NAME,
        ]);
    }

    public function updatePassword(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $userId = (int) Auth::id();
        $model  = new User();
        $user   = $model->find($userId);
        if ($user === null) {
            $this->abort(404);
        }

        $current = (string) (Input::post('current_password') ?? '');
        $new     = (string) (Input::post('new_password') ?? '');
        $confirm = (string) (Input::post('confirm_password') ?? '');

        if ($current === '' || $new === '' || $confirm === '') {
            Session::flash('error', 'Semua kolom password wajib diisi.');
            $this->redirect('/change-password');
        }
        if (!password_verify($current, (string) $user['password'])) {
            Session::flash('error', 'Password saat ini salah.');
            $this->redirect('/change-password');
        }
        if (mb_strlen($new) < 8) {
            Session::flash('error', 'Password baru minimal 8 karakter.');
            $this->redirect('/change-password');
        }
        if ($new !== $confirm) {
            Session::flash('error', 'Konfirmasi password tidak cocok.');
            $this->redirect('/change-password');
        }
        if ($new === $current) {
            Session::flash('error', 'Password baru harus berbeda dari password lama.');
            $this->redirect('/change-password');
        }

        $model->update($userId, ['password' => password_hash($new, PASSWORD_DEFAULT)]);
        (new ActivityLog())->log($userId, 'Mengubah password');

        Session::flash('success', 'Password berhasil diubah.');
        $this->redirect('/profile');
    }
}
