@if(Auth::user()->hasRole('admin') || $exam->teacher_id === optional(Auth::user()->teacher)->id)
<div class="modal fade" id="modalTeamTeaching" tabindex="-1" aria-labelledby="modalTeamTeachingLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white py-3">
                <h6 class="modal-title fw-bold" id="modalTeamTeachingLabel">
                    <i class="fas fa-users-cog me-2"></i> Kelola Team Teaching (Guru Kolaborator)
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('guru.exams.team_teaching.update', $exam->id) }}" method="POST">
                @csrf
                <div class="modal-body p-3 p-md-4">
                    <p class="small text-muted mb-3">
                        Guru yang dipilih dapat mengelola soal, memantau pengerjaan, dan melihat rekap nilai ujian ini.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Pilih Guru Kolaborator:</label>
                        <div class="border rounded p-3 bg-light" style="max-height: 220px; overflow-y: auto;">
                            @forelse($availableTeachers ?? [] as $teacher)
                                @php
                                    $isChecked = false;
                                    if (isset($exam->collaborators) && is_iterable($exam->collaborators)) {
                                        $isChecked = collect($exam->collaborators)->contains('id', $teacher->id);
                                    }
                                @endphp
                                <div class="form-check mb-2">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="collaborator_ids[]" 
                                           value="{{ $teacher->id }}" 
                                           id="teacher_{{ $teacher->id }}"
                                           @checked($isChecked)>
                                    <label class="form-check-label small fw-semibold" for="teacher_{{ $teacher->id }}">
                                        {{ $teacher->name }} <span class="text-muted">({{ $teacher->nip ?? 'Guru' }})</span>
                                    </label>
                                </div>
                            @empty
                                <small class="text-muted d-block text-center py-2">Tidak ada data guru lain tersedia.</small>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm fw-bold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif