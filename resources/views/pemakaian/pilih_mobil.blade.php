@extends('layouts.pegawai')

@section('content')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- jQuery (WAJIB untuk Select2) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<div style="max-width: 600px; margin: 0 auto;">
    <!-- Header -->
    <div style="margin-bottom: 32px;">
        <h1 style="font-size: 28px; font-weight: 700; color: #111827; margin-bottom: 8px;">
            <i class="fas fa-car" style="color: #4f46e5; margin-right: 12px;"></i>Pilih Mobil untuk Pemakaian
        </h1>
        <p style="color: #0004ffff; margin: 0;">Silakan pilih mobil yang akan digunakan untuk pemakaian</p>
    </div>

    @if ($errors->any())
        <div style="background: #fef2f2; border: 1px solid #fee2e2; border-radius: 8px; padding: 16px; margin-bottom: 24px; color: #991b1b;">
            <div style="display: flex; gap: 12px; align-items: flex-start;">
                <i class="fas fa-exclamation-circle" style="margin-top: 2px; flex-shrink: 0;"></i>
                <div>
                    <h5 style="margin: 0 0 8px 0; font-weight: 600;">Terjadi Kesalahan</h5>
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Form Card -->
    <div style="background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 28px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <form action="{{ route('pemakaian.simpanPilihan') }}" method="POST">
            @csrf

            <!-- Mobil Selection -->
<div style="margin-bottom: 24px;">
    <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #111827; font-size: 14px;">
        <i class="fas fa-check-circle" style="color: #4f46e5; margin-right: 6px;"></i>
        Pilih Mobil <span style="color: #ef4444;">*</span>
    </label>

   <select 
    name="mobil_id" 
    id="mobilSelect"
    class="form-select"
    required
    style="width: 100%;"
>
    <option value="">-- Pilih Mobil --</option>
    @foreach($mobils as $mobil)
        <option value="{{ $mobil->id }}" data-plat="{{ $mobil->no_polisi }}" data-merek="{{ $mobil->merek->nama_merek ?? '' }}" data-tipe="{{ $mobil->tipe ?? '' }}" data-tahun="{{ $mobil->tahun ?? '' }}" data-penempatan="{{ $mobil->penempatan->nama_kantor ?? '' }}" {{ $pilihanMobilId == $mobil->id ? 'selected' : '' }}>
            {{ $mobil->no_polisi }} - {{ $mobil->merek->nama_merek ?? 'N/A' }} {{ $mobil->tipe ? '(' . $mobil->tipe . ')' : '' }} {{ $mobil->tahun ? '- ' . $mobil->tahun : '' }} {{ $mobil->penempatan ? '[' . $mobil->penempatan->nama_kantor . ']' : '' }}
        </option>
    @endforeach
</select>


    <small style="display: block; color: #6c757d; margin-top: 6px;">
        <i class="fas fa-info-circle"></i> Total mobil tersedia: <strong>{{ count($mobils) }}</strong>
    </small>
</div>


            <!-- Buttons -->
            <div style="display: flex; gap: 12px; margin-top: 28px;">
                <button type="submit" style="flex: 1; padding: 12px 24px; background: #4f46e5; color: #fff; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; font-size: 14px;">
                    <i class="fas fa-check" style="margin-right: 8px;"></i>Lanjutkan ke Detail
                </button>
                <a href="{{ route('pemakaian.daftar') }}" style="flex: 1; padding: 12px 24px; background: #f3f4f6; color: #111827; border: 1px solid #e5e7eb; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; font-size: 14px; text-decoration: none; text-align: center; display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
                    <i class="fas fa-arrow-left"></i>Kembali
                </a>
            </div>
        </form>
    </div>

    <!-- Info Box -->
    <div style="background: #f0f9ff; border: 1px solid #e0f2fe; border-radius: 8px; padding: 16px; margin-top: 24px; color: #0c4a6e;">
        <div style="display: flex; gap: 12px;">
            <i class="fas fa-lightbulb" style="flex-shrink: 0; margin-top: 2px;"></i>
            <div>
                <strong>Tips:</strong> Pilih mobil yang sesuai dengan kebutuhan Anda, kemudian lanjutkan ke formulir detail untuk mengisi informasi pemakaian.
            </div>
        </div>
    </div>
</div>

<style>
    .form-select {
        transition: all 0.3s ease;
    }
    
    .form-select:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        outline: none;
    }
    
    button[type="submit"]:hover {
        background: #4338ca;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
    }
    
    a[href*="daftar"]:hover {
        background: #e5e7eb;
        transform: translateY(-1px);
    }

    @media (max-width: 640px) {
        div[style*="max-width: 600px"] {
            padding: 0;
        }
        
        div[style*="padding: 28px"] {
            padding: 20px !important;
        }
        
        h1 {
            font-size: 22px !important;
        }
        
        div[style*="display: flex; gap: 12px; margin-top: 28px"] {
            flex-direction: column;
        }
    }
</style>
<script>
document.addEventListener("DOMContentLoaded", function () {
    $('#mobilSelect').select2({
        placeholder: "Cari plat nomor, merek, tipe, tahun, atau penempatan...",
        allowClear: true,
        width: '100%',
        language: {
            noResults: function () {
                return "Mobil tidak ditemukan";
            },
            searching: function() {
                return "Sedang mencari...";
            }
        },
        // Custom search matcher untuk search multiple fields
        matcher: function(params, data) {
            if ($.trim(params.term) === '') {
                return data;
            }
            var searchTerm = params.term.toLowerCase();
            var text = data.text.toLowerCase();
            
            // Cek di text utama (visible option text)
            if (text.indexOf(searchTerm) > -1) {
                return data;
            }
            
            // Cek di attribute data (plat, merek, tipe, tahun, penempatan)
            var $option = $(data.element);
            var plat = $option.data('plat') ? $option.data('plat').toLowerCase() : '';
            var merek = $option.data('merek') ? $option.data('merek').toLowerCase() : '';
            var tipe = $option.data('tipe') ? $option.data('tipe').toLowerCase() : '';
            var tahun = $option.data('tahun') ? $option.data('tahun').toString() : '';
            var penempatan = $option.data('penempatan') ? $option.data('penempatan').toLowerCase() : '';
            
            if (plat.indexOf(searchTerm) > -1 || 
                merek.indexOf(searchTerm) > -1 || 
                tipe.indexOf(searchTerm) > -1 || 
                tahun.indexOf(searchTerm) > -1 ||
                penempatan.indexOf(searchTerm) > -1) {
                return data;
            }
            
            return null;
        },
        // Custom result template
        templateResult: function(data) {
            if (!data.id) return data.text; // Default option
            var $option = $(data.element);
            var plat = $option.data('plat') || '';
            var merek = $option.data('merek') || '';
            var tipe = $option.data('tipe') || '';
            var tahun = $option.data('tahun') || '';
            var penempatan = $option.data('penempatan') || '';
            
            return $('<span><strong>' + plat + '</strong> • ' + merek + ' ' + (tipe ? tipe + ' ' : '') + (tahun ? tahun : '') + (penempatan ? ' <i>[' + penempatan + ']</i>' : '') + '</span>');
        },
        // Custom selection template
        templateSelection: function(data) {
            if (!data.id) return data.text; // Default option
            var $option = $(data.element);
            var plat = $option.data('plat') || '';
            var merek = $option.data('merek') || '';
            return plat + ' - ' + merek;
        }
    });

    // Optional: Remove duplicate script below if exists
});
</script>


@endsection
