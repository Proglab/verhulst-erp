import { Controller } from '@hotwired/stimulus';
import Modal from 'bootstrap/js/dist/modal';

export default class extends Controller {
    modal = null;

    initialize() {
        this.modal = Modal.getOrCreateInstance(this.element);
        window.addEventListener('modal:close', this.closeModal);
        window.addEventListener('modal:open', this.openModal);
        this.element.addEventListener('shown.bs.modal', this.initializeCKEditor);
    }

    closeModal = () => {
        this.modal.hide();
        let backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.parentNode.removeChild(backdrop);
        }
    }

    openModal = () => {
        console.log('openModal');
        this.modal.show();
    }
}