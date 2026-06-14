self.addEventListener('push', event => {
    if (!event.data) return;
    let data = {};
    try { data = event.data.json(); } catch (_) { data = { title: 'Notification', body: event.data.text() }; }
    const title = data.title || 'Maison';
    const options = {
        body: data.body || '',
        data: { url: data.url || '/' },
        icon: '/favicon.svg',
        badge: '/favicon.svg',
    };
    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', event => {
    event.notification.close();
    const url = (event.notification.data && event.notification.data.url) || '/';
    event.waitUntil(clients.openWindow(url));
});
