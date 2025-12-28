(function(){
    const cfg = window.PemakaianNotifConfig || {};
    const csrfToken = cfg.csrfToken || null;
    const ROUTES = (cfg.routes) || {};
    const AUDIO_URL = cfg.audioUrl || '/assets/notification.mp3';
    let lastCheck = localStorage.getItem('lastCheck') || null;
    let pollingInterval = 7000;
    let badgeCount = cfg.initialBadgeCount || 0;

    function safe(fn){ try{ fn(); }catch(e){} }

    function playSound(){
        try{ const a = new Audio(AUDIO_URL); a.play().catch(()=>{}); }catch(e){}
    }

    function updateBadge(n){
        badgeCount = n;
        const badgeEl = document.getElementById('badgePending');
        if (badgeEl) {
            badgeEl.textContent = `⏳ Pending: ${badgeCount}`;
            badgeEl.style.display = badgeCount > 0 ? 'inline-block' : 'none';
        }
        if (badgeCount > 0) {
            document.title = `(${badgeCount}) Daftar Pemakaian`;
            if (navigator.setAppBadge) navigator.setAppBadge(badgeCount).catch(()=>{});
        } else {
            document.title = 'Daftar Pemakaian Mobil';
            if (navigator.clearAppBadge) navigator.clearAppBadge().catch(()=>{});
        }
    }

    // Polling checkNew endpoint (if available)
    function pollOnce(){
        if (!ROUTES.checkNew) return;
        let url = ROUTES.checkNew + (lastCheck ? ('?last_check=' + encodeURIComponent(lastCheck)) : '');
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }})
            .then(r => r.json())
            .then(data => {
                if (!data) return;
                if (data.new && data.new > 0) {
                    // play sound and refresh list if present
                    playSound();
                    const message = data.new > 1 ? `${data.new} pemakaian baru/update` : `Ada pemakaian baru/update`;
                    // show notification via service worker if possible
                    if (Notification.permission === 'granted' && navigator.serviceWorker && navigator.serviceWorker.controller) {
                        navigator.serviceWorker.ready.then(reg => reg.showNotification('🔔 Notifikasi Pemakaian', { body: message, tag: 'pemakaian-notif' })).catch(()=>{});
                    }
                    // try to refresh admin list if exists
                    safe(() => { if (typeof fetchList === 'function') fetchList(); });
                }
                updateBadge(data.pending ?? 0);
                lastCheck = data.server_time || new Date().toISOString();
                localStorage.setItem('lastCheck', lastCheck);
            }).catch(()=>{});
    }

    // Register service worker and subscribe to push
    (async function registerSWAndSubscribe(){
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) return;
        try{
            const reg = await navigator.serviceWorker.register('/sw.js');

            // listen messages from SW to play sound when push arrives and page open
            navigator.serviceWorker.addEventListener('message', function(event){
                try{
                    if (event.data && event.data.type === 'push'){
                        if (event.data.playSound) playSound();
                        if (event.data.payload && event.data.payload.pending_count !== undefined) updateBadge(event.data.payload.pending_count);
                        safe(() => { if (typeof fetchList === 'function') fetchList(); });
                    }
                }catch(e){}
            });

            const vapidPublic = cfg.vapidPublic || '';
            if (!vapidPublic || !ROUTES.pushSubscribe) return;

            if (Notification.permission !== 'granted') {
                await Notification.requestPermission().catch(()=>{});
            }

            const sub = await reg.pushManager.getSubscription();
            if (!sub) {
                const converted = urlBase64ToUint8Array(vapidPublic);
                const newSub = await reg.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: converted });
                // send to server
                fetch(ROUTES.pushSubscribe, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(newSub.toJSON())
                }).catch(()=>{});
            }
        }catch(e){}
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

    // start polling if checkNew route provided
    if (ROUTES.checkNew) {
        setInterval(pollOnce, pollingInterval);
        pollOnce();
    }

    // ask permission for browser notifications (Notification API only)
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission().catch(()=>{});
    }

    // expose some helper functions globally for admin pages that rely on them
    window.PemakaianNotif = {
        playSound,
        updateBadge
    };
})();
