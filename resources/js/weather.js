/**
 * Depot weather card on the dashboard. The forecast is loaded with fetch() from /weather,
 * which calls the Open-Meteo API on the server and returns the card as HTML.
 */
export function initWeather() {
    const card = document.querySelector('[data-weather]');

    if (!card) {
        return;
    }

    const body = card.querySelector('[data-weather-body]');
    const depotSelect = card.querySelector('[data-weather-depot]');

    const load = async (params) => {
        body.classList.add('opacity-50');

        try {
            const response = await fetch(`${card.dataset.weatherUrl}?${new URLSearchParams(params)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (!response.ok) {
                throw new Error(`Request failed with status ${response.status}`);
            }

            body.innerHTML = await response.text();
        } catch (error) {
            body.innerHTML = '<p class="py-6 text-center text-sm text-slate-500">The weather forecast could not be loaded.</p>';
            console.error(error);
        } finally {
            body.classList.remove('opacity-50');
        }
    };

    depotSelect.addEventListener('change', () => load({ depot: depotSelect.value }));

    load({ depot: depotSelect.value });
}