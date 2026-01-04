@extends('layouts.admin')

@section('title', 'Data Jenis Mobil')

@section('content')
<div class="admin-card">
    <div>
        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
            <div>
                <h3 class="admin-title" style="margin: 0;">Data Jenis Mobil</h3>
                <p style="color: #6c757d; margin: 8px 0 0 0; font-size: 14px;">Kelola daftar jenis kendaraan</p>
            </div>
            <div style="display:flex; gap:8px; align-items:center;">
                <button id="exportJenisBtn" class="admin-btn primary" style="display: flex; align-items: center; gap: 8px; white-space: nowrap;">
                    <i class="fas fa-download"></i> Export Terpilih
                </button>
                <button id="bulkDeleteJenisBtn" class="admin-btn danger"><i class="fas fa-trash"></i> Hapus Terpilih</button>
            </div>
        </div>

        @if(session('success'))
            <div style="background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; color: #155724; display: flex; align-items: center; gap: 12px;">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($user->role === 'admin')
        <!-- Add Form -->
        <div style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; margin-bottom: 24px;">
            <h5 style="margin: 0 0 16px 0; color: #111827; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-plus-circle" style="color: #4f46e5;"></i> Tambah Jenis Mobil
            </h5>
            <form action="{{ route('jenis-mobil.store') }}" method="POST" style="display: grid; grid-template-columns: 1fr auto; gap: 12px;">
                @csrf
                <input type="text" name="nama_jenis" class="form-control" placeholder="Masukkan nama jenis mobil" required style="border-radius: 6px;">
                <button class="admin-btn primary" style="white-space: nowrap;">Simpan</button>
            </form>
        </div>
        @endif

        <!-- Search Box -->
        <div style="margin-bottom: 20px; display: flex; gap: 8px;">
            <input type="text" id="searchJenis" class="form-control" placeholder="🔍 Cari nama jenis..." style="border-radius: 6px; border: 1px solid #d1d5db;">
            <button id="searchJenisBtn" class="admin-btn primary" style="white-space: nowrap; display: flex; align-items: center; gap: 6px;">
                <i class="fas fa-search"></i> Cari
            </button>
            <button id="resetJenisBtn" class="admin-btn" style="white-space: nowrap; display: flex; align-items: center; gap: 6px; background: #6c757d; color: white; border: none;">
                <i class="fas fa-redo"></i> Reset
            </button>
        </div>

        <!-- Table -->
        <div class="table-responsive" style="border: 1px solid #dee2e6; border-radius: 8px; overflow: hidden;">
            <table class="table table-hover" style="margin: 0;">
                <thead style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                    <tr>
                        <th style="width:50px; text-align: center;"><input type="checkbox" id="selectAllJenis"></th>
                        <th style="width:60px; color: #6c757d;">No</th>
                        <th style="color: #6c757d;">Nama Jenis</th>
                        <th style="width:280px; color: #6c757d;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="jenisTableBody">
                    @foreach ($data as $item)
                    <tr class="jenis-row" data-name="{{ strtolower($item->nama_jenis) }}">
                        <td style="text-align: center;"><input type="checkbox" class="jenis-checkbox" value="{{ $item->id }}"></td>
                        <td><span style="color: #6c757d; font-size: 13px;">{{ $loop->iteration }}</span></td>
                        <td><strong>{{ $item->nama_jenis }}</strong></td>
                        <td>
                            @if($user->role === 'admin')
                            <form action="{{ route('jenis-mobil.update', $item->id) }}" method="POST" style="display: grid; grid-template-columns: 1fr auto; gap: 8px; margin-bottom: 8px;">
                                @csrf
                                @method('PUT')
                                <input type="text" name="nama_jenis" value="{{ $item->nama_jenis }}" class="form-control" required style="border-radius: 6px; font-size: 14px;">
                                <button class="admin-btn success" style="white-space: nowrap; padding: 8px 16px; font-size: 13px;"><i class="fas fa-save"></i> Update</button>
                            </form>
                            <form action="{{ route('jenis-mobil.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin hapus jenis ini?')" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button class="admin-btn danger" style="white-space: nowrap; padding: 8px 16px; font-size: 13px;"><i class="fas fa-trash"></i> Hapus</button>
                            </form>
                            @else
                                <span style="color: #6c757d;">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div id="noDataMsg" style="text-align: center; padding: 32px; color: #6c757d; display: none;">
            <i class="fas fa-inbox" style="font-size: 32px; margin-bottom: 12px; opacity: 0.5; display: block;"></i>
            <p style="margin: 0; font-size: 14px;">Data tidak ditemukan</p>
        </div>
    </div>
</div>

<script>
// Select All functionality
document.getElementById('selectAllJenis').addEventListener('change', function() {
    document.querySelectorAll('.jenis-checkbox').forEach(cb => cb.checked = this.checked);
});

// Export functionality
document.getElementById('exportJenisBtn').addEventListener('click', function() {
    const ids = Array.from(document.querySelectorAll('.jenis-checkbox:checked')).map(cb => cb.value);
    if (!ids.length) return alert('Pilih minimal satu jenis untuk diexport.');
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route('admin.tools.export') }}';
    form.innerHTML = `<input type="hidden" name="_token" value="{{ csrf_token() }}">
                      <input type="hidden" name="model" value="jenis">`;
    ids.forEach(id => form.innerHTML += `<input type="hidden" name="ids[]" value="${id}">`);
    document.body.appendChild(form);
    form.submit();
});

// Bulk delete jenis
document.getElementById('bulkDeleteJenisBtn').addEventListener('click', function() {
    const ids = Array.from(document.querySelectorAll('.jenis-checkbox:checked')).map(cb => cb.value);
    if (!ids.length) return alert('Pilih minimal satu jenis untuk dihapus.');
    if (!confirm('Yakin menghapus jenis terpilih?')) return;
    const form = document.createElement('form'); form.method = 'POST'; form.action = '{{ route('jenis-mobil.bulkDestroy') }}';
    form.innerHTML = `<input type="hidden" name="_token" value="{{ csrf_token() }}">`;
    ids.forEach(id => form.innerHTML += `<input type="hidden" name="ids[]" value="${id}">`);
    document.body.appendChild(form); form.submit();
});

// Search functionality
document.getElementById('searchJenisBtn').addEventListener('click', function() {
    performSearch();
});
document.getElementById('searchJenis').addEventListener('keyup', function(e) {
    if (e.key === 'Enter') performSearch();
});

function performSearch() {
    const searchTerm = document.getElementById('searchJenis').value.toLowerCase();
    const rows = document.querySelectorAll('.jenis-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const name = row.dataset.name;
        if (name.includes(searchTerm)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    document.getElementById('noDataMsg').style.display = visibleCount === 0 ? 'block' : 'none';
    document.getElementById('jenisTableBody').style.display = visibleCount === 0 ? 'none' : '';
}

// Reset functionality
document.getElementById('resetJenisBtn').addEventListener('click', function() {
    document.getElementById('searchJenis').value = '';
    document.querySelectorAll('.jenis-row').forEach(row => row.style.display = '');
    document.getElementById('noDataMsg').style.display = 'none';
});
</script>

@endsection

<script>
document.getElementById('selectAllJenis').addEventListener('change', function() {
    document.querySelectorAll('.jenis-checkbox').forEach(cb => cb.checked = this.checked);
});
document.getElementById('exportJenisBtn').addEventListener('click', function() {
    const ids = Array.from(document.querySelectorAll('.jenis-checkbox:checked')).map(cb => cb.value);
    if (!ids.length) return alert('Pilih minimal satu jenis untuk diexport.');
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route('admin.tools.export') }}';
    form.innerHTML = `<input type="hidden" name="_token" value="{{ csrf_token() }}">
                      <input type="hidden" name="model" value="jenis">`;
    ids.forEach(id => form.innerHTML += `<input type="hidden" name="ids[]" value="${id}">`);
    document.body.appendChild(form);
    form.submit();
});
</script>