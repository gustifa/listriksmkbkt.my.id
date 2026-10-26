@extends('layouts.app') {{-- Sesuaikan master layout kamu --}}

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 fw-bold"><i class="bx bx-bell me-2 text-primary"></i>Semua Notifikasi</h5>
                    @if(Auth::user()->unreadNotifications->count() > 0)
                        <a href="{{ route('notifications.readAll') }}" class="btn btn-sm btn-outline-primary">
                            <i class="bx bx-check-double me-1"></i>Tandai Semua Dibaca
                        </a>
                    @endif
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($notifications as $notification)
                            <a href="{{ $notification->data['url'] ?? '#' }}" 
                               class="list-group-item list-group-item-action d-flex align-items-center p-3 {{ $notification->read_at ? 'bg-white text-muted' : 'bg-light fw-bold' }}">
                                
                                <div class="avatar-sm me-3">
                                    <span class="avatar-title rounded-circle {{ $notification->read_at ? 'bg-secondary' : 'bg-primary' }} text-white p-2">
                                        <i class="{{ $notification->data['icon'] ?? 'bx bx-bell' }} fs-4"></i>
                                    </span>
                                </div>
                                
                                <div class="flex-grow-1">
                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                        <h6 class="mb-1 {{ $notification->read_at ? 'text-secondary' : 'text-dark' }}">
                                            {{ $notification->data['title'] ?? 'Notifikasi' }}
                                        </h6>
                                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-0 small text-secondary">
                                        {{ $notification->data['message'] ?? '' }}
                                    </p>
                                </div>
                            </a>
                        @empty
                            <div class="text-center py-5 text-muted">
                                <i class="bx bx-bell-off fs-1 d-block mb-2"></i>
                                <p class="mb-0">Belum ada riwayat notifikasi.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
                @if($notifications->hasPages())
                    <div class="card-footer bg-white d-flex justify-content-center py-3">
                        {{ $notifications->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection