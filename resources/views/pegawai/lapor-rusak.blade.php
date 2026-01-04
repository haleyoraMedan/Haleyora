@extends('layouts.pegawai')

@section('title', 'Lapor Kondisi Rusak')

@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-danger text-white">
            <div class="d-flex align-items-center justify-content-between">
                <h4 class="mb-0">
                    <i class="fas fa-exclamation-triangle"></i> Lapor Kondisi Rusak
                </h4>
                <a href="{{ route('pegawai.mobilRusak') }}" class="btn btn-sm btn-light">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
        <div class="card-body">
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
                    </div>
                </div>

                <!-- Status Kondisi -->
                <div class="card mb-4">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">Status Kondisi <span class="text-warning">*</span></h5>
                    </div>
                    <div class="card-body">
                        <select name="kondisi" class="form-select form-select-lg @error('kondisi') is-invalid @enderror" required>
                            <option value="">-- Pilih Status --</option>
                            <option value="Rusak Ringan" {{ old('kondisi') === 'Rusak Ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                            <option value="Rusak Sedang" {{ old('kondisi') === 'Rusak Sedang' ? 'selected' : '' }}>Rusak Sedang</option>
                            <option value="Rusak Berat" {{ old('kondisi') === 'Rusak Berat' ? 'selected' : '' }}>Rusak Berat</option>
                        </select>
                        @error('kondisi')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>
                </div>

                <!-- Detail Kondisi -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-tools"></i> Detail Kondisi</h5>
                    </div>
                    <div class="card-body">
                        @php
                            $detailFields = [
                                'depan' => 'Depan',
                                'belakang' => 'Belakang',
                                'kanan' => 'Kanan',
                                'kiri' => 'Kiri'
                            ];
                        @endphp

                        @foreach($detailFields as $field => $label)
                            <div class="mb-3">
                                <label for="{{ $field }}" class="form-label">{{ $label }}</label>
                                <textarea name="{{ $field }}" id="{{ $field }}" class="form-control" rows="2" placeholder="Deskripsi kerusakan...">{{ old($field) }}</textarea>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Foto Dokumentasi -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-camera"></i> Foto Dokumentasi</h5>
                    </div>
                    <div class="card-body">
                        <div id="fotoWrapper"></div>
                        <small class="text-muted d-block mt-3">
                            <i class="fas fa-info-circle"></i> Ambil foto dari kamera untuk dokumentasi kerusakan
                        </small>
                    </div>
                </div>

                <!-- Catatan -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-sticky-note"></i> Catatan Tambahan</h5>
                    </div>
                    <div class="card-body">
                        <textarea name="catatan" class="form-control" rows="4" placeholder="Catatan atau rekomendasi perbaikan...">{{ old('catatan') }}</textarea>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-danger btn-lg">
                        <i class="fas fa-check"></i> Lapor Rusak
                    </button>
                    <a href="{{ route('pegawai.mobilRusak') }}" class="btn btn-secondary btn-lg">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
    const posisiFoto = [
        { value: "depan", label: "Depan" },
        { value: "belakang", label: "Belakang" },
        { value: "kanan", label: "Kanan" },
        { value: "kiri", label: "Kiri" }
    ];

    const wrapper = document.getElementById("fotoWrapper");
    const isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
    let cameraStream = null;

    // Generate input foto
    posisiFoto.forEach((posisi, index) => {
        wrapper.insertAdjacentHTML("beforeend", `
            <div class="row mb-3 p-3 border rounded bg-light">
                <div class="col-md-8">
                    <label class="form-label">Foto ${posisi.label}</label>
                    <div class="input-group">
                        <input type="file" name="foto[${index}][file]" class="form-control" accept="image/*" capture="environment">
                        <button type="button" class="btn btn-primary" onclick="openCamera(${index})">
                            <i class="fas fa-camera"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-4">
                    <input type="hidden" name="foto[${index}][posisi]" value="${posisi.value}">
                </div>
            </div>
        `);
    });

    window.openCamera = function(index) {
        const input = document.querySelector(`input[name="foto[${index}][file]"]`);
        if (isMobile) {
            input.setAttribute("capture", "environment");
            input.click();
            return;
        }
        openWebCamera(index);
    };

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

    async function startCamera(index) {
        try {
            cameraStream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: "environment" },
                audio: false
            });
            document.getElementById(`video${index}`).srcObject = cameraStream;
        } catch (err) {
            alert("❌ Kamera tidak bisa diakses");
        }
    }

    function stopCamera() {
        if (cameraStream) {
            cameraStream.getTracks().forEach(t => t.stop());
            cameraStream = null;
        }
    }

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

    function showSuccess(input) {
        const parent = input.closest(".input-group");
        const imgURL = URL.createObjectURL(input.files[0]);
        const wrapper = document.createElement("div");
        wrapper.className = "mt-2";
        wrapper.innerHTML = `<small class="text-success"><i class="fas fa-check-circle"></i> Foto berhasil diambil</small>`;
        parent.after(wrapper);
    }
});
</script>

@endsection
