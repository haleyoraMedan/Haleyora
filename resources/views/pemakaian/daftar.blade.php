<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pemakaian Mobil Saya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; padding: 20px 0; }
        .container { max-width: 1200px; }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; }
        .badge-pending { background-color: #ffc107; color: #000; }
        .badge-approved { background-color: #28a745; }
        .badge-rejected { background-color: #dc3545; }
        .search-box { margin-bottom: 20px; }
        .btn-action { padding: 4px 10px; font-size: 13px; margin-right: 5px; }
        .table-hover tbody tr:hover { background-color: #f5f5f5; }
        .pagination { margin-top: 20px; }
        .btn-edit { background: #ffc107; color: #000; border: none; }
        .btn-edit:hover { background: #e0a800; color: #000; }

        /* Modal Improvements */
        .modal-dialog { margin: 1rem auto; max-height: 90vh; }
        .modal-content { max-height: 85vh; display: flex; flex-direction: column; }
        .modal-body { flex: 1; overflow-y: auto; padding: 1.5rem; }
        .modal-header { flex-shrink: 0; }
        .modal-header.sticky-top { position: sticky; top: 0; z-index: 1020; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        
        /* Fix backdrop z-index */
        .modal { z-index: 1070 !important; }
        .modal-backdrop { z-index: 1060 !important; }

        /* Foto kecil */
        .foto { max-width: 100px; margin:5px; border:1px solid #ccc; border-radius:4px; cursor: pointer; position: relative; transition: transform 0.2s; }
        .foto:hover { transform: scale(1.1); }

        /* Tooltip posisi foto */
        .foto[data-title]:hover::after {
            content: attr(data-title);
            position: absolute;
            bottom: 100%; left: 50%;
            transform: translateX(-50%);
            background: rgba(0,0,0,0.8);
            color: #fff;
            padding: 2px 6px;
            border-radius: 4px;
            white-space: nowrap;
            font-size: 12px;
            margin-bottom: 5px;
            pointer-events: none;
            z-index: 100;
        }

        /* Modal foto besar */
        .foto-modal { display:none; position:fixed; z-index:2000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.8); justify-content:center; align-items:center; }
        .foto-modal img { max-width:90%; max-height:90%; border-radius:8px; }
        .foto-modal .close-foto { position:absolute; top:20px; right:30px; font-size:30px; color:#fff; cursor:pointer; }
        .row { display:flex; flex-wrap:wrap; margin-bottom:10px; }
        .col-half { flex:0 0 50%; padding:5px; }
    </style>
</head>
<body>
<div class="container">
    <div class="card shadow-sm">
        <div class="card-header">
            <h3 class="mb-0">
                <i class="fas fa-list"></i> Daftar Pemakaian Mobil Saya
            </h3>
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

function lihatDetail(id) {
    fetch('/pemakaian/detail/' + id)
    .then(res => res.json())
    .then(data => {
        let html = `
            <div class="row mb-3">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Mobil:</strong></p>
                    <p class="ms-3 mb-0"><strong class="text-dark">${data.mobil.no_polisi}</strong><br><small class="text-muted">${data.mobil.merek?.nama_merek ?? '-'}</small></p>
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
            
            ${data.catatan ? `<div class="alert alert-light border-start border-success ps-3 mt-3 mb-3"><p class="mb-0"><i class="fas fa-sticky-note"></i> <strong>Catatan:</strong><br>${data.catatan}</p></div>` : ''}`;


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
</body>
</html>
