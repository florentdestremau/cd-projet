import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['items'];
    static values = { prototype: String };

    add(event) {
        event.preventDefault();
        const idx = this.itemsTarget.children.length;
        const html = this.prototypeValue.replace(/__name__/g, idx);
        const wrapper = document.createElement('div');
        wrapper.className = 'form__collection-row';
        wrapper.innerHTML = html;
        this.itemsTarget.appendChild(wrapper);
    }
}
