@extends('layouts.pegawai')

@section('content')
<style>
/* Menyamakan style dengan input_detail.blade.php */
.preview-wrapper {
    margin-top: 8px;
}
.preview-img {
    max-width: 160px;
    border-radius: 8px;
    border: 2px solid #198754;
    cursor: pointer;
    transition: transform .2s;
}
.preview-img:hover {
    transform: scale(1.05);
}
.foto-input {
    background-color: #f8f9fa;
}

/* camera frame: keep video and preview stacked and same size */
.camera-frame {
    position: relative;
    width: 100%;
}
.camera-frame video,
.camera-frame img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 8px;
}
.camera-frame .preview-img { max-width: none; }
</style>

<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Lapor Kerusakan / Maintenance</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('mobil.laporRusak') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="kondisi" value="Rusak Ringan">

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mb-4">
                    <h5 class="card-title">Informasi Kendaraan</h5>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="mobil_id" class="form-label">Pilih Mobil <span class="text-danger">*</span></label>
                            <select id="mobil_id" name="mobil_id" class="form-select @error('mobil_id') is-invalid @enderror" required>
                                <option value="" disabled {{ optional($mobil)->id ? '' : 'selected' }}>-- Pilih Mobil --</option>
                                @if(isset($mobilRusak) && $mobilRusak->count())
                                    @foreach($mobilRusak as $m)
                                        <option value="{{ $m->id }}" {{ (optional($mobil)->id == $m->id || old('mobil_id') == $m->id) ? 'selected' : '' }}>
                                            {{ $m->no_polisi }} ({{ optional($m->merek)->nama_merek ?? 'N/A' }})
                                        </option>
                                    @endforeach
                                @else
                                    <option value="" disabled>Tidak ada mobil tersedia</option>
                                @endif
                            </select>
                            @error('mobil_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h5 class="card-title">Jenis Laporan</h5>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="kategori" class="form-label">Pilih Kategori <span class="text-danger">*</span></label>
                            <select id="kategori_select" name="kategori" class="form-select @error('kategori') is-invalid @enderror" onchange="handleKategoriChange(this)" required>
                                <option value="" disabled selected>-- Pilih Kategori --</option>
                                <option value="Kecelakaan">Kecelakaan</option>
                                <option value="Servis">Servis</option>
                                <option value="Ganti Oli">Ganti Oli</option>
                                <option value="Pengajuan KIR">Pengajuan KIR</option>
                                <option value="Lainnya">Lainnya (Input Sendiri)</option>
                            </select>
                            @error('kategori')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12 mb-3" id="custom_input_container" style="display: none;">
                            <label for="kategori_custom" class="form-label">Sebutkan Kategori <span class="text-danger">*</span></label>
                            <input type="text" id="kategori_custom" name="kategori_custom" class="form-control" placeholder="Masukkan jenis laporan lainnya...">
                        </div>

                        <div class="col-md-12 mb-3" id="kir_photos_container" style="display: none;">
                            <label class="form-label">Foto Pengajuan KIR (Kiri, Kanan, Depan, Belakang) <span class="text-danger">*</span></label>
                            <div class="row g-3">
                                @php $kirSides = ['kiri' => 'Kiri', 'kanan' => 'Kanan', 'depan' => 'Depan', 'belakang' => 'Belakang']; @endphp
                                @foreach($kirSides as $key => $label)
                                    <div class="col-6 col-md-3 text-center">
                                        <input type="file" id="kir_{{ $key }}_input" name="kir_photos[{{ $key }}]" class="d-none" accept="image/*" capture="environment" required>
                                                <div class="camera-frame" style="width:100%;height:160px;border:1px solid #ccc;">
                                                    <video id="camera-video-kir_{{ $key }}" autoplay playsinline></video>
                                                    <div id="preview-container-kir_{{ $key }}" class="preview-wrapper d-none">
                                                        <img id="img-preview-kir_{{ $key }}" src="#" class="preview-img" onclick="zoomImageFor('kir_{{ $key }}')">
                                                    </div>
                                                </div>
                                                <div class="mt-2">
                                                    <button type="button" class="btn btn-sm btn-primary" id="start-camera-kir_{{ $key }}" onclick="startCameraFor('kir_{{ $key }}')">Buka Kamera</button>
                                                    <button type="button" class="btn btn-sm btn-success d-none" id="capture-kir_{{ $key }}" onclick="capturePhotoFor('kir_{{ $key }}')">Ambil</button>
                                                    <button type="button" class="btn btn-sm btn-warning d-none" id="retake-kir_{{ $key }}" onclick="retakePhotoFor('kir_{{ $key }}')">Ambil Ulang</button>
                                                </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h5 class="card-title"><i class="fas fa-camera"></i> Bukti Foto Kondisi</h5>
                    <div class="foto-input row mb-3 border rounded p-3">
                        <div class="col-md-12 mb-2">
                            <label class="form-label">Ambil Foto Bukti</label>

                            <!-- Hidden file input for form submission; keep capture for mobile fallback -->
                            <input type="file" id="bukti_input" name="foto_bukti" class="d-none" accept="image/*" capture="environment" required>

                            <!-- Camera interface (generic, prefix: bukti) -->
                            <div id="camera-container-bukti" class="text-center">
                                <div class="camera-frame" style="max-width:400px;height:220px;border:1px solid #ccc;">
                                    <video id="camera-video-bukti" autoplay playsinline></video>
                                    <div id="preview-container-bukti" class="preview-wrapper d-none text-center">
                                        <img id="img-preview-bukti" src="#" class="preview-img" onclick="zoomImageFor('bukti')">
                                    </div>
                                </div>

                                    <div class="mt-2">
                                        <button type="button" id="start-camera-bukti" class="btn btn-primary" onclick="startCameraFor('bukti')">
                                            <i class="fas fa-camera"></i> Buka Kamera
                                        </button>
                                        <button type="button" id="capture-bukti" class="btn btn-success d-none" onclick="capturePhotoFor('bukti')">
                                            <i class="fas fa-camera-retro"></i> Ambil Foto
                                        </button>
                                        <button type="button" id="retake-bukti" class="btn btn-warning d-none" onclick="retakePhotoFor('bukti')">
                                            <i class="fas fa-redo"></i> Ambil Ulang
                                        </button>
                                    </div>
                                </div>

                            <small class="text-danger d-block mt-1">
                                📸 Wajib diambil langsung dari kamera saat memilih kategori
                            </small>
                        </div>
                            @if($errors->has('foto') || $errors->has('foto.*.file') || $errors->has('foto_bukti'))
                                <div class="invalid-feedback d-block">
                                    {{ $errors->first('foto') ?? $errors->first('foto.*.file') ?? $errors->first('foto_bukti') }}
                                </div>
                            @endif
                    </div>
                </div>

                <!-- Foto SIM -->
                <div class="mb-4">
                    <h5 class="card-title"><i class="fas fa-id-card"></i> Foto SIM</h5>
                    <div class="foto-input row mb-3 border rounded p-3">
                        <div class="col-md-12 text-center">
                            <input type="file" id="sim_input" name="sim_foto" class="d-none" accept="image/*" capture="environment" required>
                            <div class="camera-frame" style="max-width:320px;height:180px;border:1px solid #ccc;">
                                <video id="camera-video-sim" autoplay playsinline></video>
                                <div id="preview-container-sim" class="preview-wrapper d-none">
                                    <img id="img-preview-sim" src="#" class="preview-img" onclick="zoomImageFor('sim')">
                                </div>
                            </div>
                            <div class="mt-2">
                                <button type="button" id="start-camera-sim" class="btn btn-primary" onclick="startCameraFor('sim')"><i class="fas fa-camera"></i> Buka Kamera</button>
                                <button type="button" id="capture-sim" class="btn btn-success d-none" onclick="capturePhotoFor('sim')"><i class="fas fa-camera-retro"></i> Ambil Foto</button>
                                <button type="button" id="retake-sim" class="btn btn-warning d-none" onclick="retakePhotoFor('sim')"><i class="fas fa-redo"></i> Ambil Ulang</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Foto STNK -->
                <div class="mb-4">
                    <h5 class="card-title"><i class="fas fa-file"></i> Foto STNK</h5>
                    <div class="foto-input row mb-3 border rounded p-3">
                        <div class="col-md-12 text-center">
                            <input type="file" id="stnk_input" name="stnk_foto" class="d-none" accept="image/*" capture="environment" required>
                            <div class="camera-frame" style="max-width:320px;height:180px;border:1px solid #ccc;">
                                <video id="camera-video-stnk" autoplay playsinline></video>
                                <div id="preview-container-stnk" class="preview-wrapper d-none">
                                    <img id="img-preview-stnk" src="#" class="preview-img" onclick="zoomImageFor('stnk')">
                                </div>
                            </div>
                            <div class="mt-2">
                                <button type="button" id="start-camera-stnk" class="btn btn-primary" onclick="startCameraFor('stnk')"><i class="fas fa-camera"></i> Buka Kamera</button>
                                <button type="button" id="capture-stnk" class="btn btn-success d-none" onclick="capturePhotoFor('stnk')"><i class="fas fa-camera-retro"></i> Ambil Foto</button>
                                <button type="button" id="retake-stnk" class="btn btn-warning d-none" onclick="retakePhotoFor('stnk')"><i class="fas fa-redo"></i> Ambil Ulang</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Odometer (foto) -->
                <div class="mb-4">
                    <h5 class="card-title"><i class="fas fa-tachometer-alt"></i> Foto Odo Meter</h5>
                    <div class="foto-input row mb-3 border rounded p-3">
                        <div class="col-md-12 text-center">
                            <input type="file" id="odo_input" name="odo_meter" class="d-none" accept="image/*" capture="environment" required>
                            <div class="camera-frame" style="max-width:320px;height:180px;border:1px solid #ccc;">
                                <video id="camera-video-odo" autoplay playsinline></video>
                                <div id="preview-container-odo" class="preview-wrapper d-none">
                                    <img id="img-preview-odo" src="#" class="preview-img" onclick="zoomImageFor('odo')">
                                </div>
                            </div>
                            <div class="mt-2">
                                <button type="button" id="start-camera-odo" class="btn btn-primary" onclick="startCameraFor('odo')"><i class="fas fa-camera"></i> Buka Kamera</button>
                                <button type="button" id="capture-odo" class="btn btn-success d-none" onclick="capturePhotoFor('odo')"><i class="fas fa-camera-retro"></i> Ambil Foto</button>
                                <button type="button" id="retake-odo" class="btn btn-warning d-none" onclick="retakePhotoFor('odo')"><i class="fas fa-redo"></i> Ambil Ulang</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="lokasi" class="form-label">Lokasi Kejadian <span class="text-danger">*</span></label>
                    <input type="text" id="lokasi" name="lokasi" class="form-control @error('lokasi') is-invalid @enderror" placeholder="Contoh: Parkiran A, Ruang 2..." required>
                    @error('lokasi')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="catatan" class="form-label">Keterangan / Keluhan Tambahan</label>
                    <textarea id="catatan" name="catatan" class="form-control" rows="3" placeholder="Jelaskan detail masalah..."></textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-paper-plane"></i> Kirim Laporan
                    </button>
                    <a href="{{ route('pegawai.mobilRusak') }}" class="btn btn-secondary btn-lg">
                        <i class="fas fa-arrow-left"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function handleKategoriChange(select) {
        const customContainer = document.getElementById('custom_input_container');
        const customInput = document.getElementById('kategori_custom');
        const kirContainer = document.getElementById('kir_photos_container');

        if (select.value === 'Lainnya') {
            customContainer.style.display = 'block';
            customInput.setAttribute('required', 'required');
        } else {
            customContainer.style.display = 'none';
            customInput.removeAttribute('required');
        }

        if (select.value === 'Pengajuan KIR') {
            kirContainer.style.display = 'block';
            ['kiri','kanan','depan','belakang'].forEach(k => {
                const el = document.getElementById(`kir_${k}_input`);
                if (el) el.setAttribute('required','required');
            });
        } else {
            kirContainer.style.display = 'none';
            ['kiri','kanan','depan','belakang'].forEach(k => {
                const el = document.getElementById(`kir_${k}_input`);
                if (el) el.removeAttribute('required');
            });
        }

        setTimeout(() => {
            if (select.value === 'Pengajuan KIR') {
                startCameraFor('kir_kiri');
            } else {
                startCameraFor('bukti');
            }
        }, 300);
    }

    const _cameraStreams = {};

    function _hasGetUserMedia() {
        return !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia) ||
               !!(navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia);
    }

    async function startCameraFor(prefix) {
        const video = document.getElementById(`camera-video-${prefix}`);
        const startBtn = document.getElementById(`start-camera-${prefix}`);
        const captureBtn = document.getElementById(`capture-${prefix}`);
        const retakeBtn = document.getElementById(`retake-${prefix}`);
        const fileInput = document.getElementById(`${prefix}_input`);

        if (!video || !startBtn) return;

        if (!_hasGetUserMedia()) {
            try { if (fileInput) fileInput.click(); } catch (e) { alert('Perangkat Anda tidak mendukung pengambilan foto langsung di browser.'); }
            return;
        }

        try {
            const getUserMedia = navigator.mediaDevices && navigator.mediaDevices.getUserMedia
                ? navigator.mediaDevices.getUserMedia.bind(navigator.mediaDevices)
                : (navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia).bind(navigator);

            const stream = await getUserMedia({ video: { facingMode: 'environment' }, audio: false });
            _cameraStreams[prefix] = stream;
            video.srcObject = stream;
            await video.play();
            startBtn.classList.add('d-none');
            if (captureBtn) captureBtn.classList.remove('d-none');
            if (retakeBtn) retakeBtn.classList.add('d-none');
        } catch (err) {
            try { if (fileInput) fileInput.click(); } catch (e) { alert('Gagal membuka kamera: ' + (err.message || err)); }
        }
    }

    async function _waitForVideoReady(video, timeout = 1500) {
        if (video.videoWidth && video.videoHeight) return;
        return new Promise((resolve) => {
            let resolved = false;
            const onReady = () => {
                if (resolved) return;
                if (video.videoWidth && video.videoHeight) { resolved = true; cleanup(); resolve(); }
            };
            const cleanup = () => { video.removeEventListener('loadedmetadata', onReady); video.removeEventListener('playing', onReady); };
            video.addEventListener('loadedmetadata', onReady);
            video.addEventListener('playing', onReady);
            setTimeout(() => { if (!resolved) { resolved = true; cleanup(); resolve(); } }, timeout);
        });
    }

    async function capturePhotoFor(prefix) {
        const video = document.getElementById(`camera-video-${prefix}`);
        if (!video) return;
        await _waitForVideoReady(video, 2000);

        const canvas = document.createElement('canvas');
        const vw = video.videoWidth || video.clientWidth || 1280;
        const vh = video.videoHeight || Math.floor(vw * 0.75) || 720;
        canvas.width = vw; canvas.height = vh;
        const ctx = canvas.getContext('2d');
        try { ctx.drawImage(video, 0, 0, canvas.width, canvas.height); } catch (drawErr) { console.warn('drawImage failed', drawErr); try { document.getElementById(`${prefix}_input`).click(); } catch(e){} return; }

        canvas.toBlob(function(blob) {
            const file = new File([blob], prefix + '_' + Date.now() + '.jpg', { type: 'image/jpeg' });
            try {
                const dt = new DataTransfer(); dt.items.add(file);
                const input = document.getElementById(`${prefix}_input`);
                if (input) input.files = dt.files;

                const reader = new FileReader();
                reader.onload = function(e) { const img = document.getElementById(`img-preview-${prefix}`); if (img) img.src = e.target.result; const pc = document.getElementById(`preview-container-${prefix}`); if (pc) { pc.classList.remove('d-none'); } if (video) { video.style.display = 'none'; } };
                reader.readAsDataURL(file);
            } catch (e) {
                const dataUrl = canvas.toDataURL('image/jpeg', 0.9);
                const img = document.getElementById(`img-preview-${prefix}`); if (img) img.src = dataUrl; const pc = document.getElementById(`preview-container-${prefix}`); if (pc) { pc.classList.remove('d-none'); } if (video) { video.style.display = 'none'; }
                alert('Foto siap sebagai preview. Jika form tidak mengirim gambar, tekan "Buka Kamera" lalu ambil foto menggunakan perangkat Anda sebagai alternatif.');
            }

            if (_cameraStreams[prefix]) { _cameraStreams[prefix].getTracks().forEach(t => t.stop()); delete _cameraStreams[prefix]; }
            const v = document.getElementById(`camera-video-${prefix}`); if (v) v.srcObject = null;
            const captureBtn = document.getElementById(`capture-${prefix}`); if (captureBtn) captureBtn.classList.add('d-none');
            const retakeBtn = document.getElementById(`retake-${prefix}`); if (retakeBtn) retakeBtn.classList.remove('d-none');
        }, 'image/jpeg', 0.9);
    }

    function retakePhotoFor(prefix) {
        const input = document.getElementById(`${prefix}_input`); if (input) input.value = null;
        const pc = document.getElementById(`preview-container-${prefix}`); if (pc) pc.classList.add('d-none');
        const ret = document.getElementById(`retake-${prefix}`); if (ret) ret.classList.add('d-none');
        const start = document.getElementById(`start-camera-${prefix}`); if (start) start.classList.remove('d-none');
        const video = document.getElementById(`camera-video-${prefix}`); if (video) { video.style.display = ''; }
    }

    function showFilePreviewFor(prefix, ev) {
        const fileInput = ev instanceof Event ? ev.target : ev;
        const file = fileInput.files && fileInput.files[0]; if (!file) return;
        const reader = new FileReader();
        reader.onload = function(e) { const img = document.getElementById(`img-preview-${prefix}`); if (img) img.src = e.target.result; const pc = document.getElementById(`preview-container-${prefix}`); if (pc) pc.classList.remove('d-none'); const start = document.getElementById(`start-camera-${prefix}`); if (start) start.classList.add('d-none'); const capt = document.getElementById(`capture-${prefix}`); if (capt) capt.classList.add('d-none'); const ret = document.getElementById(`retake-${prefix}`); if (ret) ret.classList.remove('d-none'); };
        reader.readAsDataURL(file);
    }

    // Attach change listeners
    ['bukti','sim','stnk','odo','kir_kiri','kir_kanan','kir_depan','kir_belakang'].forEach(prefix => {
        const el = document.getElementById(prefix + '_input'); if (el) el.addEventListener('change', (e)=> showFilePreviewFor(prefix, e));
    });

    function zoomImageFor(prefix) {
        const src = document.getElementById(`img-preview-${prefix}`).src;
        const modal = document.createElement('div'); modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.8);display:flex;align-items:center;justify-content:center;z-index:9999;';
        modal.innerHTML = `<div style="position:relative"><img src="${src}" style="max-width:90vw;max-height:90vh;border-radius:12px"><span style="position:absolute;top:-10px;right:-10px;background:#fff;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer">×</span></div>`;
        modal.onclick = () => modal.remove(); document.body.appendChild(modal);
    }
</script>
@endsection