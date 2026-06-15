import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['sidebar', 'backdrop'];

    open() {
        this.sidebarTarget.classList.add('sidebar--open');
        this.backdropTarget.classList.add('sidebar__backdrop--open');
        document.body.style.overflow = 'hidden';
    }

    close() {
        this.sidebarTarget.classList.remove('sidebar--open');
        this.backdropTarget.classList.remove('sidebar__backdrop--open');
        document.body.style.overflow = '';
    }
}
