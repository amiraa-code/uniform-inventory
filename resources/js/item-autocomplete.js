/**
 * Item autocomplete for the Record Movement form.
 *
 * As the user types, matching items are fetched from /items/search and listed under the box.
 * Choosing one (click, tap, or arrow keys + Enter) puts its id in the hidden item_id field.
 */
export function initItemAutocomplete() {
    document.querySelectorAll('[data-item-autocomplete]').forEach((wrapper) => {
        const input = wrapper.querySelector('input[type="search"]');
        const hiddenId = wrapper.querySelector('input[name="item_id"]');
        const list = wrapper.querySelector('[role="listbox"]');
        const selectedInfo = wrapper.querySelector('[data-selected-item]');
        const stockLabel = selectedInfo.querySelector('[data-item-stock]');

        let results = [];
        let activeIndex = -1;
        let debounceTimer;
        let currentRequest;

        const close = () => {
            list.classList.add('hidden');
            input.setAttribute('aria-expanded', 'false');
            input.removeAttribute('aria-activedescendant');
            activeIndex = -1;
        };

        const render = () => {
            list.innerHTML = '';

            if (results.length === 0) {
                list.innerHTML = '<li class="px-3 py-2 text-slate-500">No matching items</li>';
            }

            results.forEach((item, index) => {
                const option = document.createElement('li');
                option.id = `item-option-${item.id}`;
                option.setAttribute('role', 'option');
                option.setAttribute('aria-selected', String(index === activeIndex));
                option.className = `flex cursor-pointer items-center justify-between gap-3 px-3 py-2 ${index === activeIndex ? 'bg-amber-50' : 'hover:bg-slate-50'}`;

                const label = document.createElement('span');
                label.textContent = item.label;

                const stock = document.createElement('span');
                stock.textContent = `${item.stock_quantity} in stock`;
                stock.className = `shrink-0 text-xs font-semibold ${{ out: 'text-red-600', low: 'text-amber-600' }[item.stock_status] ?? 'text-green-700'}`;

                option.append(label, stock);

                // mousedown fires before the input loses focus, so the choice isn't lost when the list closes.
                option.addEventListener('mousedown', (event) => {
                    event.preventDefault();
                    choose(item);
                });

                list.append(option);
            });

            list.classList.remove('hidden');
            input.setAttribute('aria-expanded', 'true');

            if (activeIndex >= 0) {
                input.setAttribute('aria-activedescendant', `item-option-${results[activeIndex].id}`);
                list.children[activeIndex].scrollIntoView({ block: 'nearest' });
            }
        };

        const choose = (item) => {
            input.value = item.label;
            hiddenId.value = item.id;
            stockLabel.textContent = item.stock_quantity;
            stockLabel.dataset.itemStock = item.id;
            selectedInfo.classList.remove('hidden');
            wrapper.querySelector('.form-error')?.remove();
            close();
        };

        const search = async (term) => {
            currentRequest?.abort();
            currentRequest = new AbortController();

            try {
                const url = `${wrapper.dataset.searchUrl}?q=${encodeURIComponent(term)}`;
                const response = await fetch(url, {
                    headers: { Accept: 'application/json' },
                    signal: currentRequest.signal,
                });

                results = await response.json();
                activeIndex = -1;
                render();
            } catch (error) {
                if (error.name !== 'AbortError') {
                    console.error(error);
                }
            }
        };

        input.addEventListener('input', () => {
            // Typing again means the previous choice no longer applies.
            hiddenId.value = '';
            selectedInfo.classList.add('hidden');
            clearTimeout(debounceTimer);

            const term = input.value.trim();

            if (term.length < 2) {
                close();
                return;
            }

            debounceTimer = setTimeout(() => search(term), 250);
        });

        input.addEventListener('keydown', (event) => {
            const isOpen = !list.classList.contains('hidden') && results.length > 0;

            if (event.key === 'ArrowDown' && isOpen) {
                event.preventDefault();
                activeIndex = (activeIndex + 1) % results.length;
                render();
            } else if (event.key === 'ArrowUp' && isOpen) {
                event.preventDefault();
                activeIndex = (activeIndex - 1 + results.length) % results.length;
                render();
            } else if (event.key === 'Enter' && isOpen && activeIndex >= 0) {
                // Choose the highlighted item instead of submitting the form.
                event.preventDefault();
                choose(results[activeIndex]);
            } else if (event.key === 'Escape') {
                close();
            }
        });

        input.addEventListener('blur', close);
    });
}