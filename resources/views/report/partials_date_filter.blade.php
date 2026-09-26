<div class="row">
    <div class="mb-3 col-md-6">
        <label class="form-label fw-bold">Bulan</label>
        <select name="month" class="form-control">
            @foreach(range(1,12) as $m)
                <option value="{{ $m }}" {{ date('n') == $m ? 'selected' : '' }}>
                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="mb-3 col-md-6">
        <label class="form-label fw-bold">Tahun</label>
        <input type="number" name="year" class="form-control" value="{{ date('Y') }}" required>
    </div>
</div>
