/**
 * Live search and filtering for list pages.
 *
 * A filter form with data-live-filter="#results" sends its values with fetch()
 * whenever they change, and swaps only the results area with the HTML the server returns.
 * The URL is updated too, so refreshing or sharing the page keeps the same filters.
 */
export function initLiveFilters() {
    document.querySelectorAll('form[data-live-filter]').forEach((form) => {
        const results = document.querySelector(form.dataset.liveFilter);
        let debounceTimer;
        let currentRequest;

        const urlFromForm = () => {
            const params = new URLSearchParams(new FormData(form));

            // Leave empty fields out of the URL, e.g. ?search=vest rather than ?search=vest&category=&status=
            [...params.keys()].forEach((key) => {
                if (params.get(key) === '') {
                    params.delete(key);
                }
            });

            const query = params.toString();

            return query ? `${form.action}?${query}` : form.action;
        };

        const load = async (url) => {
            // Cancel the previous request if the user is still typing.
            currentRequest?.abort();
            const request = new AbortController();
            currentRequest = request;

            results.setAttribute('aria-busy', 'true');
            results.classList.add('opacity-50');

            try {
                const response = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    signal: request.signal,
                });

                if (!response.ok) {
                    throw new Error(`Request failed with status ${response.status}`);
                }

                results.innerHTML = await response.text();
                window.history.replaceState(null, '', url);
            } catch (error) {
                if (error.name !== 'AbortError') {
                    // If anything goes wrong, fall back to a normal page load.
                    window.location.href = url;
                }
            } finally {
                if (currentRequest === request) {
                    results.removeAttribute('aria-busy');
                    results.classList.remove('opacity-50');
                }
            }
        };

        // Wait until the user stops typing for 300ms before searching.
        form.addEventListener('input', (event) => {
            if (event.target.matches('input[type="search"]')) {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => load(urlFromForm()), 300);
            }
        });

        form.addEventListener('change', (event) => {
            if (event.target.matches('select')) {
                load(urlFromForm());
            }
        });

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            clearTimeout(debounceTimer);
            load(urlFromForm());
        });

        // Pagination links inside the results also load without a full page refresh.
        results.addEventListener('click', (event) => {
            const pageLink = event.target.closest('nav a');

            if (pageLink) {
                event.preventDefault();
                load(pageLink.href);
            }
        });
    });
}