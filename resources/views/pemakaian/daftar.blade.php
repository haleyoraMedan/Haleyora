@extends('layouts.pegawai')
@section('title','Daftar Pemakaian Mobil Saya')
@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h3 class="mb-0"><i class="fas fa-list"></i> Daftar Pemakaian Mobil Saya</h3>
    </div>
    <div class="card-body">
            <!-- Success Message -->
            @if($message = Session::get('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Sukses!</strong> {{ $message }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Search & Filter Form -->
            <div class="search-box">
                <form method="GET" action="{{ route('pemakaian.daftar') }}" class="row g-2">
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan tujuan..." value="{{ $search ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">-- Semua Status --</option>
                            <option value="pending" {{ (request('status') === 'pending') ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ (request('status') === 'approved') ? 'selected' : '' }}>Approved</option>
                            <option value="available" {{ (request('status') === 'available') ? 'selected' : '' }}>Available</option>
                            <option value="rejected" {{ (request('status') === 'rejected') ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Cari</button>
                        <a href="{{ route('pemakaian.daftar') }}" class="btn btn-secondary w-100 mt-2"><i class="fas fa-redo"></i> Reset</a>
                    </div>
                </form>
            </div>

            @if($pemakaian->isEmpty())
                <div class="alert alert-info mb-0">
                    <strong>Info!</strong> Belum ada pemakaian mobil. <a href="{{ route('pemakaian.pilihMobil') }}" class="alert-link">Buat pemakaian baru</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th width="5%">No</th>
                                <th width="15%">Mobil</th>
                                <th width="20%">Tujuan</th>
                                <th width="12%">Tgl Mulai</th>
                                <th width="12%">Tgl Selesai</th>
                                <th width="10%">Status</th>
                                <th width="26%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pemakaian as $index => $p)
                                <tr>
                                    <td>{{ ($pemakaian->currentPage() - 1) * $pemakaian->perPage() + $loop->iteration }}</td>
                                    <td>
                                        <strong>{{ $p->mobil->no_polisi }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $p->mobil->merek->nama_merek ?? '-' }}</small>
                                    </td>
                                    <td>{{ $p->tujuan }}</td>
                                    <td>{{ \Carbon\Carbon::parse($p->tanggal_mulai)->format('d/m/Y') }}</td>
                                    <td>{{ $p->tanggal_selesai ? \Carbon\Carbon::parse($p->tanggal_selesai)->format('d/m/Y') : '-' }}</td>
                                    <td>
                                        @if($p->status === 'pending')
                                            <span class="badge badge-pending">Pending</span>
                                        @elseif($p->status === 'approved')
                                            <span class="badge badge-approved">Approved</span>
                                        @elseif($p->status === 'available')
                                            <span class="badge badge-available">Available</span>
                                        @else
                                            <span class="badge badge-rejected">Rejected</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info btn-action" onclick="lihatDetail({{ $p->id }})">
                                            <i class="fas fa-eye"></i> Lihat
                                        </button>
                                        @if($p->status === 'pending')
                                            <a href="{{ route('pemakaian.inputDetail') }}?edit_id={{ $p->id }}" class="btn btn-sm btn-edit btn-action">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                        @elseif(auth()->id() === $p->user_id && $p->status !== 'rejected')
                                            <a href="{{ route('pemakaian.inputDetail') }}?edit_id={{ $p->id }}" class="btn btn-sm btn-secondary btn-action">
                                                <i class="fas fa-pen"></i> Input Setelah
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox"></i> Tidak ada data pemakaian
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <nav aria-label="Page navigation">
                    {{ $pemakaian->appends(request()->query())->links('pagination::bootstrap-5') }}
                </nav>
            @endif

            <!-- Tombol Buat Baru -->
            <div class="mt-4">
                <a href="{{ route('pemakaian.pilihMobil') }}" class="btn btn-success btn-lg">
                    <i class="fas fa-plus"></i> Buat Pemakaian Baru
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal detail -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white sticky-top">
                <h5 class="modal-title"><i class="fas fa-info-circle"></i> Detail Pemakaian</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalBody">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal foto besar -->
<div class="modal fade" id="fotoModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="fotoModalImg" src="" alt="Foto" style="max-width: 100%; max-height: 70vh; border-radius: 8px;">
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const detailModal = new bootstrap.Modal(document.getElementById('detailModal'), {});
const fotoModal = new bootstrap.Modal(document.getElementById('fotoModal'), {});

// Live update polling - cek perubahan status setiap 5 detik
let lastUpdateCheck = localStorage.getItem('pemakaianLastCheck') || new Date().toISOString();

function playNotificationSound() {
    const audio = new Audio('/assets/notification.mp3');
    audio.play().catch(err => console.log('Audio play error:', err));
}

function showUpdateNotification(message) {
    // Create toast notification
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-info alert-dismissible fade show';
    alertDiv.style.position = 'fixed';
    alertDiv.style.top = '20px';
    alertDiv.style.right = '20px';
    alertDiv.style.minWidth = '300px';
    alertDiv.style.zIndex = '9999';
    alertDiv.innerHTML = `
        <strong>🔄 Update!</strong> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);
    
    // Auto dismiss after 5 seconds
    setTimeout(() => alertDiv.remove(), 5000);
}

// Refresh tabel pemakaian
function refreshPemakaianList() {
    const currentUrl = new URL(window.location.href);
    const params = new URLSearchParams(currentUrl.search);
    
    let url = '{{ route('pemakaian.daftar') }}';
    if (params.toString()) {
        url += '?' + params.toString();
    }
    
    fetch(url, { 
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.text())
    .then(html => {
        // Extract table content
        const parser = new DOMParser();
        const newDoc = parser.parseFromString(html, 'text/html');
        const newTableHtml = newDoc.querySelector('table')?.outerHTML;
        const oldTableHtml = document.querySelector('table')?.outerHTML;
        
        if (newTableHtml && newTableHtml !== oldTableHtml) {
            // Ada perubahan, update tabel
            const currentTableContainer = document.querySelector('.table-responsive');
            if (currentTableContainer && newTableHtml) {
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = newTableHtml;
                currentTableContainer.innerHTML = tempDiv.innerHTML;
                
                // Play sound & show notification
                playNotificationSound();
                showUpdateNotification('Status pemakaian Anda telah diperbarui!');
            }
        }
        
        lastUpdateCheck = new Date().toISOString();
        localStorage.setItem('pemakaianLastCheck', lastUpdateCheck);
    })
    .catch(err => console.error('Update check error:', err));
}

// Start polling setiap 5 detik
setInterval(refreshPemakaianList, 5000);

function lihatDetail(id) {
    fetch('/pemakaian/detail/' + id)
    .then(res => res.json())
    .then(data => {
        let html = `
            <div class="row mb-3">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Mobil:</strong></p>
                    <p class="ms-3 mb-0"><strong class="text-dark">${data.mobil.no_polisi}</strong><br><small class="text-muted">${data.mobil.merek?.nama_merek ?? '-'}</small><br><small class="text-muted">Penempatan: ${data.mobil.penempatan?.nama_kantor ?? '-'}</small></p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><strong>Tujuan:</strong></p>
                    <p class="ms-3 mb-0">${data.tujuan}</p>
                </div>
            </div>
            
            <hr class="my-3">
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <p class="mb-0"><strong>Tanggal Mulai:</strong><br><span class="ms-3 text-dark">${data.tanggal_mulai}</span></p>
                </div>
                <div class="col-md-6">
                    <p class="mb-0"><strong>Tanggal Selesai:</strong><br><span class="ms-3 text-dark">${data.tanggal_selesai ?? '-'}</span></p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <p class="mb-0"><strong>Jarak Tempuh (km):</strong><br><span class="ms-3 text-dark">${data.jarak_tempuh_km ?? '-'}</span></p>
                </div>
                <div class="col-md-6">
                    <p class="mb-0"><strong>Status:</strong><br><span class="ms-3">`;
        
        if(data.status === 'pending') html += '<span class="badge badge-pending">Pending</span>';
        else if(data.status === 'approved') html += '<span class="badge badge-approved">Approved</span>';
        else if(data.status === 'available') html += '<span class="badge badge-available">Available</span>';
        else html += '<span class="badge badge-rejected">Rejected</span>';
        
        html += `</span></p>
                </div>
            </div>

            <hr class="my-3">

            <div class="row mb-3">
                <div class="col-md-4">
                    <p class="mb-0"><strong><i class="fas fa-gas-pump"></i> Bahan Bakar:</strong><br><span class="ms-3 text-dark">${data.detail?.bahan_bakar ?? '-'}</span></p>
                </div>
                <div class="col-md-4">
                    <p class="mb-0"><strong>Liter Bahan Bakar:</strong><br><span class="ms-3 text-dark">${data.bahan_bakar_liter ?? '-'}</span></p>
                </div>
                <div class="col-md-4">
                    <p class="mb-0"><strong><i class="fas fa-cogs"></i> Transmisi:</strong><br><span class="ms-3 text-dark">${data.detail?.transmisi ?? '-'}</span></p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <p class="mb-0"><strong><i class="fas fa-check-circle"></i> Kondisi Mobil:</strong><br><span class="ms-3 text-dark">${data.detail?.kondisi ?? '-'}</span></p>
                </div>
            </div>
            
            ${data.catatan ? `<div class="alert alert-light border-start border-success ps-3 mt-3 mb-3"><p class="mb-0"><i class="fas fa-sticky-note"></i> <strong>Keluhan:</strong><br>${data.catatan}</p></div>` : ''}`;


        if(Object.keys(data.detail).length > 0) {
            html += '<hr class="my-3"><h6 class="fw-bold"><i class="fas fa-car-side"></i> Detail Kondisi Mobil</h6><div class="row p-3 bg-light rounded">';
            for (let key in data.detail) {
                // Skip fields yang sudah ditampilkan di atas
                if (['bahan_bakar', 'transmisi', 'kondisi'].includes(key)) continue;
                const label = key.charAt(0).toUpperCase() + key.slice(1).replace(/_/g, ' ');
                html += `<div class="col-md-6 mb-3"><p class="mb-1"><strong>${label}:</strong></p><p class="ms-3 mb-0 text-dark">${data.detail[key] ?? '-'}</p></div>`;
            }
            html += '</div>';
        }

        if(data.foto_kondisi.length) {
            html += '<hr class="my-3"><h6 class="fw-bold"><i class="fas fa-images"></i> Foto Kondisi Mobil</h6><div class="row">';
            data.foto_kondisi.forEach(f => {
                html += `<div class="col-md-4 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <img class="card-img-top" src="${f.foto_sebelum}" alt="${f.posisi}" style="height: 200px; object-fit: cover; cursor: pointer;" onclick="perbesarFoto('${f.foto_sebelum}')" title="Klik untuk memperbesar">
                        <div class="card-body p-3">
                            <span class="badge bg-primary">${f.posisi}</span>
                        </div>
                    </div>
                </div>`;
            });
            html += '</div>';
        }

        document.getElementById('modalBody').innerHTML = html;
        detailModal.show();
    })
    .catch(err => {
        alert('Gagal memuat detail. Pastikan server berjalan dengan benar.');
        console.error(err);
    });
}

function perbesarFoto(src) {
    document.getElementById('fotoModalImg').src = src;
    fotoModal.show();
}
</script>
@endsection
