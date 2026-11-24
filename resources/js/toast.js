/**
 * Shows a short message at the top of the screen, then removes it after a few seconds.
 * Used after fetch() requests, where there is no page reload to show a flash message.
 */
export function showToast(message, type = 'success') {
    const region = document.getElementById('toast-region');

    if (!region) {
        return;
    }

    const toast = document.createElement('div');
    toast.setAttribute('role', type === 'error' ? 'alert' : 'status');
    toast.className = [
        'pointer-events-auto w-full max-w-md rounded-md border px-4 py-3 text-sm shadow-lg transition-opacity duration-300',
        type === 'error' ? 'border-red-200 bg-red-50 text-red-800' : 'border-green-200 bg-green-50 text-green-800',
    ].join(' ');
    toast.textContent = message;

    region.append(toast);

    setTimeout(() => {
        toast.classList.add('opacity-0');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}