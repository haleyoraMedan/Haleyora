@extends('layouts.app')

@section('content')
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
                            <strong>Tipe:</strong> {{ $mobil->tipe ?? '-' }}
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

                    <div id="fotoWrapper">
                        <div class="foto-input row mb-3">
                            <div class="col-md-8 mb-2">
                                <label class="form-label">Pilih Foto</label>
                                <div class="input-group">
                                    <input type="file" name="foto[0][file]" class="form-control fotoInput" accept="image/*" capture="environment">
                                    <button class="btn btn-outline-secondary" type="button" onclick="toggleCameraMode(0)" title="Ambil foto dari kamera">
                                        <i class="fas fa-camera"></i> Kamera
                                    </button>
                                </div>
                                <small class="text-muted d-block mt-1">Pilih dari galeri atau ambil langsung dari kamera</small>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Posisi Foto</label>
                                <select name="foto[0][posisi]" class="form-select">
                                    <option value="">-- Pilih Posisi --</option>
                                    <option value="depan">Depan</option>
                                    <option value="belakang">Belakang</option>
                                    <option value="kanan">Kanan</option>
                                    <option value="kiri">Kiri</option>
                                    <option value="joksabuk">Jok/Sabuk</option>
                                    <option value="acventilasi">AC/Ventilasi</option>
                                    <option value="panelaudio">Panel Audio</option>
                                    <option value="lampukabin">Lampu Kabin</option>
                                    <option value="interior_bersih">Interior Bersih</option>
                                    <option value="toolkitdongkrak">Toolkit/Dongkrak</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-secondary mb-3" onclick="tambahFotoInput()">
                        <i class="fas fa-plus"></i> Tambah Foto Lagi
                    </button>
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
let fotoIndex = {{ isset($pemakaian) ? $pemakaian->fotoKondisiPemakaian->count() : 1 }};
let captureMode = {};
let cameraStream = null;
let cameraModal = null;

// Detect device capabilities
const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
const hasCamera = navigator.mediaDevices && navigator.mediaDevices.getUserMedia;

function tambahFotoInput() {
    const wrapper = document.getElementById('fotoWrapper');
    const div = document.createElement('div');
    div.classList.add('foto-input', 'row', 'mb-3');
    div.innerHTML = `
        <div class="col-md-8 mb-2">
            <label class="form-label">Pilih Foto</label>
            <div class="input-group">
                <input type="file" name="foto[${fotoIndex}][file]" class="form-control fotoInput" accept="image/*" capture="environment">
                <button class="btn btn-outline-secondary" type="button" onclick="toggleCameraMode(${fotoIndex})" title="Ambil foto dari kamera" id="cameraBtn${fotoIndex}">
                    <i class="fas fa-camera"></i> Kamera
                </button>
            </div>
            <small class="text-muted d-block mt-1">Pilih dari galeri atau ambil langsung dari kamera</small>
        </div>
        <div class="col-md-4 mb-2">
            <label class="form-label">Posisi Foto</label>
            <select name="foto[${fotoIndex}][posisi]" class="form-select">
                <option value="">-- Pilih Posisi --</option>
                <option value="depan">Depan</option>
                <option value="belakang">Belakang</option>
                <option value="kanan">Kanan</option>
                <option value="kiri">Kiri</option>
                <option value="joksabuk">Jok/Sabuk</option>
                <option value="acventilasi">AC/Ventilasi</option>
                <option value="panelaudio">Panel Audio</option>
                <option value="lampukabin">Lampu Kabin</option>
                <option value="interior_bersih">Interior Bersih</option>
                <option value="toolkitdongkrak">Toolkit/Dongkrak</option>
            </select>
        </div>
    `;
    wrapper.appendChild(div);
    fotoIndex++;
}

function toggleCameraMode(index) {
    if (hasCamera && isMobile) {
        // Gunakan native camera pada mobile
        openCameraApp(index);
    } else if (hasCamera) {
        // Gunakan WebRTC untuk desktop/tablet
        openWebCamera(index);
    } else {
        alert('Perangkat Anda tidak mendukung akses kamera. Silakan gunakan galeri foto.');
    }
}

function openCameraApp(index) {
    // Membuka native camera app dengan input file
    const input = document.querySelector(`input[name="foto[${index}][file]"]`);
    if (input) {
        input.setAttribute('capture', 'environment');
        input.click();
    }
}

function openWebCamera(index) {
    // Buka modal dengan preview kamera WebRTC
    let modalHTML = `
        <div class="modal fade" id="cameraModal${index}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Ambil Foto Dari Kamera</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <video id="videoFeed${index}" width="100%" height="auto" style="max-height: 400px; background: #000; border-radius: 8px;"></video>
                        <p class="text-muted mt-3 mb-0">
                            <small>Pastikan pencahayaan cukup untuk hasil foto yang baik</small>
                        </p>
                    </div>
                    <div class="modal-footer gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" onclick="capturePhoto(${index})">
                            <i class="fas fa-camera"></i> Ambil Foto
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Tambahkan modal ke body jika belum ada
    if (!document.getElementById(`cameraModal${index}`)) {
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }
    
    // Buka modal
    const modal = new bootstrap.Modal(document.getElementById(`cameraModal${index}`));
    modal.show();
    
    // Akses kamera
    setTimeout(() => {
        startCamera(index);
    }, 300);
    
    // Stop kamera saat modal ditutup
    document.getElementById(`cameraModal${index}`).addEventListener('hidden.bs.modal', () => {
        stopCamera(index);
    });
}

async function startCamera(index) {
    try {
        const constraints = {
            video: { 
                facingMode: { ideal: 'environment' }, // Prefer back camera
                width: { ideal: 1280 },
                height: { ideal: 720 }
            },
            audio: false
        };
        
        cameraStream = await navigator.mediaDevices.getUserMedia(constraints);
        const video = document.getElementById(`videoFeed${index}`);
        
        if (video) {
            video.srcObject = cameraStream;
            video.onloadedmetadata = () => {
                video.play();
            };
        }
    } catch (err) {
        console.error('Error accessing camera:', err);
        
        // Fallback: gunakan file input dengan capture
        alert('Tidak bisa mengakses kamera. Silakan gunakan galeri foto.');
        const modal = bootstrap.Modal.getInstance(document.getElementById(`cameraModal${index}`));
        if (modal) modal.hide();
        
        const input = document.querySelector(`input[name="foto[${index}][file]"]`);
        if (input) {
            input.setAttribute('capture', 'environment');
            input.click();
        }
    }
}

function stopCamera(index) {
    if (cameraStream) {
        cameraStream.getTracks().forEach(track => track.stop());
        cameraStream = null;
    }
}

function capturePhoto(index) {
    const video = document.getElementById(`videoFeed${index}`);
    const canvas = document.createElement('canvas');
    
    if (video && video.videoWidth > 0) {
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0);
        
        // Convert canvas to blob dan set ke input
        canvas.toBlob(blob => {
            const file = new File([blob], `foto_${index}_${Date.now()}.jpg`, { type: 'image/jpeg' });
            const input = document.querySelector(`input[name="foto[${index}][file]"]`);
            
            if (input) {
                // Create a DataTransfer object to set files
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                input.files = dataTransfer.files;
                
                // Trigger change event
                input.dispatchEvent(new Event('change', { bubbles: true }));
                
                // Show preview
                showPhotoPreview(index, canvas.toDataURL('image/jpeg'));
            }
            
            // Close modal
            stopCamera(index);
            const modal = bootstrap.Modal.getInstance(document.getElementById(`cameraModal${index}`));
            if (modal) modal.hide();
        }, 'image/jpeg', 0.95);
    } else {
        alert('Gagal mengambil foto. Silakan coba lagi.');
    }
}

function showPhotoPreview(index, dataUrl) {
    // Show success message
    const inputGroup = document.querySelector(`input[name="foto[${index}][file]"]`).closest('.input-group');
    if (!inputGroup.querySelector('.success-message')) {
        const successMsg = document.createElement('small');
        successMsg.className = 'text-success d-block mt-1 success-message';
        successMsg.innerHTML = '<i class="fas fa-check-circle"></i> Foto berhasil diambil!';
        inputGroup.parentElement.insertAdjacentElement('afterend', successMsg);
        
        setTimeout(() => {
            successMsg.remove();
        }, 3000);
    }
}

function toggleCameraCapture(index) {
    const input = document.querySelector(`input[name="foto[${index}][file]"]`);
    if (!input) return;
    
    if (captureMode[index]) {
        // Switch to gallery
        input.removeAttribute('capture');
        captureMode[index] = false;
    } else {
        // Switch to camera
        input.setAttribute('capture', 'environment');
        captureMode[index] = true;
    }
    
    // Reset input value
    input.value = '';
}


function hapusFoto(btn) {
    if(confirm('Hapus foto ini?')) {
        const container = btn.closest('.foto-input');
        const idInput = container.querySelector('input[name*="[id]"]');
        if(idInput && idInput.value) {
            // Create hidden input to mark for deletion
            const deleteInput = document.createElement('input');
            deleteInput.type = 'hidden';
            deleteInput.name = 'foto_delete[]';
            deleteInput.value = idInput.value;
            container.appendChild(deleteInput);
        }
        container.style.opacity = '0.5';
        container.style.textDecoration = 'line-through';
        const fileInput = container.querySelector('input[type="file"]');
        if(fileInput) fileInput.disabled = true;
        
        btn.innerHTML = '<i class="fas fa-undo"></i> Batalkan Hapus';
        btn.classList.remove('btn-outline-secondary');
        btn.classList.add('btn-outline-danger');
        btn.onclick = function() { batalkanHapusFoto(this); };
    }
}

function batalkanHapusFoto(btn) {
    const container = btn.closest('.foto-input');
    const deleteInput = container.querySelector('input[name="foto_delete[]"]');
    if(deleteInput) deleteInput.remove();
    
    container.style.opacity = '1';
    container.style.textDecoration = 'none';
    const fileInput = container.querySelector('input[type="file"]');
    if(fileInput) fileInput.disabled = false;
    
    btn.innerHTML = '<i class="fas fa-trash"></i> Hapus Foto Ini';
    btn.classList.remove('btn-outline-danger');
    btn.classList.add('btn-outline-secondary');
    btn.onclick = function() { hapusFoto(this); };
}

function lihatFoto(src) {
    const modal = document.createElement('div');
    modal.style.position = 'fixed';
    modal.style.top = '0';
    modal.style.left = '0';
    modal.style.width = '100%';
    modal.style.height = '100%';
    modal.style.background = 'rgba(0,0,0,0.8)';
    modal.style.display = 'flex';
    modal.style.justifyContent = 'center';
    modal.style.alignItems = 'center';
    modal.style.zIndex = '2000';
    modal.innerHTML = `
        <div style="position: relative;">
            <img src="${src}" style="max-width: 90vw; max-height: 90vh; border-radius: 8px;">
            <span style="position: absolute; top: 20px; right: 30px; font-size: 30px; color: #fff; cursor: pointer; font-weight: bold;" onclick="this.parentElement.parentElement.remove()">×</span>
        </div>
    `;
    document.body.appendChild(modal);
}
</script>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    .border-left { border-left: 4px solid #0d6efd !important; }
    .card { border: none; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
</style>
@endsection
