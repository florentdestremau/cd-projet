import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['column', 'dropzone', 'card'];

    connect() {
        this.cardTargets.forEach(card => this.bindCard(card));
        this.dropzoneTargets.forEach(zone => this.bindZone(zone));
    }

    bindCard(card) {
        card.addEventListener('dragstart', e => {
            card.classList.add('kanban__card--dragging');
            e.dataTransfer.setData('text/plain', card.dataset.reference);
            e.dataTransfer.setData('application/x-csrf', card.dataset.csrf);
            e.dataTransfer.effectAllowed = 'move';
        });
        card.addEventListener('dragend', () => card.classList.remove('kanban__card--dragging'));
    }

    bindZone(zone) {
        zone.addEventListener('dragover', e => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            zone.classList.add('kanban__cards--drop');
        });
        zone.addEventListener('dragleave', () => zone.classList.remove('kanban__cards--drop'));
        zone.addEventListener('drop', async e => {
            e.preventDefault();
            zone.classList.remove('kanban__cards--drop');
            const ref = e.dataTransfer.getData('text/plain');
            const csrf = e.dataTransfer.getData('application/x-csrf');
            const column = zone.closest('[data-kanban-target="column"]');
            const targetStage = column.dataset.stage;

            const card = this.cardTargets.find(c => c.dataset.reference === ref);
            if (!card) return;
            const fromZone = card.closest('[data-kanban-target="dropzone"]');
            if (fromZone === zone) return;

            // optimistic
            zone.appendChild(card);

            const resp = await fetch(`/api/projets/${ref}/etape`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
                body: JSON.stringify({ stage: targetStage }),
            });
            if (!resp.ok) {
                fromZone.appendChild(card);
                const body = await resp.json().catch(() => ({}));
                alert('Impossible de changer l’étape : ' + (body.error || resp.status));
            }
        });
    }
}
