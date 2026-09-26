<?php

namespace App\DataTables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class UserDataTable extends DataTable
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
            ->addColumn('hak_akses', function($row) {
                $badges = [
                    'siswa' => 'bg-success',
                    'guru'  => 'bg-dark',
                    'wakil' => 'bg-success',
                    'piket' => 'bg-danger',
                    'admin' => 'bg-primary'
                ];
                $class = $badges[$row->jenis_user] ?? 'bg-secondary';
                return '<span class="badge '.$class.'">'.ucfirst($row->jenis_user).'</span>';
            })
            ->addColumn('status_switch', function($row) {
                $checked = $row->status ? 'checked' : '';
                return '<div class="form-check form-switch">
                            <input class="form-check-input large-chexbox status-toggle" type="checkbox" 
                                   data-user="'.$row->id.'" '.$checked.'>
                        </div>';
            })
            ->addColumn('action', function($row) {
                return '<div class="d-flex order-actions">
                            <a href="#" class="btn btn-sm btn-warning me-1"><i class="bx bx-detail"></i></a>
                            <a href="#" class="btn btn-sm btn-info me-1"><i class="bx bx-edit"></i></a>
                            <button class="btn btn-sm btn-danger delete-btn" data-id="'.$row->id.'"><i class="lni lni-trash"></i></button>
                        </div>';
            })
            ->rawColumns(['hak_akses', 'status_switch', 'action']); // Agar HTML dirender
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(User $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('user-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->orderBy(1)
                    ->selectStyleSingle()
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
    {
        return [
            Column::make('DT_RowIndex')->title('No')->orderable(false)->searchable(false)->width(5),
            Column::make('name')->title('Nama Pengguna'),
            Column::make('email')->title('Email'),
            Column::computed('hak_akses')->title('Hak Akses'),
            Column::computed('status_switch')->title('Status'),
            Column::computed('action')->title('Action')->exportable(false)->printable(false)->width(100),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'User_' . date('YmdHis');
    }
}
