@extends('layouts.pegawai')

@section('content')
<style>
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
</style>

<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">{{ isset($pemakaian) ? 'Edit Pemakaian Mobil' : 'Buat Pemakaian Mobil Baru' }}</h4>
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

            <form action="{{ isset($pemakaian) ? route('pemakaian.simpanDetail', $pemakaian->id) : route('pemakaian.simpanDetail') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Informasi Mobil -->
                <div class="mb-4">
                    <h5 class="card-title">Informasi Mobil</h5>
                    <div class="alert alert-light border-left border-primary">
                        <p class="mb-0">
                            <strong>Mobil:</strong> {{ $mobil->no_polisi }}<br>
                            <strong>Merek:</strong> {{ $mobil->merek->nama_merek ?? '-' }}<br>
                            <strong>Tipe:</strong> {{ $mobil->tipe ?? '-' }}<br>
                            <strong>Penempatan:</strong> {{ $mobil->penempatan->nama_kantor ?? '-' }}
                        </p>
                    </div>
                </div>

                <!-- Detail Pemakaian -->
                <div class="mb-4">
                    <h5 class="card-title">Detail Pemakaian</h5>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="tujuan" class="form-label">Tujuan <span class="text-danger">*</span></label>
                            <input type="text" id="tujuan" name="tujuan" class="form-control @error('tujuan') is-invalid @enderror" 
                                value="{{ old('tujuan', $pemakaian->tujuan ?? '') }}" placeholder="Contoh: Malang" required>
                            @error('tujuan')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tanggal_mulai" class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" id="tanggal_mulai" name="tanggal_mulai" class="form-control @error('tanggal_mulai') is-invalid @enderror" 
                                value="{{ old('tanggal_mulai', isset($pemakaian) ? (is_string($pemakaian->tanggal_mulai) ? $pemakaian->tanggal_mulai : $pemakaian->tanggal_mulai->format('Y-m-d')) : '') }}" required>
                            @error('tanggal_mulai')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
                            <input type="date" id="tanggal_selesai" name="tanggal_selesai" class="form-control @error('tanggal_selesai') is-invalid @enderror" 
                                value="{{ old('tanggal_selesai', isset($pemakaian) && $pemakaian->tanggal_selesai ? (is_string($pemakaian->tanggal_selesai) ? $pemakaian->tanggal_selesai : $pemakaian->tanggal_selesai->format('Y-m-d')) : '') }}">
                            @error('tanggal_selesai')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="jarak_tempuh_km" class="form-label">Jarak Tempuh (km)</label>
                            <input type="number" id="jarak_tempuh_km" name="jarak_tempuh_km" class="form-control @error('jarak_tempuh_km') is-invalid @enderror" 
                                value="{{ old('jarak_tempuh_km', $pemakaian->jarak_tempuh_km ?? '') }}" step="0.01">
                            @error('jarak_tempuh_km')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="catatan" class="form-label">Catatan</label>
                            <textarea id="catatan" name="catatan" class="form-control @error('catatan') is-invalid @enderror" 
                                rows="2">{{ old('catatan', $pemakaian->catatan ?? '') }}</textarea>
                            @error('catatan')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>

                <!-- Bahan Bakar & Transmisi -->
                <div class="mb-4">
                    <h5 class="card-title">Bahan Bakar & Transmisi</h5>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="bahan_bakar" class="form-label">Jenis Bahan Bakar <span class="text-danger">*</span></label>
                            <select id="bahan_bakar" name="bahan_bakar" class="form-select @error('bahan_bakar') is-invalid @enderror" required>
                                <option value="">-- Pilih --</option>
                                @foreach(['Bensin','Solar','Listrik'] as $bb)
                                    <option value="{{ $bb }}" {{ old('bahan_bakar', isset($pemakaian) ? $pemakaian->detail->bahan_bakar ?? '' : ($mobil->detail->bahan_bakar ?? '')) == $bb ? 'selected' : '' }}>{{ $bb }}</option>
                                @endforeach
                            </select>
                            @error('bahan_bakar')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="bahan_bakar_liter" class="form-label">Liter Bahan Bakar</label>
                            <input type="number" id="bahan_bakar_liter" name="bahan_bakar_liter" class="form-control @error('bahan_bakar_liter') is-invalid @enderror" 
                                value="{{ old('bahan_bakar_liter', $pemakaian->bahan_bakar_liter ?? '') }}" step="0.01">
                            @error('bahan_bakar_liter')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="transmisi" class="form-label">Transmisi <span class="text-danger">*</span></label>
                            <select id="transmisi" name="transmisi" class="form-select @error('transmisi') is-invalid @enderror" required>
                                <option value="">-- Pilih --</option>
                                @foreach(['Manual','Automatic'] as $tr)
                                    <option value="{{ $tr }}" {{ old('transmisi', isset($pemakaian) ? $pemakaian->detail->transmisi ?? '' : ($mobil->detail->transmisi ?? '')) == $tr ? 'selected' : '' }}>{{ $tr }}</option>
                                @endforeach
                            </select>
                            @error('transmisi')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>

                <!-- Detail Kondisi Mobil -->
                <div class="mb-4">
                    <h5 class="card-title">Detail Kondisi Mobil</h5>
                    
                    @php
                        $detailFields = [
                            'kilometer' => 'Kilometer',
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
                                <label for="{{ $field }}" class="form-label">
                                    {{ $label }}
                                    @if(in_array($field, ['kilometer', 'depan', 'belakang', 'kanan', 'kiri']))
                                        <span class="text-danger">*</span>
                                    @endif
                                </label>
                                <input type="text" id="{{ $field }}" name="{{ $field }}" class="form-control @error($field) is-invalid @enderror" 
                                    value="{{ old($field, isset($pemakaian) ? $pemakaian->detail->{$field} ?? '' : ($mobil->detail->{$field} ?? '')) }}" 
                                    placeholder="Deskripsi kondisi..."
                                    {{ in_array($field, ['kilometer', 'depan', 'belakang', 'kanan', 'kiri']) ? 'required' : '' }}>
                                @error($field)<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        @endforeach
                    </div>

                    <!-- Kondisi Mobil Overall -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="kondisi" class="form-label">Kondisi Mobil Secara Keseluruhan</label>
                            <select id="kondisi" name="kondisi" class="form-select @error('kondisi') is-invalid @enderror">
                                <option value="">-- Pilih Kondisi --</option>
                                <option value="Sangat Baik" {{ old('kondisi', $pemakaian->detail->kondisi ?? '') === 'Sangat Baik' ? 'selected' : '' }}>Sangat Baik</option>
                                <option value="Baik" {{ old('kondisi', $pemakaian->detail->kondisi ?? '') === 'Baik' ? 'selected' : '' }}>Baik</option>
                                <option value="Cukup" {{ old('kondisi', $pemakaian->detail->kondisi ?? '') === 'Cukup' ? 'selected' : '' }}>Cukup</option>
                                <option value="Kurang" {{ old('kondisi', $pemakaian->detail->kondisi ?? '') === 'Kurang' ? 'selected' : '' }}>Kurang</option>
                            </select>
                            @error('kondisi')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>

                <!-- Foto Kondisi Mobil -->
                <div class="mb-4">
                    <h5 class="card-title"><i class="fas fa-camera"></i> Foto Kondisi Mobil</h5>
                    
                    @if(isset($pemakaian) && $pemakaian->fotoKondisiPemakaian && $pemakaian->fotoKondisiPemakaian->count() > 0)
                        <div class="mb-4">
                            <h6 class="text-secondary mb-3"><i class="fas fa-images"></i> Foto yang Sudah Ada:</h6>
                            <div id="fotoExisting">
                                @foreach($pemakaian->fotoKondisiPemakaian as $index => $f)
                                    <div class="foto-input row mb-3 p-3 border rounded bg-light">
                                        <div class="col-md-2 mb-2">
                                            <img src="{{ $f->foto_sebelum }}" class="img-thumbnail" style="height: 100px; object-fit: cover; cursor: pointer;" 
                                                onclick="lihatFoto('{{ $f->foto_sebelum }}')">
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <label class="form-label"><strong>Posisi:</strong> {{ $f->posisi }}</label>
                                            <input type="hidden" name="foto[{{ $index }}][posisi]" value="{{ $f->posisi }}">
                                            <input type="hidden" name="foto[{{ $index }}][id]" value="{{ $f->id }}">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <label class="form-label">Ganti Foto (Opsional)</label>
                                            <input type="file" name="foto[{{ $index }}][file]" class="form-control" accept="image/*" capture="environment">
                                            <small class="text-muted">Biarkan kosong jika tidak ingin mengubah</small>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <label class="form-label">&nbsp;</label>
                                            <div class="d-grid">
                                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="hapusFoto(this)">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            <h6 class="text-secondary mb-3 mt-4"><i class="fas fa-plus-circle"></i> Tambah Foto Baru:</h6>
                        </div>
                    @else
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-camera"></i> Tambahkan foto kondisi mobil (opsional)
                        </div>
                    @endif

                    <div id="fotoWrapper"></div>

                </div>

                <!-- Submit Button -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i> {{ isset($pemakaian) ? 'Update Pemakaian' : 'Simpan Pemakaian' }}
                    </button>
                    <a href="{{ route('pemakaian.daftar') }}" class="btn btn-secondary btn-lg">
                        <i class="fas fa-arrow-left"></i> Kembali
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

// ==========================
// GENERATE INPUT FOTO
// ==========================
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
                        required
                    >
                    <button
                        type="button"
                        class="btn btn-primary"
                        onclick="openCamera(${index})"
                    >
                        <i class="fas fa-camera"></i> Kamera
                    </button>
                </div>

                <small class="text-danger d-block mt-1">
                    📸 Wajib diambil langsung dari kamera
                </small>
            </div>

            <div class="col-md-4 mb-2">
                <label class="form-label">Posisi</label>
                <input
                    type="text"
                    name="foto[${index}][posisi]"
                    class="form-control"
                    value="${posisi.value}"
                    readonly
                >
            </div>
        </div>
    `);
});

// ==========================
// OPEN CAMERA
// ==========================
window.openCamera = function(index) {
    const input = document.querySelector(`input[name="foto[${index}][file]"]`);

    if (isMobile) {
        input.setAttribute("capture", "environment");
        input.click();
        return;
    }

    openWebCamera(index);
};

// ==========================
// MODAL CAMERA (DESKTOP)
// ==========================
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
                        <video id="video${index}" autoplay playsinline
                            style="width:100%;border-radius:8px;background:#000">
                        </video>
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

    document
        .getElementById(`cameraModal${index}`)
        .addEventListener("hidden.bs.modal", stopCamera);
}

// ==========================
// START CAMERA
// ==========================
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

// ==========================
// STOP CAMERA
// ==========================
function stopCamera() {
    if (cameraStream) {
        cameraStream.getTracks().forEach(t => t.stop());
        cameraStream = null;
    }
}

// ==========================
// CAPTURE FOTO
// ==========================
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
        bootstrap.Modal.getInstance(
            document.getElementById(`cameraModal${index}`)
        ).hide();

        showSuccess(input);
    }, "image/jpeg", 0.95);
};

// ==========================
// PREVIEW + SUCCESS
// ==========================
function showSuccess(input) {
    const parent = input.closest(".col-md-8");
    parent.querySelector(".preview-wrapper")?.remove();

    const imgURL = URL.createObjectURL(input.files[0]);

    const wrapper = document.createElement("div");
    wrapper.className = "preview-wrapper mt-2";

    wrapper.innerHTML = `
        <small class="text-success d-block mb-1">
            <i class="fas fa-check-circle"></i> Foto berhasil diambil
        </small>
        <img src="${imgURL}" class="preview-img">
    `;

    wrapper.querySelector("img").onclick = () => showImageModal(imgURL);
    parent.appendChild(wrapper);
}

// ==========================
// VIEWER
// ==========================
function showImageModal(src) {
    const modal = document.createElement("div");
    modal.style.cssText = `
        position:fixed;inset:0;
        background:rgba(0,0,0,.8);
        display:flex;align-items:center;justify-content:center;
        z-index:9999;
    `;

    modal.innerHTML = `
        <div style="position:relative">
            <img src="${src}" style="max-width:90vw;max-height:90vh;border-radius:12px">
            <span style="
                position:absolute;top:-10px;right:-10px;
                background:#fff;width:32px;height:32px;
                border-radius:50%;display:flex;
                align-items:center;justify-content:center;
                font-size:20px;cursor:pointer
            ">×</span>
        </div>
    `;

    modal.querySelector("span").onclick = () => modal.remove();
    modal.onclick = e => e.target === modal && modal.remove();

    document.body.appendChild(modal);
}

});
</script>


<script src="/js/pemakaian-notif.js"></script>

@endsection
