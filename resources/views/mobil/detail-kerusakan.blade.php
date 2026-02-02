@extends('layouts.admin')

@section('title', 'Detail Kerusakan Mobil')

@section('content')
<div class="admin-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3><i class="fas fa-car-crash text-danger"></i> Detail Kerusakan - {{ e($mobil->no_polisi) }}</h3>
        <div class="d-flex gap-2">
            @if(Auth::user() && in_array(optional(Auth::user())->role ?? '', ['admin']))
                @php
                    $pending = 0; $approved = 0; $rejected = 0;
                    if(isset($mobil->laporanRusak) && count($mobil->laporanRusak)){
                        foreach($mobil->laporanRusak as $lr){
                            if($lr->status == \App\Models\LaporanRusak::STATUS_PENDING) $pending++;
                            if($lr->status == \App\Models\LaporanRusak::STATUS_APPROVED) $approved++;
                            if($lr->status == \App\Models\LaporanRusak::STATUS_REJECTED) $rejected++;
                        }
                    }
                @endphp

                @if($approved > 0)
                    <button class="admin-btn success" id="btnSetAvailable">Set Available</button>
                @elseif($pending > 0)
                    <button class="admin-btn primary" id="btnApprove">Approve</button>
                    <button class="admin-btn danger" id="btnReject">Reject</button>
                @elseif($rejected > 0)
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-danger">Laporan ditolak - mobil berstatus rusak</span>
                        <button class="admin-btn success" id="btnSetAvailable">Set Available</button>
                    </div>
                @endif
            @endif

            <a href="{{ route('mobil.index') }}" class="admin-btn secondary">Kembali</a>
        </div>
    </div>

    <div class="card mb-3">
            <div class="card-body">
            <p><strong>No Polisi:</strong> {{ e($mobil->no_polisi) }}</p>
            <p><strong>Brand:</strong> {{ e(optional($mobil->merek)->nama_merek) }}</p>
            <p><strong>Jenis:</strong> {{ e(optional($mobil->jenis)->nama_jenis) }}</p>
            <p><strong>Penempatan:</strong> {{ e(optional($mobil->penempatan)->nama_kantor) }}</p>
            <p><strong>Kondisi detail:</strong> {{ e(optional($mobil->detail)->kondisi ?? '-') }}</p>
        </div>
    </div>

    <h5>Laporan Kerusakan</h5>
    @if(isset($mobil->laporanRusak) && count($mobil->laporanRusak))
        @foreach($mobil->laporanRusak as $laporan)
            <div class="card mb-3">
                <div class="card-header">
                    <strong>Laporan #{{ $laporan->id }}</strong>
                    <span class="text-muted"> - {{ $laporan->created_at->format('d M Y H:i') }}</span>
                </div>
                <div class="card-body">
                    <p><strong>Pelapor:</strong> {{ e(optional($laporan->user)->username ?? optional($laporan->user)->nip ?? 'N/A') }}</p>
                    <p><strong>Kondisi yang dilaporkan:</strong> {{ e($laporan->kondisi) }}</p>
                    <p><strong>Catatan:</strong> {{ e($laporan->catatan ?? '-') }}</p>
                    <p><strong>Lokasi:</strong> {{ e($laporan->lokasi ?? '-') }}</p>

                    <div class="row">
                        @if(isset($laporan->fotos) && count($laporan->fotos))
                            @foreach($laporan->fotos as $foto)
                                <div class="col-md-3 mb-2">
                                    <a href="{{ $foto->file_path }}" target="_blank">
                                        <img src="{{ $foto->file_path }}" class="img-fluid rounded" style="max-height:160px;object-fit:cover;">
                                    </a>
                                    <div class="small text-muted">Posisi: {{ e($foto->posisi) }}</div>
                                </div>
                            @endforeach
                        @else
                            <div class="col-12 text-muted">Tidak ada foto</div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="alert alert-info">Tidak ditemukan laporan kerusakan untuk mobil ini.</div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.getElementById('btnSetAvailable')?.addEventListener('click', function(){
    openKeteranganModal('{!! route('mobil.setAvailable', $mobil->id) !!}', 'Set Available', 'Masukkan keterangan (wajib)');
});
document.getElementById('btnReject')?.addEventListener('click', function(){
    openKeteranganModal('{!! route('mobil.rejectLaporan', $mobil->id) !!}', 'Reject Laporan', 'Masukkan keterangan (wajib)');
});
document.getElementById('btnApprove')?.addEventListener('click', function(){
    openKeteranganModal('{!! route('mobil.approveLaporan', $mobil->id) !!}', 'Approve Laporan', 'Masukkan keterangan (wajib)');
});

function openKeteranganModal(actionUrl, title, placeholder){
        // create modal if not exists
        let modal = document.getElementById('keteranganModal');
        if(!modal){
                modal = document.createElement('div');
                modal.id = 'keteranganModal';
                modal.innerHTML = `
                <div class="modal" tabindex="-1" style="display:block;background:rgba(0,0,0,0.4);position:fixed;inset:0;z-index:2000;">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">${title}</h5>
                                <button type="button" class="btn-close" id="keteranganClose"></button>
                            </div>
                            <div class="modal-body">
                                <form id="keteranganForm" method="POST" action="${actionUrl}">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                                    <div class="mb-3">
                                        <label class="form-label">Keterangan</label>
                                        <textarea name="keterangan" class="form-control" placeholder="${placeholder}" rows="4"></textarea>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" id="keteranganCancel">Batal</button>
                                <button type="button" class="btn btn-primary" id="keteranganSubmit">Kirim</button>
                            </div>
                        </div>
                    </div>
                </div>`;
                document.body.appendChild(modal);

                document.getElementById('keteranganClose').addEventListener('click', closeModal);
                document.getElementById('keteranganCancel').addEventListener('click', closeModal);
                document.getElementById('keteranganSubmit').addEventListener('click', function(){
                    const form = document.getElementById('keteranganForm');
                    const txt = form.querySelector('textarea[name=keterangan]').value.trim();
                    if(!txt){ alert('Keterangan admin wajib diisi.'); return; }
                    form.submit();
                });
        } else {
                // update title and action
                modal.querySelector('.modal-title').textContent = title;
                const form = modal.querySelector('#keteranganForm');
                form.action = actionUrl;
                modal.querySelector('textarea[name=keterangan]').placeholder = placeholder;
        }

        // show modal
        modal.style.display = 'block';

        function closeModal(){
                modal.style.display = 'none';
        }
}
</script>
@endpush
