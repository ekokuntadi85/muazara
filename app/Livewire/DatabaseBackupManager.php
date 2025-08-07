<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;

#[Title('Manajemen Backup Database')]
class DatabaseBackupManager extends Component
{
    public $backups = [];
    public $isBackingUp = false;

    // Direktori tempat backup disimpan (sesuaikan jika berbeda)
    protected $backupDisk = 'local'; // Atau 's3' jika menggunakan S3
    protected $backupPath = 'backups'; // Subdirektori di dalam disk

    public function mount()
    {
        $this->getBackups();
    }

    public function getBackups()
    {
        $files = Storage::disk($this->backupDisk)->files($this->backupPath);
        \Illuminate\Support\Facades\Log::info('DatabaseBackupManager: Files found in backup path: ', ['files' => $files]);

        $this->backups = collect($files)->map(function ($file) {
            return [
                'name' => str_replace($this->backupPath . '/', '', $file),
                'size' => Storage::disk($this->backupDisk)->size($file),
                'last_modified' => Storage::disk($this->backupDisk)->lastModified($file),
            ];
        })->sortByDesc('last_modified')->values()->all();

        \Illuminate\Support\Facades\Log::info('DatabaseBackupManager: Backups array after processing: ', ['backups' => $this->backups]);
    }

    public function performBackup()
    {
        $this->isBackingUp = true;
        try {
            // Jalankan perintah artisan db:backup
            Artisan::call('db:backup');
            session()->flash('message', 'Backup database berhasil dibuat!');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal membuat backup: ' . $e->getMessage());
        } finally {
            $this->isBackingUp = false;
            $this->getBackups(); // Muat ulang daftar backup
        }
    }

    public function downloadBackup($filename)
    {
        // Pastikan file ada dan aman untuk diunduh
        if (Storage::disk($this->backupDisk)->exists($this->backupPath . '/' . $filename)) {
            return Storage::disk($this->backupDisk)->download($this->backupPath . '/' . $filename);
        }
        session()->flash('error', 'File backup tidak ditemukan.');
    }

    public function deleteBackup($filename)
    {
        // Pastikan file ada dan aman untuk dihapus
        if (Storage::disk($this->backupDisk)->exists($this->backupPath . '/' . $filename)) {
            Storage::disk($this->backupDisk)->delete($this->backupPath . '/' . $filename);
            session()->flash('message', 'File backup berhasil dihapus!');
            $this->getBackups(); // Muat ulang daftar backup
        } else {
            session()->flash('error', 'File backup tidak ditemukan.');
        }
    }

    public function render()
    {
        return view('livewire.database-backup-manager');
    }
}
