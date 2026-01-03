<!-- pastikan pakai layout admin -->
@extends('layouts.admin')
@section('title', 'Daftar Pemakaian Mobil')

@section('content')
<div class="admin-card">
    <div class="admin-toolbar">
        <h2 class="admin-title"><i class="fas fa-list"></i> Daftar Pemakaian Mobil</h2>
        <div class="ms-auto d-flex align-items-center gap-2">
            <span class="badge bg-danger" id="badgePending"><i class="fas fa-hourglass-half"></i> Pending: {{ $notifikasi }}</span>
            <button id="exportSelected" class="admin-btn ms-2"><i class="fas fa-file-export"></i> Export Selected</button>
            <button id="deleteSelected" class="admin-btn danger ms-2"><i class="fas fa-trash"></i> Hapus Terpilih</button>
        </div>
    </div>

    <!-- Filter & Search (AJAX) -->
    <div class="mt-3">
            <form id="filterForm" class="d-flex flex-wrap gap-2 form-admin align-items-center">
            <input type="text" name="search" id="searchInput" class="form-control" style="flex: 1; min-width: 200px;" placeholder="🔍 Cari nama user, tujuan, no polisi..." value="{{ $search }}">
            <input type="date" name="date_from" id="dateFromInput" class="form-control" style="flex: 0 1 165px;" value="{{ request('date_from', $date_from ?? '') }}" />
            <input type="date" name="date_to" id="dateToInput" class="form-control" style="flex: 0 1 165px;" value="{{ request('date_to', $date_to ?? '') }}" />
            <select name="status" id="statusInput" class="form-select" style="flex: 0 1 200px;">
                <option value="">-- Semua Status --</option>
                <option value="pending" {{ $status=='pending' ? 'selected' : '' }}>⏳ Pending</option>
                <option value="approved" {{ $status=='approved' ? 'selected' : '' }}>✓ Approved</option>
                <option value="rejected" {{ $status=='rejected' ? 'selected' : '' }}>✗ Rejected</option>
                <option value="available" {{ $status=='available' ? 'selected' : '' }}>◆ Available</option>
            </select>
            <button type="submit" class="admin-btn primary">
                <i class="fas fa-search"></i> Cari
            </button>
            <button type="button" id="resetBtn" class="admin-btn">
                <i class="fas fa-redo"></i> Reset
            </button>
        </form>
    </div>

    <!-- Container for table loaded via AJAX -->
    <div id="listContainer" class="mt-3">
        @include('admin.pemakaian.partials.table', ['pemakaian' => $pemakaian])
        <!-- initial server render; subsequent updates via AJAX -->
    </div>

</div>

<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Notifikasi suara diatur lewat push/service worker; autoplay dihapus untuk UX -->

<!-- Modal detail -->
<style>
    .modal-detail { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; overflow: auto; }
    .modal-detail.show { display:flex; align-items:center; justify-content:center; }
    .modal-detail-content { background:#fff; border-radius:8px; padding:24px; max-width:900px; width:95%; max-height:90vh; overflow:auto; box-shadow:0 10px 30px rgba(0,0,0,0.2); }
    .modal-detail-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; }
    .close-btn { background:none; border:none; font-size:24px; cursor:pointer; color:#666; }
</style>

<div id="modalDetail" class="modal-detail">
    <div class="modal-detail-content">
        <div class="modal-detail-header">
            <h4><i class="fas fa-info-circle"></i> Detail Pemakaian</h4>
            <button class="close-btn" onclick="closeModal()">&times;</button>
        </div>
        <div id="modalContent">
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const pollingInterval = 3000; // 3 detik
let lastCheck = localStorage.getItem('lastCheck') || null;

function closeModal() {
    document.getElementById('modalDetail').classList.remove('show');
}

// Fungsi untuk play sound notifikasi
function playSound() {
    const audio = new Audio('/assets/notification.mp3');
    audio.play().catch(err => console.log('Audio play error:', err));
}

// Tampilkan notifikasi browser
function showBrowserNotification(title, body, icon = '/assets/notification.png') {
    if (Notification.permission === 'granted') {
        const options = {
            body: body,
            icon: icon,
            tag: 'pemakaian-notif',
            requireInteraction: true,
            badge: '/assets/notification-badge.png',
            data: { url: '/admin/pemakaian' }
        };
        
        if (navigator.serviceWorker && navigator.serviceWorker.controller) {
            navigator.serviceWorker.ready.then(reg => {
                reg.showNotification(title, options);
            });
        } else {
            try { new Notification(title, options); } catch(e) {}
        }
    }
}

function fetchList(url = null) {
    const params = new URLSearchParams();
    const search = document.getElementById('searchInput').value;
    const status = document.getElementById('statusInput').value;
    const dateFrom = document.getElementById('dateFromInput').value;
    const dateTo = document.getElementById('dateToInput').value;
    if (search) params.append('search', search);
    if (status) params.append('status', status);
    if (dateFrom) params.append('date_from', dateFrom);
    if (dateTo) params.append('date_to', dateTo);

    let fetchUrl;
        if (url) {
        // Parse pagination URL and add search/status/date params
        const urlObj = new URL(url, window.location.origin);
        if (search) urlObj.searchParams.set('search', search);
        if (status) urlObj.searchParams.set('status', status);
        if (dateFrom) urlObj.searchParams.set('date_from', dateFrom);
        if (dateTo) urlObj.searchParams.set('date_to', dateTo);
        fetchUrl = urlObj.toString().replace(window.location.origin, '');
    } else {
        // New request - build from scratch
        fetchUrl = '{{ route('admin.pemakaian.list') }}' + (params.toString() ? '?' + params.toString() : '');
    }

    fetch(fetchUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' }})
        .then(res => res.json())
        .then(data => {
            if (data.html) {
                document.getElementById('listContainer').innerHTML = data.html;
                // sync checkboxes after table replacement (preserve multi-page selections)
                if (typeof afterTableReload === 'function') afterTableReload();
            }
            // Update badge jika notifikasi count di-return
            if (data.notifikasi !== undefined) {
                updateBadgeGlobal(data.notifikasi);
            }
        })
        .catch(err => console.error(err));
}

function updateBadgeGlobal(count) {
    const badgeEl = document.getElementById('badgePending');
    if (badgeEl) {
        badgeEl.textContent = `⏳ Pending: ${count}`;
        badgeEl.style.display = count > 0 ? 'inline-block' : 'none';
    }
}

// handle filter submit
document.getElementById('filterForm').addEventListener('submit', function(e){
    e.preventDefault();
    fetchList();
});

// Tambah notifikasi toast saat status berubah
function showNotificationToast(message, type = 'success') {
    const toastContainer = document.getElementById('notificationToasts') || createToastContainer();
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show`;
    toast.style.position = 'fixed';
    toast.style.top = '20px';
    toast.style.right = '20px';
    toast.style.minWidth = '300px';
    toast.style.zIndex = '9999';
    toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    toastContainer.appendChild(toast);
    setTimeout(() => toast.remove(), 5000);
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'notificationToasts';
    document.body.appendChild(container);
    return container;
}

document.getElementById('resetBtn').addEventListener('click', function(){
    document.getElementById('searchInput').value = '';
    document.getElementById('statusInput').value = '';
    document.getElementById('dateFromInput').value = '';
    document.getElementById('dateToInput').value = '';
    fetchList();
});

// event delegation for pagination links, detail button, status updates and batal
document.addEventListener('click', function(e){
    // pagination links - match Bootstrap pagination (.page-link)
    if (e.target.classList.contains('page-link')) {
        e.preventDefault();
        const url = e.target.getAttribute('href');
        if (url && !e.target.closest('.disabled')) {
            fetchList(url);
        }
        return;
    }

    // detail button
    if (e.target.classList.contains('btn-detail')) {
        const id = e.target.getAttribute('data-id');
        showDetail(id);
        return;
    }

    // toggle status controls
    if (e.target.classList.contains('btn-toggle-status')) {
        const id = e.target.getAttribute('data-id');
        const statusControls = document.querySelector(`.status-controls[data-id='${id}']`);
        if (statusControls) {
            statusControls.classList.toggle('d-none');
        }
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
                // play sound & show notification
                playSound();
                showNotificationToast(`✅ Status berhasil diubah menjadi <strong>${status}</strong>`, 'success');
                // refresh list dengan delay kecil
                setTimeout(() => fetchList(), 500);
            } else {
                showNotificationToast(`❌ ${resp.message || 'Gagal mengubah status'}`, 'danger');
            }
        })
        .catch(err => {
            console.error(err);
            showNotificationToast('❌ Terjadi error saat mengubah status', 'danger');
        });
        return;
    }

    // cancel status change
    if (e.target.classList.contains('btn-cancel-status')) {
        const id = e.target.getAttribute('data-id');
        const statusControls = document.querySelector(`.status-controls[data-id='${id}']`);
        if (statusControls) {
            statusControls.classList.add('d-none');
        }
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
                // play sound & show notification
                playSound();
                showNotificationToast('🚫 Pemakaian berhasil dibatalkan (ditolak)', 'warning');
                // refresh list dengan delay kecil
                setTimeout(() => fetchList(), 500);
            } else {
                showNotificationToast(`❌ ${resp.message || 'Gagal membatalkan'}`, 'danger');
            }
        })
        .catch(err => {
            console.error(err);
            showNotificationToast('❌ Terjadi error', 'danger');
        });
        return;
    }

    // delete pemakaian
    if (e.target.classList.contains('btn-delete')) {
        const id = e.target.getAttribute('data-id');
        if (!confirm('Yakin menghapus pemakaian ini? Semua foto terkait juga akan dihapus.')) return;

        fetch('/pemakaian/' + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(resp => {
            if (resp.success) {
                showNotificationToast(resp.message || 'Data berhasil dihapus', 'success');
                fetchList();
            } else {
                showNotificationToast(resp.message || 'Gagal menghapus data', 'danger');
            }
        })
        .catch(err => {
            console.error(err);
            showNotificationToast('Terjadi error saat menghapus', 'danger');
        });

        return;
    }
});

function showDetail(id) {
    fetch('/admin/pemakaian/' + id + '/detail')
        .then(res => res.json())
        .then(data => {
            // Header dengan status badge
            let statusBadge = 'secondary';
            if (data.status === 'pending') statusBadge = 'warning';
            else if (data.status === 'approved') statusBadge = 'success';
            else if (data.status === 'available') statusBadge = 'info';
            else if (data.status === 'rejected') statusBadge = 'danger';
            console.log(data);
            let html = `
                <div class="row mb-4">
                    <div class="col-md-6">
                                        <div class="card border-0 shadow-sm h-100">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="mb-0"><i class="fas fa-user-circle"></i> Informasi Pengguna</h6>
                                        </div>
                                        <div class="card-body">
                                            ${(() => {
                                                // Prefer showing user's name if available, otherwise show NIP as primary identifier
                                                const name = data.user.name ? `<p class="mb-2"><strong>${data.user.name}</strong></p>` : ``;
                                                const nipLine = data.user.nip ? `<p class="mb-2"><small class="text-muted"><i class="fas fa-id-card"></i> NIP: ${data.user.nip}</small></p>` : ``;
                                                const emailLine = data.user.email ? `<p class="mb-2"><small class="text-muted"><i class="fas fa-envelope"></i> ${data.user.email}</small></p>` : ``;
                                                return name + nipLine + emailLine;
                                            })()}
                                            <p class="mb-0"><span class="badge bg-secondary">${data.user.role}</span></p>
                                        </div>
                                    </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0"><i class="fas fa-car"></i> Informasi Mobil</h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-2"><strong>${data.mobil.no_polisi}</strong></p>
                                <p class="mb-2"><small class="text-muted"><i class="fas fa-tag"></i> ${data.mobil.merek.nama_merek} - ${data.mobil.tipe}</small></p>
                                <p class="mb-0"><span class="badge bg-${statusBadge}">${data.status.toUpperCase()}</span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0"><i class="fas fa-home"></i> Informasi Penempatan</h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-2"><strong>Nama Kantor : ${data.mobil.kantor ?? '-'}</strong></p>
                                <p class="mb-2"><strong>Kota/Kabupaten : ${data.mobil.kota ?? '-'}</strong></p>
                                
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="fas fa-map-marker-alt"></i> Detail Perjalanan</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <p class="mb-1"><strong>Tujuan:</strong></p>
                                <p class="ms-3 text-dark">${data.tujuan}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <p class="mb-1"><strong>Jarak Tempuh:</strong></p>
                                <p class="ms-3 text-dark">${data.jarak_tempuh_km || '-'} km</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <p class="mb-1"><strong>Tanggal Mulai:</strong></p>
                                <p class="ms-3 text-dark">${data.tanggal_mulai}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <p class="mb-1"><strong>Tanggal Selesai:</strong></p>
                                <p class="ms-3 text-dark">${data.tanggal_selesai ?? '-'}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <p class="mb-1"><strong><i class="fas fa-gas-pump"></i> Bahan Bakar:</strong></p>
                                <p class="ms-3 text-dark">${data.bahan_bakar} (${data.bahan_bakar_liter || '-'} liter)</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <p class="mb-1"><strong><i class="fas fa-cogs"></i> Transmisi:</strong></p>
                                <p class="ms-3 text-dark">${data.detail?.transmisi ?? '-'}</p>
                            </div>
                        </div>
                        ${data.catatan ? `<div class="alert alert-light border-start border-warning ps-3 mt-3 mb-0"><p class="mb-0"><i class="fas fa-sticky-note"></i> <strong>Keluhan:</strong><br>${data.catatan}</p></div>` : ''}
                    </div>
                </div>`;

            if(Object.keys(data.detail).length > 0) {
                html += `<div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0"><i class="fas fa-check-circle"></i> Kondisi Mobil</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">`;
                for (let key in data.detail) {
                    if (['transmisi'].includes(key)) continue;
                    const label = key.charAt(0).toUpperCase() + key.slice(1).replace(/_/g, ' ');
                    html += `<div class="col-md-6 mb-3"><p class="mb-1"><strong>${label}:</strong></p><p class="ms-3 text-dark">${data.detail[key] ?? '-'}</p></div>`;
                }
                html += `</div>
                    </div>
                </div>`;
            }

            if(data.foto_kondisi && data.foto_kondisi.length) {
                html += `<div class="card border-0 shadow-sm">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0"><i class="fas fa-images"></i> Foto Kondisi Mobil</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">`;
                data.foto_kondisi.forEach(f => {
                    html += `<div class="col-md-4 mb-3">
                        <div class="card border-0 shadow-sm h-100">
                            <img class="card-img-top" src="${f.foto_sebelum}" alt="${f.posisi}" style="height: 200px; object-fit: cover; cursor: pointer;" onclick="perbesarFoto('${f.foto_sebelum}')" title="Klik untuk memperbesar">
                            <div class="card-body p-2">
                                <span class="badge bg-primary">${f.posisi}</span>
                            </div>
                        </div>
                    </div>`;
                });
                html += `</div>
                    </div>
                </div>`;
            }
            document.getElementById('modalContent').innerHTML = html;
            document.getElementById('modalDetail').classList.add('show');
        })
        .catch(err => {
            alert('Gagal memuat detail. Silakan coba lagi.');
            console.error(err);
        });
}

// --- Real-time polling + push registration ---
(() => {
    const AUDIO_URL = '{{ asset('assets/notification.mp3') }}';
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let lastCheck = localStorage.getItem('lastCheck') || null;
    let pollingInterval = 7000; // 7s
    let badgeCount = {{ $notifikasi ?? 0 }};

    function playSound() {
        try {
            const a = new Audio(AUDIO_URL);
            a.play().catch(()=>{});
        } catch (e) {}
    }

    function updateBadge(n) {
        badgeCount = n;
        const badgeEl = document.getElementById('badgePending');
        if (badgeEl) {
            badgeEl.textContent = `⏳ Pending: ${badgeCount}`;
            badgeEl.style.display = badgeCount > 0 ? 'inline-block' : 'none';
        }
        // update title
        if (badgeCount > 0) {
            document.title = `(${badgeCount}) Daftar Pemakaian`;
            if (navigator.setAppBadge) navigator.setAppBadge(badgeCount).catch(()=>{});
        } else {
            document.title = 'Daftar Pemakaian Mobil';
            if (navigator.clearAppBadge) navigator.clearAppBadge().catch(()=>{});
        }
    }

    window.perbesarFoto = function(src) {
        const modal = document.createElement('div');
        modal.style.position = 'fixed';
        modal.style.top = '0';
        modal.style.left = '0';
        modal.style.width = '100%';
        modal.style.height = '100%';
        modal.style.background = 'rgba(0,0,0,0.9)';
        modal.style.display = 'flex';
        modal.style.justifyContent = 'center';
        modal.style.alignItems = 'center';
        modal.style.zIndex = '10000';
        modal.innerHTML = `
            <div style="position: relative;">
                <img src="${src}" style="max-width: 90vw; max-height: 90vh; border-radius: 8px;">
                <span style="position: absolute; top: 20px; right: 30px; font-size: 40px; color: #fff; cursor: pointer; font-weight: bold;" onclick="this.closest('div').parentElement.remove()">×</span>
            </div>
        `;
        document.body.appendChild(modal);
    }

    function showBrowserNotification(title, body) {
        if (Notification.permission === 'granted') {
            if (navigator.serviceWorker && navigator.serviceWorker.controller) {
                navigator.serviceWorker.ready.then(reg => {
                    reg.showNotification(title, { body, tag: 'pemakaian-notif' });
                });
            } else {
                try { new Notification(title, { body }); } catch(e) {}
            }
        }
    }

    function pollOnce() {
        const url = '{{ route('admin.pemakaian.checkNew') }}' + (lastCheck ? ('?last_check=' + encodeURIComponent(lastCheck)) : '');
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }})
            .then(r => r.json())
            .then(data => {
                if (!data) return;
                if (data.new && data.new > 0) {
                    // ada item baru atau update
                    playSound();
                    const message = data.new > 1 ? `${data.new} pemakaian baru/update` : `Ada pemakaian baru/update`;
                    showBrowserNotification('🔔 Notifikasi Pemakaian', message);
                    // refresh list
                    fetchList();
                }
                updateBadge(data.pending ?? 0);
                lastCheck = data.server_time || new Date().toISOString();
                localStorage.setItem('lastCheck', lastCheck);
            }).catch(()=>{});
    }

    // start polling
    setInterval(pollOnce, pollingInterval);
    // run first immediately
    pollOnce();

    // ask permission for browser notifications (Notification API only)
    (function requestNotificationPermission(){
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission().catch(()=>{});
        }
    })();

    // register service worker and subscribe to push (VAPID public key from env)
    (async function registerSWAndSubscribe(){
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) return;

        try {
            const reg = await navigator.serviceWorker.register('/sw.js');
            // listen messages from SW to play sound when push arrives and page open
            navigator.serviceWorker.addEventListener('message', function(event){
                try {
                    if (event.data && event.data.type === 'push') {
                        // play sound jika push notification include sound flag
                        if (event.data.playSound) {
                            playSound();
                        }
                        if (event.data.payload && event.data.payload.pending_count !== undefined) {
                            updateBadge(event.data.payload.pending_count);
                        }
                        fetchList();
                    }
                } catch(e){}
            });

            const vapidPublic = '{{ env('VAPID_PUBLIC_KEY', '') }}';
            if (!vapidPublic) return;

            // request permission if not granted
            if (Notification.permission !== 'granted') {
                await Notification.requestPermission();
            }

            const sub = await reg.pushManager.getSubscription();
            if (!sub) {
                const converted = urlBase64ToUint8Array(vapidPublic);
                const newSub = await reg.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: converted });
                // send to server
                fetch('{{ route('admin.push.subscribe') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(newSub.toJSON())
                }).catch(()=>{});
            }
        } catch (e) {
            // ignore if not supported or no VAPID key
        }
    })();

    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);
        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

})();

window.PemakaianNotifConfig = {
    csrfToken: "{{ csrf_token() }}",
    routes: {
        checkNew: "{{ route('admin.pemakaian.checkNew') }}",
        list: "{{ route('admin.pemakaian.list') }}",
        pushSubscribe: "{{ route('admin.push.subscribe') }}"
    },
    vapidPublic: "{{ env('VAPID_PUBLIC_KEY', '') }}",
    audioUrl: "{{ asset('assets/notification.mp3') }}",
    initialBadgeCount: {{ $notifikasi ?? 0 }}
};
</script>
<script src="/js/pemakaian-notif.js"></script>

<script>
// Bulk selection, export, and bulk delete handlers
window.selectedPemakaian = window.selectedPemakaian || new Set();

function syncRowCheckboxesWithSet() {
    // mark each visible row checkbox according to the global set
    document.querySelectorAll('.row-checkbox').forEach(cb => {
        const id = cb.value;
        cb.checked = window.selectedPemakaian.has(id);
    });
    // header checkbox state: checked when all visible rows are selected
    const visible = Array.from(document.querySelectorAll('.row-checkbox'));
    const header = document.getElementById('selectAllRows');
    if (!header) return;
    if (visible.length === 0) {
        header.checked = false;
        header.indeterminate = false;
        return;
    }
    const checkedCount = visible.filter(cb => cb.checked).length;
    header.checked = checkedCount === visible.length;
    header.indeterminate = checkedCount > 0 && checkedCount < visible.length;
}

// when table is replaced by AJAX, call this after insertion
function afterTableReload() {
    syncRowCheckboxesWithSet();
}

document.addEventListener('change', function(e){
    if (!e.target) return;
    // header toggle: select/deselect visible rows
    if (e.target && e.target.id === 'selectAllRows') {
        const checked = e.target.checked;
        document.querySelectorAll('.row-checkbox').forEach(cb => {
            cb.checked = checked;
            if (checked) window.selectedPemakaian.add(cb.value);
            else window.selectedPemakaian.delete(cb.value);
        });
        return;
    }

    // individual row checkbox toggled
    if (e.target.classList && e.target.classList.contains('row-checkbox')) {
        const id = e.target.value;
        if (e.target.checked) window.selectedPemakaian.add(id);
        else window.selectedPemakaian.delete(id);
        syncRowCheckboxesWithSet();
        return;
    }
});

function getSelectedIds() {
    // prefer the global set (supports multi-page selection), fall back to DOM
    const fromSet = Array.from(window.selectedPemakaian);
    if (fromSet.length) return fromSet;
    return Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
}

document.addEventListener('click', function(e){
    if (!e.target) return;

    if (e.target.id === 'exportSelected') {
        const ids = getSelectedIds();
        if (!ids.length) return alert('Pilih minimal satu item untuk diexport.');

        // create a form and submit to trigger CSV download
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route('admin.pemakaian.export') }}';
        form.style.display = 'none';
        const token = document.createElement('input');
        token.type = 'hidden'; token.name = '_token'; token.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(token);
        // include current filters (search/status) to keep export consistent with view
        const searchVal = document.getElementById('searchInput') ? document.getElementById('searchInput').value : '';
        const statusVal = document.getElementById('statusInput') ? document.getElementById('statusInput').value : '';
        const sinp = document.createElement('input'); sinp.type = 'hidden'; sinp.name = 'search'; sinp.value = searchVal; form.appendChild(sinp);
        const sinp2 = document.createElement('input'); sinp2.type = 'hidden'; sinp2.name = 'status'; sinp2.value = statusVal; form.appendChild(sinp2);
        ids.forEach(id => {
            const inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = id;
            form.appendChild(inp);
        });
        document.body.appendChild(form);
        form.submit();
        return;
    }

    if (e.target.id === 'deleteSelected') {
        const ids = getSelectedIds();
        if (!ids.length) return alert('Pilih minimal satu item untuk dihapus.');
        if (!confirm('Yakin menghapus ' + ids.length + ' item? Semua foto terkait juga akan dihapus.')) return;

        fetch('{{ route('admin.pemakaian.bulkDelete') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ ids })
        })
        .then(res => res.json())
        .then(resp => {
            if (resp.success) {
                showNotificationToast(resp.message || `${resp.deleted || ids.length} item dihapus`, 'success');
                fetchList();
            } else {
                showNotificationToast(resp.message || 'Gagal menghapus item', 'danger');
            }
        })
        .catch(err => {
            console.error(err);
            showNotificationToast('Terjadi error saat menghapus', 'danger');
        });

        return;
    }
});

// Keep header checkbox in sync with individual row checkboxes
document.addEventListener('change', function(e){
    if (!e.target) return;
    if (e.target.classList && e.target.classList.contains('row-checkbox')) {
        const all = Array.from(document.querySelectorAll('.row-checkbox'));
        if (!all.length) return;
        const checked = all.filter(cb => cb.checked).length === all.length;
        const header = document.getElementById('selectAllRows');
        if (header) header.checked = checked;
    }
});


// Enhance search UX: Enter key, debounce live-search, and status change trigger
(() => {
    const searchInput = document.getElementById('searchInput');
    const statusInput = document.getElementById('statusInput');
    if (!searchInput) return;

    let debounceTimer = null;

    // Enter key submits immediately
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            fetchList();
        }
    });

    // Debounced live search (typing)
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const v = searchInput.value.trim();
            // only search when empty (reset) or 2+ chars to avoid noisy requests
            if (v.length === 0 || v.length >= 2) fetchList();
        }, 400);
    });

    // Status select change triggers search immediately
    if (statusInput) {
        statusInput.addEventListener('change', function() {
            fetchList();
        });
    }

})();
</script>
@endsection
