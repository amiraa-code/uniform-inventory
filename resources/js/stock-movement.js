import { showToast } from './toast';

/**
 * Sends stock movement forms (data-ajax-form) with fetch() instead of a full page reload.
 *
 * On success the server returns JSON with the item's new stock level, and every element on the
 * page that shows that item's stock is updated. On a validation error (HTTP 422) the messages
 * are shown under the matching fields.
 */
export function initStockMovementForms() {
    document.querySelectorAll('form[data-ajax-form]').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const submitButton = form.querySelector('button[type="submit"]');
            const buttonText = submitButton.textContent;
            submitButton.disabled = true;
            submitButton.textContent = 'Saving…';
            clearErrors(form);

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    // FormData includes the hidden _token field added by @csrf.
                    body: new FormData(form),
                });

                const data = await response.json().catch(() => ({}));

                if (response.status === 422) {
                    showErrors(form, data.errors ?? {});
                    return;
                }

                if (response.status === 419 || response.status === 401) {
                    showToast('Your session has expired. Please refresh the page and try again.', 'error');
                    return;
                }

                if (!response.ok) {
                    throw new Error(data.message ?? `Request failed with status ${response.status}`);
                }

                updateItemOnPage(data);
                resetForm(form);
                form.closest('dialog')?.close();
                showToast(data.message);
                form.dispatchEvent(new CustomEvent('stock-movement:saved', { bubbles: true, detail: data }));
            } catch (error) {
                showToast('The movement could not be saved. Please try again.', 'error');
                console.error(error);
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = buttonText;
            }
        });
    });
}

function updateItemOnPage({ item, row_html: rowHtml }) {
    document.querySelectorAll(`[data-item-stock="${item.id}"]`).forEach((element) => {
        element.textContent = item.stock_quantity;

        if (element.hasAttribute('data-stock-colour')) {
            element.classList.toggle('text-red-600', item.stock_status === 'out');
            element.classList.toggle('text-amber-600', item.stock_status === 'low');
        }
    });

    document.querySelectorAll(`[data-item-badge="${item.id}"]`).forEach((element) => {
        element.innerHTML = item.badge_html;
    });

    document.querySelectorAll(`[data-item-value="${item.id}"]`).forEach((element) => {
        element.textContent = item.stock_value;
    });

    const historyRows = document.querySelector(`[data-history-rows="${item.id}"]`);

    if (historyRows) {
        historyRows.querySelector('[data-empty-row]')?.remove();
        historyRows.insertAdjacentHTML('afterbegin', rowHtml);
        historyRows.firstElementChild.classList.add('bg-amber-50');
    }
}

// Keep the chosen item, but clear the quantity, reference and notes for the next entry.
function resetForm(form) {
    form.querySelectorAll('input[name="quantity"], input[name="reference"], textarea[name="notes"]').forEach((field) => {
        field.value = '';
    });
}

function clearErrors(form) {
    form.querySelectorAll('.form-error').forEach((error) => error.remove());
}

function showErrors(form, errors) {
    Object.entries(errors).forEach(([field, messages]) => {
        const input = form.querySelector(`[name="${field}"]`);
        const container = input?.closest('fieldset') ?? input?.parentElement ?? form;
        const error = document.createElement('p');
        error.className = 'form-error';
        error.textContent = messages[0];
        container.append(error);
    });

    // Move the cursor to the first field with an error.
    const firstField = Object.keys(errors)[0];
    form.querySelector(`[name="${firstField}"]:not([type="hidden"])`)?.focus();
}