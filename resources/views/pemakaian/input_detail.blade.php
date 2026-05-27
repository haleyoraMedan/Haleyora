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

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ isset($pemakaian) ? route('pemakaian.simpanDetail', $pemakaian->id) : route('pemakaian.simpanDetail') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- InformasiMobil -->
                <div class="mb-4">
                    <h5 class="card-title">Informasi Mobil</h5>
                    <div class="alert alert-light border-left border-primary">
                        <p class="mb-0">
                            <strong>Mobil:</strong> {{ $mobil->no_polisi }}<br>
                            <strong>Brand:</strong> {{ $mobil->merek->nama_merek ?? '-' }}<br>
                            <strong>Tipe:</strong> {{ $mobil->jenis->nama_jenis ?? $mobil->tipe ?? '-' }}<br>
                            <strong>Penempatan:</strong> {{ $mobil->penempatan->nama_kantor ?? '-' }}
                        </p>
                        <!-- Foto SIM (wajib diambil dari kamera) -->
                        <div class="mt-3">
                            <label class="form-label">Foto SIM (wajib diambil dari kamera) <span class="text-danger">*</span></label>

                            @if(isset($pemakaian) && !empty($pemakaian->sim_foto))
                                <div>
                                    <img src="{{ $pemakaian->sim_foto }}" id="preview-sim-existing" class="img-thumbnail" style="height:100px; object-fit:cover; cursor:pointer;" onclick="showImageModal('{{ $pemakaian->sim_foto }}')">
                                    <div class="mt-2">
                                            <input type="file" name="sim_foto" id="sim_foto_input" accept="image/*" capture="environment" style="opacity:0;position:absolute;left:-9999px;width:1px;height:1px;" @if(!isset($pemakaian) || empty($pemakaian->sim_foto)) required @endif>
                                            <button type="button" class="btn btn-outline-secondary sim-foto-btn">
                                                <i class="fas fa-camera"></i> Ganti Foto SIM
                                            </button>
                                    </div>
                                </div>
                            @else
                                <div class="input-group">
                                    <input type="file" name="sim_foto" id="sim_foto_input" accept="image/*" capture="environment" style="opacity:0;position:absolute;left:-9999px;width:1px;height:1px;" @if(!isset($pemakaian) || empty($pemakaian->sim_foto)) required @endif>
                                    <button type="button" class="btn btn-outline-secondary sim-foto-btn">
                                        <i class="fas fa-camera"></i> Ambil Foto SIM
                                    </button>
                                </div>
                                <div id="simPreview"></div>
                            @endif

                            <small class="text-danger d-block mt-1">📸 Foto SIM harus diambil langsung dari kamera (tidak boleh memilih file).</small>
                        </div>
                    </div>
                </div>

                <!-- Detail Pemakaian -->
                <div class="mb-4">
                    <h5 class="card-title">Detail Pemakaian</h5>
                    
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="tujuan" class="form-label">Tujuan <span class="text-danger">*</span></label>
                            <input type="text" id="tujuan" name="tujuan" class="form-control @error('tujuan') is-invalid @enderror" 
                                value="{{ old('tujuan', $pemakaian->tujuan ?? '') }}" placeholder="Contoh: Malang" required>
                            @error('tujuan')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="kondisi_sebelum_setelah" class="form-label">Kondisi Mobil <span class="text-danger">*</span></label>
                            <select id="kondisi_sebelum_setelah" name="kondisi_sebelum_setelah" class="form-select @error('kondisi_sebelum_setelah') is-invalid @enderror" required>
                                <option value="">Pilih kondisi</option>
                                <option value="sebelum pemakaian" {{ old('kondisi_sebelum_setelah', $pemakaian->kondisi_sebelum_setelah ?? '') === 'sebelum pemakaian' ? 'selected' : '' }}>Sebelum Pemakaian</option>
                                <option value="sesudah pemakaian" {{ old('kondisi_sebelum_setelah', $pemakaian->kondisi_sebelum_setelah ?? '') === 'sesudah pemakaian' ? 'selected' : '' }}>Sesudah Pemakaian</option>
                            </select>
                            @error('kondisi_sebelum_setelah')<span class="invalid-feedback">{{ $message }}</span>@enderror
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
                            <label for="tanggal_selesai" class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" id="tanggal_selesai" name="tanggal_selesai" class="form-control @error('tanggal_selesai') is-invalid @enderror" 
                                value="{{ old('tanggal_selesai', isset($pemakaian) && $pemakaian->tanggal_selesai ? (is_string($pemakaian->tanggal_selesai) ? $pemakaian->tanggal_selesai : $pemakaian->tanggal_selesai->format('Y-m-d')) : '') }}" required>
                            @error('tanggal_selesai')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="jarak_tempuh_km" class="form-label">Jarak Tempuh (km)</label>
                            <input type="number" id="jarak_tempuh_km" name="jarak_tempuh_km" class="form-control @error('jarak_tempuh_km') is-invalid @enderror" 
                                value="{{ old('jarak_tempuh_km', $pemakaian->jarak_tempuh_km ?? '') }}" step="0.01" placeholder="Opsional">
                            @error('jarak_tempuh_km')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="catatan" class="form-label">Keluhan</label>
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
                            <label for="bahan_bakar" class="form-label">Jenis Bahan Bakar</label>
                            <select id="bahan_bakar" name="bahan_bakar" class="form-select @error('bahan_bakar') is-invalid @enderror">
                                <option value="">-- Pilih --</option>
                                @foreach(['Bensin','Solar','Listrik'] as $bb)
                                    <option value="{{ $bb }}" {{ old('bahan_bakar', isset($pemakaian) ? $pemakaian->detail->bahan_bakar ?? '' : ($mobil->detail->bahan_bakar ?? '')) == $bb ? 'selected' : '' }}>{{ $bb }}</option>
                                @endforeach
                            </select>
                            @error('bahan_bakar')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="bahan_bakar_liter" class="form-label">Sisa Bahan Bakar (bar)</label>
                            <input type="number" id="bahan_bakar_liter" name="bahan_bakar_liter" class="form-control @error('bahan_bakar_liter') is-invalid @enderror" 
                                value="{{ old('bahan_bakar_liter', $pemakaian->bahan_bakar_liter ?? '') }}" step="0.01" required>
                            @error('bahan_bakar_liter')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="transmisi" class="form-label">Transmisi </span></label>
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

                <!-- Detail Kondisi Mobil (always rendered; JS will toggle visibility realtime) -->
                <div id="conditionWrapper" class="mb-4 @if($is_restricted ?? false) d-none @endif">
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

                    <div class="row" id="conditionFields">
                        @foreach($detailFields as $field => $label)
                            <div class="col-md-6 mb-3">
                                <label for="{{ $field }}" class="form-label">
                                    {{ $label }}
                                    <span class="text-danger" data-required-for="{{ $field }}">@if(in_array($field, ['kilometer','depan','belakang','kanan','kiri']))*@endif</span>
                                </label>
                                <input type="text" id="{{ $field }}" name="{{ $field }}" class="form-control @error($field) is-invalid @enderror" 
                                    value="{{ old($field, isset($pemakaian) ? ($pemakaian->detail->{$field} ?? '') : ( $field === 'kilometer' ? '' : ($mobil->detail->{$field} ?? '') )) }}" 
                                    placeholder="Deskripsi kondisi...">
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
                                <option value="Rusak" {{ old('kondisi', $pemakaian->detail->kondisi ?? '') === 'Rusak' ? 'selected' : '' }}>Rusak</option>
                            </select>
                            @error('kondisi')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>

                <!-- Alert for restricted period (hidden when not restricted) -->
                <div id="conditionAlert" class="mb-4 @if(!($is_restricted ?? false)) d-none @endif">
                    <div class="alert alert-warning">
                        Isi data penuh pada pukul 06:00–09:59 dan 16:30–19:59. Pada jam lain isi ringkas; nilai kondisi akan diset otomatis ke '-'.
                        <div class="small text-muted mt-2">Waktu sekarang (WIB): <span id="jktClock">{{ $current_time_jkt ?? '' }}</span></div>
                    </div>
                    {{-- hidden inputs will be added dynamically by JS when restricted; include server-side ones initially if restricted --}}
                    @if($is_restricted ?? false)
                        @foreach(['depan','belakang','kanan','kiri','joksabuk','acventilasi','panelaudio','lampukabin','interior_bersih','toolkitdongkrak','kondisi'] as $hf)
                            <input type="hidden" class="condition-hidden" name="{{ $hf }}" value="-">
                        @endforeach
                    @endif
                </div>


{{-- <div class="mb-4">  
                    <h5 class="card-title"><i class="fas fa-camera"></i> Foto Kondisi Mobil</h5>  @if(isset($pemakaian) && $pemakaian->fotoKondisiPemakaian && $pemakaian->fotoKondisiPemakaian->count() > 0)  
                    <div class="mb-4">  
                        <h6 class="tet-secondary mb-3"><i class="fas fa-images"></i> Foto yang Sudah Ada:</h6>  
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

            </div> --}}

                <!-- Foto Kondisi Mobil -->
                
               <div class="mb-4">
    <h5 class="card-title">
        <i class="fas fa-camera"></i> Foto Kondisi Mobil
    </h5>

    @if(isset($pemakaian) && $pemakaian->fotoKondisiPemakaian && $pemakaian->fotoKondisiPemakaian->count() > 0)
        <div class="mb-4">
            <h6 class="text-secondary mb-3">
                <i class="fas fa-images"></i> Foto yang Sudah Ada
            </h6>

            <div id="fotoExisting">
                @foreach($pemakaian->fotoKondisiPemakaian as $index => $f)
                    <div class="foto-input row mb-3 p-3 border rounded bg-light">

                        <!-- FOTO LAMA -->
                        <div class="col-md-2 mb-2">
                            <img src="{{ $f->foto_sebelum }}"
                                 class="img-thumbnail"
                                 style="height:100px;object-fit:cover;cursor:pointer;">
                        </div>

                        <!-- INFO -->
                        <div class="col-md-3 mb-2">
                            <label class="form-label">
                                <strong>Posisi:</strong> {{ $f->posisi }}
                            </label>

                            <input type="hidden" name="foto[{{ $index }}][id]" value="{{ $f->id }}">
                            <input type="hidden" name="foto[{{ $index }}][posisi]" value="{{ $f->posisi }}">
                        </div>

                        <!-- GANTI FOTO -->
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Ganti Foto (Opsional)</label>

                            <div class="input-group">
                                <input type="file"
                                       name="foto[{{ $index }}][file]"
                                       class="form-control d-none kamera-only"
                                       accept="image/*"
                                       capture="environment"
                                       onchange="previewExisting(this)"
                                       data-preview="preview-existing-{{ $index }}">

                                <button type="button" class="btn btn-primary foto-camera-btn" data-index="{{ $index }}">
                                    <i class="fas fa-camera"></i> Kamera
                                </button>
                            </div>

                            <small class="text-danger d-block mt-1">📸 Jika mengganti foto, wajib diambil dari kamera</small>

                            <img id="preview-existing-{{ $index }}"
                                 class="img-thumbnail mt-2 d-none"
                                 style="height:100px;object-fit:cover;">
                        </div>

                        <!-- HAPUS -->
                        <div class="col-md-3 mb-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="button"
                                        class="btn btn-sm btn-outline-danger"
                                        onclick="hapusFoto(this)">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </div>
                        </div>

                    </div>
                @endforeach
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
/* ===============================
   PREVIEW FOTO EXISTING
================================ */
function previewExisting(input) {
    if (!input.files || !input.files[0]) return;

    const img = document.getElementById(input.dataset.preview);
    img.src = URL.createObjectURL(input.files[0]);
    img.classList.remove('d-none');
}
</script>
<script>
  const IS_EDIT = {{ isset($pemakaian) ? 'true' : 'false' }};
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
const isMobile = /Mobi|Android|iPhone|iPad|iPod/i.test(navigator.userAgent) || (navigator.maxTouchPoints && navigator.maxTouchPoints > 0) || ('ontouchstart' in window);
let cameraStream = null;

// ==========================
// GENERATE INPUT FOTO
// ==========================
if (!IS_EDIT) {
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
                                
                            required
                        >
                        <button
                            type="button"
                            class="btn btn-primary foto-camera-btn"
                            data-index="${index}"
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
}

// ==========================
// OPEN CAMERA
// ==========================
window.openCamera = function(index) {
    const input = (String(index) === 'SIM')
        ? document.querySelector('input[name="sim_foto"]')
        : document.querySelector(`input[name="foto[${index}][file]"]`);

    if (!input) {
        alert('Input foto tidak ditemukan.');
        return;
    }

    // Mobile browsers sometimes ignore programmatic click on inputs with display:none.
    // To improve reliability, temporarily make the input focusable/visible but hidden (opacity 0)
    // before calling click(), then restore its original display.
    if (isMobile) {
        input.setAttribute("capture", "environment");

        const orig = {
            display: input.style.display || '',
            visibility: input.style.visibility || '',
            opacity: input.style.opacity || '',
            position: input.style.position || '',
            left: input.style.left || '',
            width: input.style.width || '',
            height: input.style.height || ''
        };

        input.style.display = 'block';
        input.style.visibility = 'visible';
        input.style.opacity = '0';
        input.style.position = 'absolute';
        input.style.left = '-9999px';
        input.style.width = '1px';
        input.style.height = '1px';

        // Small timeout to ensure rendering on some mobile browsers
        setTimeout(() => {
            input.click();

            // restore styles after short delay
            setTimeout(() => {
                input.style.display = orig.display;
                input.style.visibility = orig.visibility;
                input.style.opacity = orig.opacity;
                input.style.position = orig.position;
                input.style.left = orig.left;
                input.style.width = orig.width;
                input.style.height = orig.height;
            }, 500);
        }, 50);

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
                        <button class="btn btn-primary" onclick="capturePhoto('${index}')">
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
    // If getUserMedia is not supported, fallback to file input click
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        // close modal if present and fallback to file input
        try {
            const modalEl = document.getElementById(`cameraModal${index}`);
            if (modalEl) bootstrap.Modal.getInstance(modalEl)?.hide();
        } catch (e) {}

        const input = String(index) === 'SIM' ? document.querySelector('input[name="sim_foto"]') : document.querySelector(`input[name="foto[${index}][file]"]`);
        if (input) {
            // make sure input is focusable and visible offscreen (already handled elsewhere)
            setTimeout(() => input.click(), 200);
            return;
        }
        alert('Kamera tidak tersedia di browser ini.');
        return;
    }

    try {
        cameraStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" }, audio: false });
        document.getElementById(`video${index}`).srcObject = cameraStream;
    } catch (err) {
        console.error('getUserMedia error:', err);
        // If permission denied or no camera, fallback to file input picker
        try {
            const modalEl = document.getElementById(`cameraModal${index}`);
            if (modalEl) bootstrap.Modal.getInstance(modalEl)?.hide();
        } catch (e) {}

        const input = String(index) === 'SIM' ? document.querySelector('input[name="sim_foto"]') : document.querySelector(`input[name="foto[${index}][file]"]`);
        if (input) {
            setTimeout(() => input.click(), 200);
            return;
        }
        alert("❌ Kamera tidak bisa diakses");
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
        let input;
        if (String(index) === 'SIM') {
            input = document.querySelector('input[name="sim_foto"]');
        } else {
            input = document.querySelector(`input[name="foto[${index}][file]"]`);
        }

        if (!input) {
            alert('Input foto tidak ditemukan.');
            return;
        }

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
    // Special handling for SIM input preview
    if (input.name === 'sim_foto') {
        const imgURL = URL.createObjectURL(input.files[0]);
        const existing = document.getElementById('preview-sim-existing');
        if (existing) {
            existing.src = imgURL;
        } else {
            const container = document.getElementById('simPreview');
            if (container) container.innerHTML = "\n                <div class=\"preview-wrapper mt-2\">\n                    <small class=\"text-success d-block mb-1\">\n                        <i class=\"fas fa-check-circle\"></i> Foto SIM berhasil diambil\n                    </small>\n                    <img src=\"" + imgURL + "\" class=\"preview-img\" id=\"preview-sim-new\">\n                </div>\n            ";
            const imgEl = document.getElementById('preview-sim-new');
            if (imgEl) imgEl.onclick = () => showImageModal(imgURL);
        }
        return;
    }

    const parent = input.closest('.col-md-8, .col-md-4, .col-md-3') || input.parentElement;
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

    // Ensure file inputs (mobile capture) trigger preview when user takes photo
    document.body.addEventListener('change', function(e) {
        const el = e.target;
        if (!el) return;
        if (el.tagName && el.tagName.toLowerCase() === 'input' && el.type === 'file') {
            if (el.name === 'sim_foto' || (el.name && el.name.indexOf('foto[') === 0)) {
                if (el.files && el.files[0]) {
                    showSuccess(el);
                }
            }
        }
    });

    // Attach click handlers for SIM buttons (avoid inline onclick quoting issues)
    document.querySelectorAll('.sim-foto-btn').forEach(btn => {
        btn.addEventListener('click', function (ev) {
            ev.preventDefault();
            openCamera('SIM');
        });
    });

    // Delegated handler for foto camera buttons (static + dynamically generated)
    document.body.addEventListener('click', function(e) {
        const btn = e.target.closest && e.target.closest('.foto-camera-btn');
        if (!btn) return;
        e.preventDefault();
        const idx = btn.getAttribute('data-index');
        if (idx !== null) openCamera(idx);
    });

});
</script>


<script src="/js/pemakaian-notif.js"></script>

<script>
// Live Jakarta clock + UI toggling for condition inputs
(function(){
    const clockEl = document.getElementById('jktClock');
    const wrapper = document.getElementById('conditionWrapper');
    const alertEl = document.getElementById('conditionAlert');
    const hiddenClass = 'condition-hidden';
    const hiddenNames = ['depan','belakang','kanan','kiri','joksabuk','acventilasi','panelaudio','lampukabin','interior_bersih','toolkitdongkrak','kondisi'];

    function getJktParts(){
        const fmt = new Intl.DateTimeFormat('en-US', { timeZone: 'Asia/Jakarta', hour12: false, hour: '2-digit', minute: '2-digit', second: '2-digit' });
        const parts = fmt.formatToParts(new Date());
        const hour = parseInt(parts.find(p=>p.type==='hour').value,10);
        const minute = parts.find(p=>p.type==='minute').value;
        const second = parts.find(p=>p.type==='second').value;
        return {hour, minute, second};
    }

    function ensureHiddenInputs(){
        hiddenNames.forEach(name => {
            if (!document.querySelector(`input[name="${name}"].${hiddenClass}`)){
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = name;
                inp.value = '-';
                inp.className = hiddenClass;
                alertEl.appendChild(inp);
            }
        });
    }

    function removeHiddenInputs(){
        document.querySelectorAll(`input.${hiddenClass}`).forEach(n=>n.remove());
    }

    function updateUI(){
        const {hour, minute, second} = getJktParts();
        if (clockEl) clockEl.textContent = `${String(hour).padStart(2,'0')}:${minute}:${second}`;

        // minutes since midnight
        const m = hour * 60 + parseInt(minute, 10);
        // Full input windows: 06:00-09:59 (360-599) and 16:30-19:59 (990-1199)
        const isFull = (m >= 360 && m < 600) || (m >= 990 && m < 1200);

        if (!isFull){
            // ringkas: hide full inputs, show alert and add hidden defaults
            wrapper?.classList.add('d-none');
            alertEl?.classList.remove('d-none');
            ensureHiddenInputs();
        } else {
            // full: show inputs
            wrapper?.classList.remove('d-none');
            alertEl?.classList.add('d-none');
            removeHiddenInputs();
        }

        // set required attributes for fields during full-input windows
        const requiredFields = ['kilometer','depan','belakang','kanan','kiri'];
        requiredFields.forEach(fn => {
            const el = document.getElementById(fn);
            if (!el) return;
            if (isFull) el.setAttribute('required','required'); else el.removeAttribute('required');
        });
    }

    // initial run
    updateUI();
    // update every second
    setInterval(updateUI, 1000);

})();
</script>

@endsection
