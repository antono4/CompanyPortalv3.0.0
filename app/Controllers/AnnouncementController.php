<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Input;
use App\Core\Session;
use App\Core\Uploader;
use App\Models\ActivityLog;
use App\Models\Announcement;
use App\Models\Media;
use RuntimeException;

final class AnnouncementController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $this->view('pages/announcements', [
            'title'         => 'Pengumuman - ' . \App\Config\App::NAME,
            'announcements' => (new Announcement())->withAuthor(),
        ]);
    }

    public function create(): void
    {
        $this->requireAdmin();

        $this->view('pages/announcement_form', [
            'title'        => 'Tambah Pengumuman - ' . \App\Config\App::NAME,
            'announcement' => null,
        ]);
    }

    public function store(): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();

        $errors = $this->validate([
            'title'       => 'required|min:3|max:180',
            'description' => 'required|min:5',
        ]);

        if ($errors !== []) {
            Session::setOld($_POST);
            Session::flash('error', implode(' ', $errors));
            $this->redirect('/announcements/create');
        }

        try {
            $imageId = $this->storeImageOrNull();
        } catch (RuntimeException $e) {
            Session::setOld($_POST);
            Session::flash('error', $e->getMessage());
            $this->redirect('/announcements/create');
        }

        $id = (new Announcement())->create([
            'title'       => Input::str('title'),
            'description' => Input::str('description'),
            'image_id'    => $imageId,
            'created_by'  => Auth::id(),
        ]);

        (new ActivityLog())->log(Auth::id(), 'Menambahkan pengumuman #' . $id);
        Session::flash('success', 'Pengumuman berhasil dipublikasikan.');
        $this->redirect('/announcements');
    }

    public function show(string $id): void
    {
        $this->requireAuth();

        $model        = new Announcement();
        $announcement = $model->findWithAuthor((int) $id);
        if ($announcement === null) {
            $this->abort(404);
        }

        $this->view('pages/announcement_show', [
            'title'        => $announcement['title'] . ' - ' . \App\Config\App::NAME,
            'announcement' => $announcement,
            'others'       => $model->latestExcept((int) $id, 3),
        ]);
    }

    /** Stream an announcement image to authenticated users. */
    public function image(string $id): void
    {
        $this->requireAuth();

        $announcement = (new Announcement())->find((int) $id);
        if ($announcement === null || empty($announcement['image_id'])) {
            $this->abort(404);
        }

        $media = (new Media())->findWithData((int) $announcement['image_id']);
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

    public function edit(string $id): void
    {
        $this->requireAdmin();

        $announcement = (new Announcement())->findWithAuthor((int) $id);
        if ($announcement === null) {
            $this->abort(404);
        }

        $this->view('pages/announcement_form', [
            'title'        => 'Edit Pengumuman - ' . \App\Config\App::NAME,
            'announcement' => $announcement,
        ]);
    }

    public function update(string $id): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();

        $announcementId = (int) $id;
        $model          = new Announcement();
        $announcement   = $model->find($announcementId);
        if ($announcement === null) {
            $this->abort(404);
        }

        $errors = $this->validate([
            'title'       => 'required|min:3|max:180',
            'description' => 'required|min:5',
        ]);

        if ($errors !== []) {
            Session::setOld($_POST);
            Session::flash('error', implode(' ', $errors));
            $this->redirect('/announcements/' . $announcementId . '/edit');
        }

        try {
            $newImageId = $this->storeImageOrNull();
        } catch (RuntimeException $e) {
            Session::setOld($_POST);
            Session::flash('error', $e->getMessage());
            $this->redirect('/announcements/' . $announcementId . '/edit');
        }

        $imageId = !empty($announcement['image_id']) ? (int) $announcement['image_id'] : null;
        if ($newImageId !== null) {
            if ($imageId !== null) {
                (new Media())->delete($imageId);
            }
            $imageId = $newImageId;
        } elseif (Input::bool('remove_image')) {
            if ($imageId !== null) {
                (new Media())->delete($imageId);
            }
            $imageId = null;
        }

        $model->update($announcementId, [
            'title'       => Input::str('title'),
            'description' => Input::str('description'),
            'image_id'    => $imageId,
        ]);

        (new ActivityLog())->log(Auth::id(), 'Memperbarui pengumuman #' . $announcementId);
        Session::flash('success', 'Pengumuman berhasil diperbarui.');
        $this->redirect('/announcements');
    }

    public function destroy(string $id): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();

        $announcementId = (int) $id;
        $model          = new Announcement();
        $announcement   = $model->find($announcementId);
        if ($announcement === null) {
            $this->abort(404);
        }

        if (!empty($announcement['image_id'])) {
            (new Media())->delete((int) $announcement['image_id']);
        }
        $model->delete($announcementId);

        (new ActivityLog())->log(Auth::id(), 'Menghapus pengumuman #' . $announcementId);
        Session::flash('success', 'Pengumuman berhasil dihapus.');
        $this->redirect('/announcements');
    }

    /** Store the optional announcement image, returning its media id. */
    private function storeImageOrNull(): ?int
    {
        $file = Input::file('image');
        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $stored = (new Media())->store(Uploader::readImage($file), 'news', Auth::id());

        return $stored['id'];
    }
}
