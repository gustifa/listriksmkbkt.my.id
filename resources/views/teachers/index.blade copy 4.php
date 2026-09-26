<x-app-layout>
    <div class="page-content">
        <div class="card shadow border-0">
            <div class="card-body">
                <div class="table-responsive">
                    {{ $dataTable->table(['class' => 'table table-striped table-bordered w-100']) }}
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
</x-app-layout>