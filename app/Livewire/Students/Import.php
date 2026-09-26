<?php

namespace App\Livewire\Students;

use App\Imports\StudentImport;
use Livewire\Component;
use Livewire\WithFileUploads; // WAJIB untuk upload file
use Maatwebsite\Excel\Facades\Excel;

class Import extends Component
{
    use WithFileUploads;

    public $file; // Variabel penampung file

    public function import()
    {
        // 1. Validasi File
        $this->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048', // Max 2MB
        ]);

        try {
            // 2. Proses Impor
            Excel::import(new StudentImport, $this->file);

            // 3. Notifikasi Sukses (SweetAlert)
            $this->dispatch('show-alert', [
                'icon' => 'success',
                'title' => 'Berhasil!',
                'message' => 'Data siswa berhasil diimpor.',
            ]);

            // 4. Reset Input & Close Modal (jika pakai modal)
            $this->reset('file');
            $this->dispatch('close-modal'); // Event custom untuk tutup modal

        } catch (\Exception $e) {
            // Error Handling
            $this->dispatch('show-alert', [
                'icon' => 'error',
                'title' => 'Gagal!',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.students.import');
    }
}
