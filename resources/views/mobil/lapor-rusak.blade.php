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

                <input type="hidden" name="mobil_id" value="{{ $mobil->id }}">

                <div class="mb-4">
                    <h5 class="card-title">Informasi Kendaraan</h5>
                    <div class="alert alert-info">
                        <strong>Mobil:</strong> {{ $mobil->no_polisi }} ({{ $mobil->merek->nama_merek ?? 'N/A' }})
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
                            
                            <div class="input-group">
                                <input 
                                    type="file" 
                                    id="foto_bukti"
                                    name="foto_bukti" 
                                    class="form-control" 
                                    accept="image/*" 
                                    capture="environment" 
                                    onchange="showPreview(this)"
                                    required
                                >
                                <button type="button" class="btn btn-primary" onclick="triggerCamera()">
                                    <i class="fas fa-camera"></i> Kamera
                                </button>
                            </div>
                            
                            <div id="preview-container" class="preview-wrapper d-none">
                                <small class="text-success d-block mb-1">
                                    <i class="fas fa-check-circle"></i> Foto berhasil diambil
                                </small>
                                <img id="img-preview" src="#" class="preview-img" onclick="zoomImage()">
                            </div>

                            <small class="text-danger d-block mt-1">
                                📸 Wajib diambil langsung dari kamera saat memilih kategori
                            </small>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="keterangan" class="form-label">Keterangan / Keluhan Tambahan</label>
                    <textarea name="keterangan" class="form-control" rows="3" placeholder="Jelaskan detail masalah..."></textarea>
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
            triggerCamera();
        }, 300);
    }

    function triggerCamera() {
        document.getElementById('foto_bukti').click();
    }

    function showPreview(input) {
        const previewContainer = document.getElementById('preview-container');
        const imgPreview = document.getElementById('img-preview');

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imgPreview.src = e.target.result;
                previewContainer.classList.remove('d-none');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

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