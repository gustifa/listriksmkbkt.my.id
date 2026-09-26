<x-app-layout>
<div class="page-content">
        <div class="container mt-5">
            <div class="mx-auto col-md-6">
                <div class="shadow card">
                    <div class="card-header bg-warning text-dark">Edit Permission</div>
                    <div class="card-body">
                        <form action="{{ route('permissions.update', $permission->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="fw-bold">Nama Permission</label>
                                <input type="text" name="name" class="form-control" value="{{ $permission->name }}" required>
                            </div>

                            <div class="mt-4 d-flex justify-content-between">
                                <a href="{{ route('permissions.index') }}" class="btn btn-secondary">Batal</a>
                                <button type="submit" class="btn btn-warning">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
