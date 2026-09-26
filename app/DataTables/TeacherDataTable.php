<?php

namespace App\DataTables;

use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class TeacherDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('nama_lengkap', function($row) {
                return $row->user->name ?? '-';
            })
            ->addColumn('email_login', function($row) {
                return $row->user->email ?? '-';
            })
            ->addColumn('keterangan', function($row) {
                $html = '';
                if($row->major) {
                    $html .= '<span class="badge bg-info text-dark">Guru Jurusan '.($row->major->code ?? '').'</span>';
                } else {
                    $html .= '<span class="badge bg-secondary">Guru Umum</span>';
                }

                if($row->role_type == 'piket') {
                    $html .= '<div class="mt-1"><span class="badge bg-warning text-dark">Petugas Piket</span></div>';
                }
                return $html;
            })
            ->addColumn('action', function($row) {

                return '
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-warning btn-edit" data-id="'.$row->id.'">
                        <i class="bx bx-message-square-edit"></i>
                    </button>
                    <form action="'.route('teachers.destroy', $row->id).'" method="POST" class="d-inline">
                        '.csrf_field().method_field('DELETE').'
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'Yakin hapus?\')">
                            <i class="bx bx-trash"></i>
                        </button>
                    </form>
                </div>';
            })
            ->rawColumns(['keterangan', 'action']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Teacher $model): QueryBuilder
    {
        // Gunakan with() agar tidak terjadi N+1 query problem
        // return $model->newQuery()->with(['user', 'major']);
        // Pastikan kolom 'id' ikut dipanggil
        return $model->newQuery()->with('user')->select('teachers.*');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('teacher-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->orderBy(1)
                    // ->parameters([
                    //     'language' => ['url' => '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json']
                    // ]);

                    ->parameters([
                    // Ganti URL dengan objek bahasa langsung
                    'language' => [
                        'emptyTable' => 'Tidak ada data yang tersedia pada tabel ini',
                        'info' => 'Menampilkan _START_ sampai _END_ dari _TOTAL_ entri',
                        'infoEmpty' => 'Menampilkan 0 sampai 0 dari 0 entri',
                        'infoFiltered' => '(disaring dari _MAX_ entri keseluruhan)',
                        'lengthMenu' => 'Tampilkan _MENU_ entri',
                        'loadingRecords' => 'Sedang memuat...',
                        'processing' => 'Sedang memproses...',
                        'search' => 'Cari:',
                        'zeroRecords' => 'Tidak ditemukan data yang sesuai',
                        'paginate' => [
                            'first' => 'Pertama',
                            'last' => 'Terakhir',
                            'next' => 'Selanjutnya',
                            'previous' => 'Sebelumnya'
                        ]
                    ]
                    ]);


    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    // {
    //     return [
    //         Column::make('DT_RowIndex')->title('No')->orderable(false)->searchable(false)->width(30),
    //         Column::computed('nama_lengkap')->title('Nama Lengkap'),
    //         Column::make('nip')->title('NIP')->addClass('text-center'),
    //         Column::make('gender')->title('L/P')->addClass('text-center'),
    //         Column::computed('email_login')->title('Email (Login)'),
    //         Column::make('phone')->title('No. HP'),
    //         Column::computed('keterangan')->title('Keterangan')->addClass('text-center'),
    //         Column::computed('action')->title('Aksi')->exportable(false)->printable(false)->addClass('text-center'),
    //     ];
    // }


    {
    return [
        Column::make('DT_RowIndex')->title('No')->orderable(false)->searchable(false)->width(30),
        // Tambahkan 'name' sebagai data agar bisa dicari, tapi title tetap 'Nama Lengkap'
        Column::make('user.name')->title('Nama Lengkap'), 
        Column::make('nip')->title('NIP')->addClass('text-center'),
        Column::make('gender')->title('L/P')->addClass('text-center'),
        Column::make('user.email')->title('Email (Login)'),
        Column::make('phone')->title('No. HP'),
        Column::computed('keterangan')->title('Keterangan')->addClass('text-center'),
        Column::computed('action')->title('Aksi')->addClass('text-center'),
    ];
}

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Teacher_' . date('YmdHis');
    }
}
