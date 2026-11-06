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
use App\Models\Upload;
use RuntimeException;

final class UploadController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $this->view('pages/uploads', [
            'title'   => 'Upload Center - ' . \App\Config\App::NAME,
            'uploads' => (new Upload())->withUploader(),
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();

        $this->view('pages/upload_form', [
            'title' => 'Upload Berkas - ' . \App\Config\App::NAME,
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $errors = $this->validate([
            'title'       => 'required|min:3|max:180',
            'description' => 'max:2000',
        ]);

        if ($errors !== []) {
            Session::setOld($_POST);
            Session::flash('error', implode(' ', $errors));
            $this->redirect('/uploads/create');
        }

        try {
            $file = Uploader::readDocument(Input::file('file') ?? []);
        } catch (RuntimeException $e) {
            Session::setOld($_POST);
            Session::flash('error', $e->getMessage());
            $this->redirect('/uploads/create');
        }

        $stored = (new Media())->store($file, 'upload', Auth::id());

        $id = (new Upload())->create([
            'title'         => Input::str('title'),
            'description'   => Input::str('description'),
            'filename'      => $file['filename'],
            'original_name' => $file['original_name'],
            'media_id'      => $stored['id'],
            'filesize'      => $file['filesize'],
            'filetype'      => $file['filetype'],
            'uploaded_by'   => Auth::id(),
        ]);

        (new ActivityLog())->log(Auth::id(), 'Mengunggah berkas #' . $id . ' (' . $file['original_name'] . ')');
        Session::flash('success', 'Berkas berhasil diunggah.');
        $this->redirect('/uploads');
    }

    public function download(string $id): void
    {
        $this->requireAuth();

        $upload = (new Upload())->find((int) $id);
        if ($upload === null) {
            $this->abort(404);
        }

        if (empty($upload['media_id'])) {
            Session::flash('error', 'Berkas tidak ditemukan di server.');
            $this->redirect('/uploads');
        }

        $media = (new Media())->findWithData((int) $upload['media_id']);
        if ($media === null) {
            Session::flash('error', 'Berkas tidak ditemukan di server.');
            $this->redirect('/uploads');
        }

        $this->sendBlob((string) $media['data'], (string) $upload['original_name'], (string) $upload['filetype']);
    }

    public function destroy(string $id): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $uploadId = (int) $id;
        $model    = new Upload();
        $upload   = $model->find($uploadId);

        if ($upload === null) {
            $this->abort(404);
        }
        if (!Auth::isAdmin() && (int) $upload['uploaded_by'] !== Auth::id()) {
            Session::flash('error', 'Anda tidak berhak menghapus berkas ini.');
            $this->redirect('/uploads');
        }

        if (!empty($upload['media_id'])) {
            (new Media())->delete((int) $upload['media_id']);
        }
        $model->delete($uploadId);

        (new ActivityLog())->log(Auth::id(), 'Menghapus berkas #' . $uploadId);
        Session::flash('success', 'Berkas berhasil dihapus.');
        $this->redirect('/uploads');
    }
}
