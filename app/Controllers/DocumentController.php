<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Input;
use App\Core\Session;
use App\Core\Uploader;
use App\Models\ActivityLog;
use App\Models\Document;
use App\Models\Media;
use RuntimeException;

final class DocumentController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $this->view('pages/documents', [
            'title'     => 'Dokumen - ' . \App\Config\App::NAME,
            'documents' => (new Document())->withUploader(),
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();

        $this->view('pages/document_form', [
            'title' => 'Upload Dokumen - ' . \App\Config\App::NAME,
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
            $this->redirect('/documents/create');
        }

        try {
            $file = Uploader::readDocument(Input::file('document') ?? []);
        } catch (RuntimeException $e) {
            Session::setOld($_POST);
            Session::flash('error', $e->getMessage());
            $this->redirect('/documents/create');
        }

        $stored = (new Media())->store($file, 'document', Auth::id());

        $id = (new Document())->create([
            'title'         => Input::str('title'),
            'description'   => Input::str('description'),
            'filename'      => $file['filename'],
            'original_name' => $file['original_name'],
            'media_id'      => $stored['id'],
            'filesize'      => $file['filesize'],
            'filetype'      => $file['filetype'],
            'uploaded_by'   => Auth::id(),
        ]);

        (new ActivityLog())->log(Auth::id(), 'Mengunggah dokumen #' . $id . ' (' . $file['original_name'] . ')');
        Session::flash('success', 'Dokumen berhasil diunggah.');
        $this->redirect('/documents');
    }

    public function download(string $id): void
    {
        $this->requireAuth();

        $document = (new Document())->find((int) $id);
        if ($document === null) {
            $this->abort(404);
        }

        if (empty($document['media_id'])) {
            Session::flash('error', 'Berkas tidak ditemukan di server.');
            $this->redirect('/documents');
        }

        $media = (new Media())->findWithData((int) $document['media_id']);
        if ($media === null) {
            Session::flash('error', 'Berkas tidak ditemukan di server.');
            $this->redirect('/documents');
        }

        $this->sendBlob((string) $media['data'], (string) $document['original_name'], (string) $document['filetype']);
    }

    public function destroy(string $id): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $documentId = (int) $id;
        $model      = new Document();
        $document   = $model->find($documentId);

        if ($document === null) {
            $this->abort(404);
        }
        if (!Auth::isAdmin() && (int) $document['uploaded_by'] !== Auth::id()) {
            Session::flash('error', 'Anda tidak berhak menghapus dokumen ini.');
            $this->redirect('/documents');
        }

        if (!empty($document['media_id'])) {
            (new Media())->delete((int) $document['media_id']);
        }
        $model->delete($documentId);

        (new ActivityLog())->log(Auth::id(), 'Menghapus dokumen #' . $documentId);
        Session::flash('success', 'Dokumen berhasil dihapus.');
        $this->redirect('/documents');
    }
}
