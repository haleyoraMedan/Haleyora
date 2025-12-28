/* Service Worker untuk Web Push Notifications */

self.addEventListener('push', function(event) {
    let data = {};
    try { 
        data = event.data.json(); 
    } catch (e) { 
        data = { title: 'Notifikasi', body: 'Ada pembaruan', sound: true }; 
    }

    const title = data.title || 'Notifikasi Baru';
    const options = {
        body: data.body || '',
        tag: data.tag || 'pemakaian-notif',
        data: {
            url: data.url || '/',
            pending: data.pending_count || 0,
            sound: data.sound || true
        },
        renotify: true,
        requireInteraction: true,
        badge: '/assets/notification-badge.png',
        icon: '/assets/notification.png',
        vibrate: [200, 100, 200]
    };

    // Show notification and inform open clients to play sound (if any)
    event.waitUntil(
        Promise.resolve()
            .then(() => self.registration.showNotification(title, options))
            .then(() => clients.matchAll({ includeUncontrolled: true, type: 'window' }))
            .then(function(clientList) {
                clientList.forEach(function(client) {
                    try {
                        client.postMessage({
                            type: 'push',
                            payload: data,
                            playSound: !!data.sound
                        });
                    } catch (e) {}
                });
            })
    );
});

self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    const url = event.notification.data && event.notification.data.url ? event.notification.data.url : '/admin/pemakaian';
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function(clientList) {
            for (var i=0; i<clientList.length; i++){
                var client = clientList[i];
                if (client.url === url && 'focus' in client) return client.focus();
            }
            if (clients.openWindow) return clients.openWindow(url);
        })
    );
});
