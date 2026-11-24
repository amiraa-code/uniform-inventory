/**
 * Checks a SKU while it is being typed on the Add / Edit item form, and shows
 * "PPE-VEST-XL is available" or "Already used by ..." under the field before the form is submitted.
 */
export function initSkuCheck() {
    document.querySelectorAll('input[data-sku-check]').forEach((input) => {
        const status = input.parentElement.querySelector('[data-sku-status]');
        const originalSku = input.value.trim().toUpperCase();
        let debounceTimer;
        let currentRequest;

        const show = (message, available) => {
            status.textContent = `${available ? '✓' : '✗'} ${message}`;
            status.classList.remove('hidden', 'text-green-700', 'text-red-600');
            status.classList.add(available ? 'text-green-700' : 'text-red-600');
        };

        const check = async () => {
            const sku = input.value.trim().toUpperCase();

            // Nothing to check if the field is empty or still has the item's own SKU.
            if (sku === '' || sku === originalSku) {
                status.classList.add('hidden');
                return;
            }

            currentRequest?.abort();
            currentRequest = new AbortController();

            const params = new URLSearchParams({ sku });

            if (input.dataset.ignoreId) {
                params.set('ignore', input.dataset.ignoreId);
            }

            try {
                const response = await fetch(`${input.dataset.skuCheck}?${params}`, {
                    headers: { Accept: 'application/json' },
                    signal: currentRequest.signal,
                });
                const result = await response.json();
                show(result.message, result.available);
            } catch (error) {
                if (error.name !== 'AbortError') {
                    status.classList.add('hidden');
                }
            }
        };

        input.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(check, 400);
        });
    });
}