/**
 * Real-time dashboard: every few seconds (data-auto-refresh="30") the cards and tables are
 * fetched again from the server and swapped in, so stock changes made by other staff appear
 * without anyone refreshing the page. Refreshing pauses while the browser tab is hidden.
 */
export function initDashboardRefresh() {
    const panel = document.querySelector('[data-auto-refresh]');

    if (!panel) {
        return;
    }

    const seconds = Number(panel.dataset.autoRefresh) || 30;
    const timeLabel = document.querySelector('[data-refresh-time]');
    let timer;

    const refresh = async () => {
        try {
            const response = await fetch(panel.dataset.refreshUrl, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            // 401 means the user's session has ended; reloading takes them to the login page.
            if (response.status === 401) {
                window.location.reload();
                return;
            }

            if (response.ok) {
                panel.innerHTML = await response.text();
                timeLabel.textContent = new Date().toLocaleTimeString('en-GB', { timeZone: 'Europe/Malta' });
            }
        } catch (error) {
            // Network problems are ignored; the next refresh will try again.
            console.error(error);
        }
    };

    const start = () => {
        clearInterval(timer);
        timer = setInterval(refresh, seconds * 1000);
    };

    // Don't keep asking the server for updates while nobody is looking at the page.
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            clearInterval(timer);
        } else {
            refresh();
            start();
        }
    });

    start();
}
