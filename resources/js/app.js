import './bootstrap';
import { initItemAutocomplete } from './item-autocomplete';
import { initLiveFilters } from './live-filter';
import { initSkuCheck } from './sku-check';
import { initStockMovementForms } from './stock-movement';

initLiveFilters();
initItemAutocomplete();
initSkuCheck();
initStockMovementForms();

// Mobile navigation: show or hide the menu when the hamburger button is pressed.
const menuToggle = document.querySelector('[data-menu-toggle]');
const mobileMenu = document.getElementById('mobile-menu');

menuToggle?.addEventListener('click', () => {
    const isOpen = !mobileMenu.classList.toggle('hidden');
    menuToggle.setAttribute('aria-expanded', String(isOpen));
});

// Modals use the native <dialog> element. Buttons point at a dialog by id.
document.querySelectorAll('[data-open-modal]').forEach((button) => {
    button.addEventListener('click', () => {
        document.getElementById(button.dataset.openModal)?.showModal();
    });
});

document.querySelectorAll('[data-close-modal]').forEach((button) => {
    button.addEventListener('click', () => button.closest('dialog')?.close());
});

// Re-open a modal after a failed submission so the user can see the validation errors.
document.querySelectorAll('dialog[data-open-on-load]').forEach((dialog) => dialog.showModal());

// Ask for confirmation before submitting forms such as delete.
document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!window.confirm(form.dataset.confirm)) {
            event.preventDefault();
        }
    });
});