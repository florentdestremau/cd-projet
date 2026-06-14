import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'suggestions'];
    static values = { users: Array };

    connect() {
        this.inputTarget.addEventListener('input', this.onInput.bind(this));
        this.inputTarget.addEventListener('keydown', this.onKeyDown.bind(this));
        this.activeIndex = 0;
        this.matches = [];
    }

    onInput() {
        const value = this.inputTarget.value;
        const caret = this.inputTarget.selectionStart;
        const head = value.slice(0, caret);
        const match = head.match(/@([a-zA-Z]*)$/);
        if (!match) {
            this.hide();
            return;
        }

        const query = match[1].toLowerCase();
        this.matches = this.usersValue.filter(u => u.handle.startsWith(query)).slice(0, 6);
        this.activeIndex = 0;
        this.render();
    }

    render() {
        if (this.matches.length === 0) {
            this.hide();
            return;
        }
        this.suggestionsTarget.style.display = 'block';
        this.suggestionsTarget.innerHTML = this.matches.map((u, i) => `
            <button type="button" data-index="${i}"
                    style="display: block; width: 100%; text-align: left; padding: var(--space-2) var(--space-3); border: none; background: ${i === this.activeIndex ? 'var(--color-paper)' : 'transparent'}; cursor: pointer; font-size: var(--text-sm);">
                <strong>@${u.handle}</strong> <span style="color: var(--color-ink-muted);">— ${u.label}</span>
            </button>
        `).join('');
        this.suggestionsTarget.querySelectorAll('button').forEach(btn => {
            btn.addEventListener('mousedown', e => {
                e.preventDefault();
                this.choose(parseInt(btn.dataset.index, 10));
            });
        });
    }

    onKeyDown(event) {
        if (this.suggestionsTarget.style.display === 'none' || this.matches.length === 0) return;
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            this.activeIndex = (this.activeIndex + 1) % this.matches.length;
            this.render();
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            this.activeIndex = (this.activeIndex - 1 + this.matches.length) % this.matches.length;
            this.render();
        } else if (event.key === 'Enter' || event.key === 'Tab') {
            event.preventDefault();
            this.choose(this.activeIndex);
        } else if (event.key === 'Escape') {
            this.hide();
        }
    }

    choose(index) {
        const user = this.matches[index];
        if (!user) return;
        const value = this.inputTarget.value;
        const caret = this.inputTarget.selectionStart;
        const head = value.slice(0, caret).replace(/@[a-zA-Z]*$/, `@${user.handle} `);
        const tail = value.slice(caret);
        this.inputTarget.value = head + tail;
        this.inputTarget.focus();
        const pos = head.length;
        this.inputTarget.setSelectionRange(pos, pos);
        this.hide();
    }

    hide() {
        this.suggestionsTarget.style.display = 'none';
        this.matches = [];
    }
}
