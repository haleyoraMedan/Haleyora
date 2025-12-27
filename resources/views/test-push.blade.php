@extends('layouts.admin')
@section('title', 'Test Push Notification')

@section('content')
<div style="max-width:600px; margin:40px auto; padding:20px; border:1px solid #ccc; border-radius:8px;">
    <h2>Test Push Notification</h2>
    
    <div style="margin:20px 0; padding:15px; background:#f0f0f0; border-radius:4px;">
        <p><strong>Status:</strong></p>
        <ul>
            <li>Pemakaian Pending: <strong>{{ $pending }}</strong></li>
            <li>Subscriptions Tersimpan: <strong>{{ $subscriptions }}</strong></li>
            <li>Queue Driver: <strong>{{ config('queue.default') }}</strong></li>
            <li>VAPID_PUBLIC_KEY: <strong>{{ env('VAPID_PUBLIC_KEY') ? '✓ Set' : '✗ Not Set' }}</strong></li>
        </ul>
    </div>

    <div style="background:#fff3cd; padding:15px; border-radius:4px; margin-bottom:20px;">
        <strong>⚠️ Perhatian:</strong>
        <ol>
            <li>Pastikan <code>php artisan queue:work</code> atau <code>php artisan queue:listen</code> sedang berjalan di terminal lain</li>
            <li>Halaman harus terbuka dan izinkan Notification</li>
            <li>Service Worker harus terdaftar (buka DevTools → Application → Service Workers)</li>
        </ol>
    </div>

    <form method="POST" action="{{ route('test.push.send') }}">
        @csrf
        <button type="submit" style="padding:10px 20px; background:#007bff; color:#fff; border:none; border-radius:4px; cursor:pointer; font-size:16px;">
            Kirim Test Push Notification
        </button>
    </form>

    @if(session('success'))
        <div style="margin-top:20px; padding:15px; background:#d4edda; border:1px solid #c3e6cb; border-radius:4px; color:#155724;">
            ✓ {{ session('success') }}
        </div>
    @endif

    <div style="margin-top:30px; padding:15px; background:#e7f3ff; border-radius:4px; font-size:13px;">
        <strong>Debugging tips:</strong>
        <ul>
            <li>Buka DevTools (F12) → Console: cek error</li>
            <li>Buka DevTools → Application → Service Workers: cek status</li>
            <li>Terminal queue: lihat apakah job `SendPushNotification` dijalankan</li>
            <li>Jika QUEUE_CONNECTION=file, push ke `storage/logs/` atau cek `storage/framework/queue/`</li>
        </ul>
    </div>
</div>

<script>
// Refresh status info setiap 5 detik
setInterval(() => {
    location.reload();
}, 30000);
</script>

@endsection
