<!-- pastikan pakai layout admin -->
@extends('layouts.admin')
@section('title', 'Daftar Pemakaian Mobil')

@section('content')
<h1>Daftar Pemakaian Mobil</h1>

<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Notifikasi suara -->
@if($notifikasi > 0)
<audio autoplay>
    <source src="{{ asset('assets/notification.mp3') }}" type="audio/mpeg">
</audio>
@endif

<!-- Filter & Search (AJAX) -->
<form id="filterForm" class="mb-3">
    <input type="text" name="search" id="searchInput" placeholder="Cari tujuan" value="{{ $search }}">
    <select name="status" id="statusInput">
        <option value="">Semua Status</option>
        <option value="pending" {{ $status=='pending' ? 'selected' : '' }}>Pending</option>
        <option value="approved" {{ $status=='approved' ? 'selected' : '' }}>Approved</option>
        <option value="rejected" {{ $status=='rejected' ? 'selected' : '' }}>Rejected</option>
        <option value="available" {{ $status=='available' ? 'selected' : '' }}>Available</option>
    </select>
    <button type="submit">Filter</button>
    <button type="button" id="resetBtn">Reset</button>
</form>

<!-- Container for table loaded via AJAX -->
<div id="listContainer">
    @include('admin.pemakaian.partials.table', ['pemakaian' => $pemakaian])
    <!-- initial server render; subsequent updates via AJAX -->
</div>

<!-- Modal detail -->
<div id="modalDetail" style="display:none; position:fixed; top:10%; left:10%; width:80%; background:#fff; border:1px solid #ccc; padding:20px; z-index:9999; overflow:auto; max-height:80%;">
    <h3>Detail Pemakaian</h3>
    <div id="modalContent"></div>
    <button onclick="closeModal()">Tutup</button>
    </div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

function fetchList(url = null) {
    const params = new URLSearchParams();
    const search = document.getElementById('searchInput').value;
    const status = document.getElementById('statusInput').value;
    if (search) params.append('search', search);
    if (status) params.append('status', status);

    const fetchUrl = url ? url : ('{{ route('admin.pemakaian.daftar') }}' + '?' + params.toString());

    fetch(fetchUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' }})
        .then(res => res.json())
        .then(data => {
            document.getElementById('listContainer').innerHTML = data.html;
        })
        .catch(err => console.error(err));
}

// handle filter submit
document.getElementById('filterForm').addEventListener('submit', function(e){
    e.preventDefault();
    fetchList();
});

document.getElementById('resetBtn').addEventListener('click', function(){
    document.getElementById('searchInput').value = '';
    document.getElementById('statusInput').value = '';
    fetchList();
});

// event delegation for pagination links, detail button, status updates and batal
document.addEventListener('click', function(e){
    // pagination links
    if (e.target.closest('.pagination-links a')) {
        e.preventDefault();
        const url = e.target.closest('a').getAttribute('href');
        fetchList(url);
        return;
    }

    // detail button
    if (e.target.classList.contains('btn-detail')) {
        const id = e.target.getAttribute('data-id');
        showDetail(id);
        return;
    }

    // update status
    if (e.target.classList.contains('btn-update-status')) {
        const id = e.target.getAttribute('data-id');
        const select = document.querySelector(`select.status-select[data-id='${id}']`);
        const status = select ? select.value : null;
        if (!status) return;

        fetch(`/admin/pemakaian/${id}/ubah-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ status })
        })
        .then(res => res.json())
        .then(resp => {
            if (resp.success) {
                // update status cell text
                const row = document.querySelector(`tr[data-id='${id}']`);
                if (row) row.querySelector('.status-cell').innerText = resp.status.charAt(0).toUpperCase() + resp.status.slice(1);
            }
        });
        return;
    }

    // batal -> set to rejected
    if (e.target.classList.contains('btn-batal')) {
        const id = e.target.getAttribute('data-id');
        if (!confirm('Batalkan pemakaian ini?')) return;
        fetch(`/admin/pemakaian/${id}/ubah-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ status: 'rejected' })
        })
        .then(res => res.json())
        .then(resp => {
            if (resp.success) {
                const row = document.querySelector(`tr[data-id='${id}']`);
                if (row) row.querySelector('.status-cell').innerText = 'Rejected';
            }
        });
        return;
    }
});

function showDetail(id) {
    fetch('/admin/pemakaian/' + id + '/detail')
        .then(res => res.json())
        .then(data => {
            let html = `<p><strong>Mobil:</strong> ${data.mobil.no_polisi} - ${data.mobil.merek.nama_merek}</p>`;
            html += `<p><strong>Tujuan:</strong> ${data.tujuan}</p>`;
            html += `<p><strong>Tanggal Mulai:</strong> ${data.tanggal_mulai}</p>`;
            html += `<p><strong>Tanggal Selesai:</strong> ${data.tanggal_selesai ?? '-'}</p>`;
            html += `<p><strong>Status:</strong> ${data.status}</p>`;
            if(data.detail) {
                html += '<h4>Detail Mobil:</h4><ul>';
                for(let key in data.detail) {
                    html += `<li>${key}: ${data.detail[key]}</li>`;
                }
                html += '</ul>';
            }
            if(data.foto_kondisi && data.foto_kondisi.length > 0) {
                html += '<h4>Foto Kondisi:</h4>';
                data.foto_kondisi.forEach(f => {
                    html += `<img src="${f.foto_sebelum}" style="width:100px;margin:5px;">`;
                });
            }
            document.getElementById('modalContent').innerHTML = html;
            document.getElementById('modalDetail').style.display = 'block';
        });
}

function closeModal() {
    document.getElementById('modalDetail').style.display = 'none';
}
</script>

@endsection
