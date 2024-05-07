import { Controller } from '@hotwired/stimulus';
import Modal from 'bootstrap/js/dist/modal';
import 'tinymce';

export default class extends Controller {
    modal = null;

    initialize() {
        this.modal = Modal.getOrCreateInstance(this.element);
        window.addEventListener('modal:close', this.closeModal);
        window.addEventListener('modal:open', this.openModal);
        this.element.addEventListener('shown.bs.modal', this.initializeCKEditor);
        this.element.addEventListener('hidden.bs.modal', this.deleteCKEditor);
    }

    closeModal = () => {
        this.modal.hide();
        let backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.parentNode.removeChild(backdrop);
        }
    }

    openModal = () => {
        this.modal.show();
    }

    initializeCKEditor = () => {
        console.log('Quill initialized in modal');
        tinymce.init({
            selector: 'textarea.tyny',
            license_key: 'gpl',
            setup: (editor) => {
                editor.on('init', () => {
                    editor.getContainer().style.transition='border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out';
                });
                editor.on('focus', () => {
                    editor.getContainer().style.boxShadow='0 0 0 .2rem rgba(0, 123, 255, .25)';
                    editor.getContainer().style.borderColor='#80bdff';
                });
                editor.on('blur', () => {
                    editor.getContainer().style.boxShadow='';
                    editor.getContainer().style.borderColor='';
                });
                editor.on('change', () => {
                    let textAreaElement = document.querySelector('#todo_todo');
                    textAreaElement.value = editor.getContent();
                    textAreaElement.dispatchEvent(new Event('change', { bubbles: true }));
                    console.log(editor.getContent());
                });
            },
            plugins: 'autosave',
        });
    }

    deleteCKEditor = () => {
        console.log('Quill initialized in modal');
        tinymce.remove();
    }
}