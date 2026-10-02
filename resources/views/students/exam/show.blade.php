<form action="{{ route('student.exam.submit', $session->id) }}" method="POST">
    @csrf
    @foreach($exam->questions as $index => $q)
        <div class="card mb-3 p-3">
            <p><strong>Soal {{ $index + 1 }}:</strong> {{ $q->question_text }}</p>

            @if($q->question_type === 'single')
                {{-- Pilihan Ganda (Radio Button) --}}
                @foreach($q->options as $opt)
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="answers[{{ $q->id }}][]" value="{{ $opt['key'] }}" id="q_{{ $q->id }}_{{ $opt['key'] }}">
                        <label class="form-check-label" for="q_{{ $q->id }}_{{ $opt['key'] }}">
                            {{ $opt['key'] }}. {{ $opt['text'] }}
                        </label>
                    </div>
                @endforeach

            @elseif($q->question_type === 'multiple')
                {{-- Multiple Choice / Centang Banyak (Checkbox) --}}
                @foreach($q->options as $opt)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="answers[{{ $q->id }}][]" value="{{ $opt['key'] }}" id="q_{{ $q->id }}_{{ $opt['key'] }}">
                        <label class="form-check-label" for="q_{{ $q->id }}_{{ $opt['key'] }}">
                            {{ $opt['key'] }}. {{ $opt['text'] }}
                        </label>
                    </div>
                @endforeach

            @elseif($q->question_type === 'essay')
                {{-- Essay (Textarea) --}}
                <textarea class="form-control" name="answers[{{ $q->id }}][]" rows="4" placeholder="Ketikkan jawaban Anda..."></textarea>
            @endif
        </div>
    @endforeach

    <button type="submit" class="btn btn-primary">Kirim Jawaban Ujian</button>
</form>