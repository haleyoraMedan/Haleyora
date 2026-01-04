<!-- Reusable form for create/edit mobil -->
<div class="admin-card">
    <h3 class="admin-title mb-4">{{ isset($mobil) ? 'Edit Mobil' : 'Tambah Mobil' }}</h3>
    
    <form action="{{ isset($mobil) ? route('mobil.update', $mobil->id) : route('mobil.store') }}" method="POST">
        @csrf
        @if(isset($mobil)) @method('PUT') @endif

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">No Polisi</label>
                <input type="text" name="no_polisi" class="form-control @error('no_polisi') is-invalid @enderror" value="{{ $mobil->no_polisi ?? old('no_polisi') }}" required>
                @error('no_polisi') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Merek</label>
                <select name="merek_id" class="form-select @error('merek_id') is-invalid @enderror" required>
                    <option value="">-- Pilih Merek --</option>
                    @foreach($merek as $m)
                        <option value="{{ $m->id }}" {{ isset($mobil) && $mobil->merek_id == $m->id ? 'selected' : (old('merek_id') == $m->id ? 'selected' : '') }}>
                            {{ $m->nama_merek }}
                        </option>
                    @endforeach
                </select>
                @error('merek_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Jenis</label>
                <select name="jenis_id" class="form-select @error('jenis_id') is-invalid @enderror" required>
                    <option value="">-- Pilih Jenis --</option>
                    @foreach($jenis as $j)
                        <option value="{{ $j->id }}" {{ isset($mobil) && $mobil->jenis_id == $j->id ? 'selected' : (old('jenis_id') == $j->id ? 'selected' : '') }}>
                            {{ $j->nama_jenis }}
                        </option>
                    @endforeach
                </select>
                @error('jenis_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Tahun</label>
                <input type="number" name="tahun" class="form-control @error('tahun') is-invalid @enderror" value="{{ $mobil->tahun ?? old('tahun') }}" required>
                @error('tahun') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Warna</label>
                <input type="text" name="warna" class="form-control @error('warna') is-invalid @enderror" value="{{ $mobil->warna ?? old('warna') }}" required>
                @error('warna') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">No Rangka</label>
                <input type="text" name="no_rangka" class="form-control @error('no_rangka') is-invalid @enderror" value="{{ $mobil->no_rangka ?? old('no_rangka') }}">
                @error('no_rangka') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">No Mesin</label>
                <input type="text" name="no_mesin" class="form-control @error('no_mesin') is-invalid @enderror" value="{{ $mobil->no_mesin ?? old('no_mesin') }}">
                @error('no_mesin') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-12 mb-3">
                <label class="form-label">Penempatan</label>
                <select name="penempatan_id" class="form-select @error('penempatan_id') is-invalid @enderror">
                    <option value="">-- Pilih Penempatan --</option>
                    @foreach($penempatans as $p)
                        <option value="{{ $p->id }}" {{ isset($mobil) && $mobil->penempatan_id == $p->id ? 'selected' : (old('penempatan_id') == $p->id ? 'selected' : '') }}>
                            {{ $p->nama_kantor }} - {{ $p->kota }}, {{ $p->provinsi }} - {{ $p->alamat }}
                        </option>
                    @endforeach
                </select>
                @error('penempatan_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="admin-btn primary">{{ isset($mobil) ? 'Update' : 'Simpan' }}</button>
            <a href="{{ route('mobil.index') }}" class="admin-btn">Kembali</a>
        </div>
    </form>
</div>
