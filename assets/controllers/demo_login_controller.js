import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['form', 'email', 'password'];
    static values = { password: String };

    fillAndSubmit(event) {
        event.preventDefault();
        const button = event.currentTarget;
        const email = button.dataset.email;
        if (!email) return;

        this.emailTarget.value = email;
        this.passwordTarget.value = this.passwordValue || 'demo';

        // requestSubmit() pour que le listener csrf-protection (capture submit) puisse générer le token
        this.formTarget.requestSubmit();
    }
}
