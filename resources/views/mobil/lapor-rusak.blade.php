@extends(
    auth()->user()->role === 'admin'
        ? 'layouts.admin'
        : 'layouts.pegawai'
)


@section('title', 'Lapor Kondisi Rusak')

@section('content')
<div class="admin-card">
    <div class="admin-toolbar mb-3">
        <h2 class="admin-title"><i class="fas fa-exclamation-triangle text-danger"></i> Lapor Kondisi Rusak</h2>
        <a href="{{ route('mobil.index') }}" class="btn btn-secondary ms-auto">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Validasi Gagal!</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('mobil.laporRusak') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="mobil_id" value="{{ $mobil->id }}">

        <!-- Info Mobil -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-car"></i> Informasi Mobil</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><strong>No Polisi</strong></label>
                        <input type="text" class="form-control" value="{{ $mobil->no_polisi }}" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><strong>Merek</strong></label>
                        <input type="text" class="form-control" value="{{ $mobil->merek->nama_merek ?? '-' }}" readonly>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><strong>Jenis</strong></label>
                        <input type="text" class="form-control" value="{{ $mobil->jenis->nama_jenis ?? '-' }}" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><strong>Penempatan</strong></label>
                        <input type="text" class="form-control" value="{{ $mobil->penempatan->nama_kantor ?? '-' }}" readonly>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Kondisi -->
        <div class="card mb-4">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="fas fa-check-circle"></i> Status Kondisi <span class="text-warning">*</span></h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label for="kondisi" class="form-label">Pilih Status Kondisi <span class="text-danger">*</span></label>
                        <select id="kondisi" name="kondisi" class="form-select @error('kondisi') is-invalid @enderror" required>
                            <option value="">-- Pilih Status --</option>
                            <option value="Rusak Ringan" {{ old('kondisi') === 'Rusak Ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                            <option value="Rusak Sedang" {{ old('kondisi') === 'Rusak Sedang' ? 'selected' : '' }}>Rusak Sedang</option>
                            <option value="Rusak Berat" {{ old('kondisi') === 'Rusak Berat' ? 'selected' : '' }}>Rusak Berat</option>
                        </select>
                        @error('kondisi')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Kondisi -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-tools"></i> Detail Kondisi Kerusakan</h5>
            </div>
            <div class="card-body">
                @php
                    $detailFields = [
                        'depan' => 'Kondisi Depan',
                        'belakang' => 'Kondisi Belakang',
                        'kanan' => 'Kondisi Kanan',
                        'kiri' => 'Kondisi Kiri',
                        'joksabuk' => 'Jok/Sabuk',
                        'acventilasi' => 'AC/Ventilasi',
                        'panelaudio' => 'Panel Audio',
                        'lampukabin' => 'Lampu Kabin',
                        'interior_bersih' => 'Interior Bersih',
                        'toolkitdongkrak' => 'Toolkit/Dongkrak'
                    ];
                @endphp

                <div class="row">
                    @foreach($detailFields as $field => $label)
                        <div class="col-md-6 mb-3">
                            <label for="{{ $field }}" class="form-label">{{ $label }}</label>
                            <textarea 
                                name="{{ $field }}" 
                                id="{{ $field }}" 
                                class="form-control @error($field) is-invalid @enderror" 
                                rows="2" 
                                placeholder="Deskripsi kerusakan...">{{ old($field) }}</textarea>
                            @error($field)<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Foto Dokumentasi -->
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-camera"></i> Foto Dokumentasi Kerusakan</h5>
            </div>
            <div class="card-body">
                <div id="fotoWrapper"></div>
                <small class="text-muted d-block mt-3">
                    <i class="fas fa-info-circle"></i> Foto akan diambil langsung dari kamera ponsel/desktop
                </small>
            </div>
        </div>

        <!-- Catatan Tambahan -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-sticky-note"></i> Catatan Tambahan</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="catatan" class="form-label">Catatan & Rekomendasi</label>
                        <textarea 
                            name="catatan" 
                            id="catatan" 
                            class="form-control @error('catatan') is-invalid @enderror" 
                            rows="4" 
                            placeholder="Informasi tambahan atau rekomendasi perbaikan...">{{ old('catatan') }}</textarea>
                        @error('catatan')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Buttons -->
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-danger btn-lg">
                <i class="fas fa-check"></i> Lapor Rusak
            </button>
            <a href="{{ route('mobil.index') }}" class="btn btn-secondary btn-lg">
                <i class="fas fa-times"></i> Batal
            </a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
    // Daftar posisi foto
    const posisiFoto = [
        { value: "depan", label: "Depan" },
        { value: "belakang", label: "Belakang" },
        { value: "kanan", label: "Kanan" },
        { value: "kiri", label: "Kiri" },
        { value: "joksabuk", label: "Jok / Sabuk" },
        { value: "acventilasi", label: "AC / Ventilasi" },
        { value: "panelaudio", label: "Panel Audio" },
        { value: "lampukabin", label: "Lampu Kabin" },
        { value: "interior_bersih", label: "Interior Bersih" },
        { value: "toolkitdongkrak", label: "Toolkit / Dongkrak" }
    ];

    const wrapper = document.getElementById("fotoWrapper");
    const isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
    let cameraStream = null;

    // Generate input foto untuk semua posisi
    posisiFoto.forEach((posisi, index) => {
        wrapper.insertAdjacentHTML("beforeend", `
            <div class="foto-input row mb-3 border rounded p-3">
                <div class="col-md-8 mb-2">
                    <label class="form-label">Foto ${posisi.label}</label>
                    <div class="input-group">
                        <input
                            type="file"
                            name="foto[${index}][file]"
                            class="form-control kamera-only"
                            accept="image/*"
                            capture="environment"
                        >
                        <button type="button" class="btn btn-primary" onclick="openCamera(${index})">
                            <i class="fas fa-camera"></i> Kamera
                        </button>
                    </div>
                    <small class="text-danger d-block mt-1">
                        📸 Ambil langsung dari kamera
                    </small>
                </div>
                <div class="col-md-4 mb-2">
                    <label class="form-label">Posisi</label>
                    <input type="text" name="foto[${index}][posisi]" class="form-control" value="${posisi.value}" readonly>
                </div>
            </div>
        `);
    });

    // Open Camera
    window.openCamera = function(index) {
        const input = document.querySelector(`input[name="foto[${index}][file]"]`);

        if (isMobile) {
            input.setAttribute("capture", "environment");
            input.click();
            return;
        }

        openWebCamera(index);
    };

    // Modal Camera untuk Desktop
    function openWebCamera(index) {
        document.getElementById(`cameraModal${index}`)?.remove();

        document.body.insertAdjacentHTML("beforeend", `
            <div class="modal fade" id="cameraModal${index}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Ambil Foto</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center">
                            <video id="video${index}" autoplay playsinline style="width:100%;border-radius:8px;background:#000"></video>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button class="btn btn-primary" onclick="capturePhoto(${index})">
                                <i class="fas fa-camera"></i> Ambil
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `);

        const modal = new bootstrap.Modal(document.getElementById(`cameraModal${index}`));
        modal.show();

        setTimeout(() => startCamera(index), 300);

        document.getElementById(`cameraModal${index}`).addEventListener("hidden.bs.modal", stopCamera);
    }

    // Start Camera
    async function startCamera(index) {
        try {
            cameraStream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: "environment" },
                audio: false
            });
            document.getElementById(`video${index}`).srcObject = cameraStream;
        } catch (err) {
            alert("❌ Kamera tidak bisa diakses");
            console.error(err);
        }
    }

    // Stop Camera
    function stopCamera() {
        if (cameraStream) {
            cameraStream.getTracks().forEach(t => t.stop());
            cameraStream = null;
        }
    }

    // Capture Photo
    window.capturePhoto = function(index) {
        const video = document.getElementById(`video${index}`);
        const canvas = document.createElement("canvas");

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext("2d").drawImage(video, 0, 0);

        canvas.toBlob(blob => {
            const file = new File([blob], `foto_${Date.now()}.jpg`, { type: "image/jpeg" });
            const input = document.querySelector(`input[name="foto[${index}][file]"]`);

            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;

            stopCamera();
            bootstrap.Modal.getInstance(document.getElementById(`cameraModal${index}`)).hide();

            showSuccess(input);
        }, "image/jpeg", 0.95);
    };

    // Preview + Success
    function showSuccess(input) {
        const parent = input.closest(".input-group");
        parent.parentElement.querySelector(".preview-wrapper")?.remove();

        const imgURL = URL.createObjectURL(input.files[0]);
        const wrapper = document.createElement("div");
        wrapper.className = "preview-wrapper mt-2";

        wrapper.innerHTML = `
            <small class="text-success d-block mb-1">
                <i class="fas fa-check-circle"></i> Foto berhasil diambil
            </small>
            <img src="${imgURL}" class="preview-img" style="max-width:160px;border-radius:8px;border:2px solid #198754;cursor:pointer;">
        `;

        wrapper.querySelector("img").onclick = () => showImageModal(imgURL);
        parent.parentElement.appendChild(wrapper);
    }

    // Image Viewer Modal
    function showImageModal(src) {
        const modal = document.createElement("div");
        modal.style.cssText = `position:fixed;inset:0;background:rgba(0,0,0,.8);display:flex;align-items:center;justify-content:center;z-index:9999;`;

        modal.innerHTML = `
            <div style="position:relative">
                <img src="${src}" style="max-width:90vw;max-height:90vh;border-radius:12px">
                <span style="position:absolute;top:-10px;right:-10px;background:#fff;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:20px;cursor:pointer">×</span>
            </div>
        `;

        modal.querySelector("span").onclick = () => modal.remove();
        modal.onclick = e => e.target === modal && modal.remove();

        document.body.appendChild(modal);
    }
});
</script>

@endsection