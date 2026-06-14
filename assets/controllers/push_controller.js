import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['status', 'enable', 'disable'];
    static values = { vapidKey: String, subscribeUrl: String, unsubscribeUrl: String };

    async connect() {
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
            this.statusTarget.textContent = 'Statut : non supporté par ce navigateur.';
            return;
        }

        await navigator.serviceWorker.register('/sw.js');
        const reg = await navigator.serviceWorker.ready;
        const sub = await reg.pushManager.getSubscription();

        if (Notification.permission === 'denied') {
            this.statusTarget.textContent = 'Statut : permission refusée (à débloquer dans les réglages navigateur).';
            return;
        }
        if (sub) {
            this.statusTarget.textContent = 'Statut : notifications actives.';
            this.disableTarget.hidden = false;
        } else {
            this.statusTarget.textContent = 'Statut : notifications inactives.';
            this.enableTarget.hidden = false;
        }
    }

    async enable() {
        const reg = await navigator.serviceWorker.ready;
        const sub = await reg.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: this.urlBase64ToUint8Array(this.vapidKeyValue),
        });
        await fetch(this.subscribeUrlValue, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(sub.toJSON()),
        });
        this.statusTarget.textContent = 'Statut : notifications actives.';
        this.enableTarget.hidden = true;
        this.disableTarget.hidden = false;
    }

    async disable() {
        const reg = await navigator.serviceWorker.ready;
        const sub = await reg.pushManager.getSubscription();
        if (sub) {
            await fetch(this.unsubscribeUrlValue, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ endpoint: sub.endpoint }),
            });
            await sub.unsubscribe();
        }
        this.statusTarget.textContent = 'Statut : notifications inactives.';
        this.enableTarget.hidden = false;
        this.disableTarget.hidden = true;
    }

    urlBase64ToUint8Array(base64) {
        const padding = '='.repeat((4 - base64.length % 4) % 4);
        const b64 = (base64 + padding).replace(/-/g, '+').replace(/_/g, '/');
        const raw = atob(b64);
        return Uint8Array.from([...raw].map(c => c.charCodeAt(0)));
    }
}
