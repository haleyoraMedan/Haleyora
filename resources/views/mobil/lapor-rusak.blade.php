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
                    </div>
                </div>

                <div class="mb-4">
                    <h5 class="card-title"><i class="fas fa-camera"></i> Bukti Foto Kondisi</h5>
                    <div class="foto-input row mb-3 border rounded p-3">
                        <div class="col-md-12 mb-2">
                            <label class="form-label">Ambil Foto Bukti</label>

                            <!-- Hidden file input for form submission; keep capture for mobile fallback -->
                            <input type="file" id="foto_bukti" name="foto_bukti" class="d-none" accept="image/*" capture="environment" required>

                            <!-- Camera interface -->
                            <div id="camera-container" class="text-center">
                                <video id="camera-video" autoplay playsinline style="width: 100%; max-width: 400px; border: 1px solid #ccc; border-radius: 8px;"></video>
                                <div class="mt-2">
                                    <button type="button" id="start-camera-btn" class="btn btn-primary" onclick="startCamera()">
                                        <i class="fas fa-camera"></i> Buka Kamera
                                    </button>
                                    <button type="button" id="capture-btn" class="btn btn-success d-none" onclick="capturePhoto()">
                                        <i class="fas fa-camera-retro"></i> Ambil Foto
                                    </button>
                                    <button type="button" id="retake-btn" class="btn btn-warning d-none" onclick="retakePhoto()">
                                        <i class="fas fa-redo"></i> Ambil Ulang
                                    </button>
                                </div>
                            </div>

                            <div id="preview-container" class="preview-wrapper d-none text-center">
                                <small class="text-success d-block mb-1">
                                    <i class="fas fa-check-circle"></i> Foto berhasil diambil
                                </small>
                                <img id="img-preview" src="#" class="preview-img" onclick="zoomImage()" style="max-width: 100%; border-radius: 8px;">
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

                <div class="mb-4">
                    <label for="lokasi" class="form-label">Lokasi Kejadian (opsional)</label>
                    <input type="text" id="lokasi" name="lokasi" class="form-control" placeholder="Contoh: Parkiran A, Ruang 2...">
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
        
        if (select.value === 'Lainnya') {
            customContainer.style.display = 'block';
            customInput.setAttribute('required', 'required');
        } else {
            customContainer.style.display = 'none';
            customInput.removeAttribute('required');
        }

        setTimeout(() => {
            // Open camera UI when kategori changed
            startCamera();
        }, 300);
    }

    let _cameraStream = null;
    function _hasGetUserMedia() {
        return !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia) ||
               !!(navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia);
    }

    async function startCamera() {
        const video = document.getElementById('camera-video');
        const startBtn = document.getElementById('start-camera-btn');
        const captureBtn = document.getElementById('capture-btn');
        const retakeBtn = document.getElementById('retake-btn');

        // If getUserMedia is not available, fallback to native file input (will open camera on mobile)
        if (!_hasGetUserMedia()) {
            try {
                document.getElementById('foto_bukti').click();
            } catch (e) {
                alert('Perangkat Anda tidak mendukung pengambilan foto langsung di browser.');
            }
            return;
        }

        try {
            const getUserMedia = navigator.mediaDevices && navigator.mediaDevices.getUserMedia
                ? navigator.mediaDevices.getUserMedia.bind(navigator.mediaDevices)
                : (navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia).bind(navigator);

            _cameraStream = await getUserMedia({ video: { facingMode: 'environment' }, audio: false });
            // Some older prefixed implementations return stream differently; normalize
            video.srcObject = _cameraStream;
            await video.play();
            startBtn.classList.add('d-none');
            captureBtn.classList.remove('d-none');
            retakeBtn.classList.add('d-none');
        } catch (err) {
            // Fallback to native picker if stream cannot be opened
            try {
                document.getElementById('foto_bukti').click();
            } catch (e) {
                alert('Gagal membuka kamera: ' + (err.message || err));
            }
        }
    }

    async function _waitForVideoReady(video, timeout = 1500) {
        if (video.videoWidth && video.videoHeight) return;
        return new Promise((resolve) => {
            let resolved = false;
            const onReady = () => {
                if (resolved) return;
                if (video.videoWidth && video.videoHeight) {
                    resolved = true;
                    cleanup();
                    resolve();
                }
            };
            const cleanup = () => {
                video.removeEventListener('loadedmetadata', onReady);
                video.removeEventListener('playing', onReady);
            };
            video.addEventListener('loadedmetadata', onReady);
            video.addEventListener('playing', onReady);
            // fallback timeout
            setTimeout(() => {
                if (!resolved) {
                    resolved = true;
                    cleanup();
                    resolve();
                }
            }, timeout);
        });
    }

    async function capturePhoto() {
        const video = document.getElementById('camera-video');
        // Ensure video has dimensions before capturing (mobile may be slow)
        await _waitForVideoReady(video, 2000);

        const canvas = document.createElement('canvas');
        // prefer natural dimensions if available
        const vw = video.videoWidth || video.clientWidth || 1280;
        const vh = video.videoHeight || Math.floor(vw * 0.75) || 720;
        canvas.width = vw;
        canvas.height = vh;
        const ctx = canvas.getContext('2d');
        try {
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        } catch (drawErr) {
            // if drawImage fails, fall back to showing an error and triggering native picker
            console.warn('drawImage failed', drawErr);
            try { document.getElementById('foto_bukti').click(); } catch(e){}
            return;
        }

        canvas.toBlob(function(blob) {
            const file = new File([blob], 'lapor_rusak_' + Date.now() + '.jpg', { type: 'image/jpeg' });
            // Put file into hidden input using DataTransfer
            try {
                const dt = new DataTransfer();
                dt.items.add(file);
                const input = document.getElementById('foto_bukti');
                input.files = dt.files;

                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('img-preview').src = e.target.result;
                    document.getElementById('preview-container').classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            } catch (e) {
                // Some mobile browsers (notably older iOS Safari) prevent programmatic
                // assignment to input.files. In that case, show the preview and instruct
                // the user to tap the camera button to attach the file via native picker.
                const dataUrl = canvas.toDataURL('image/jpeg', 0.9);
                document.getElementById('img-preview').src = dataUrl;
                document.getElementById('preview-container').classList.remove('d-none');
                alert('Foto siap sebagai preview. Jika form tidak mengirim gambar, tekan "Buka Kamera" lalu ambil foto menggunakan perangkat Anda sebagai alternatif.');
            }

            // Stop camera stream
            if (_cameraStream) {
                _cameraStream.getTracks().forEach(t => t.stop());
                _cameraStream = null;
            }

            document.getElementById('camera-video').srcObject = null;
            document.getElementById('capture-btn').classList.add('d-none');
            document.getElementById('retake-btn').classList.remove('d-none');
        }, 'image/jpeg', 0.9);
    }

    function retakePhoto() {
        // Clear current file and preview, allow opening camera again
        const input = document.getElementById('foto_bukti');
        input.value = null;
        document.getElementById('preview-container').classList.add('d-none');
        document.getElementById('retake-btn').classList.add('d-none');
        document.getElementById('start-camera-btn').classList.remove('d-none');
    }

    // Show preview when user selects a file via native picker fallback
    function showFilePreview(input) {
        const fileInput = input instanceof Event ? input.target : input;
        const file = fileInput.files && fileInput.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('img-preview').src = e.target.result;
            document.getElementById('preview-container').classList.remove('d-none');
            // Show retake button and hide start
            document.getElementById('start-camera-btn').classList.add('d-none');
            document.getElementById('capture-btn').classList.add('d-none');
            document.getElementById('retake-btn').classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    }

    document.getElementById('foto_bukti')?.addEventListener('change', showFilePreview);

    function zoomImage() {
        const src = document.getElementById('img-preview').src;
        const modal = document.createElement("div");
        modal.style.cssText = `
            position:fixed;inset:0;background:rgba(0,0,0,.8);
            display:flex;align-items:center;justify-content:center;z-index:9999;
        `;
        modal.innerHTML = `
            <div style="position:relative">
                <img src="${src}" style="max-width:90vw;max-height:90vh;border-radius:12px">
                <span style="position:absolute;top:-10px;right:-10px;background:#fff;width:32px;height:32px;
                border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer">×</span>
            </div>
        `;
        modal.onclick = () => modal.remove();
        document.body.appendChild(modal);
    }
</script>
@endsection