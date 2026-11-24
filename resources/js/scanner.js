/**
 * Scan page: reads a QR code or barcode from the device camera (or from a photo) and opens
 * the item with that SKU. Uses the html5-qrcode library, which is only downloaded on this page.
 */
export async function initScanner() {
    const page = document.querySelector('[data-scanner]');

    if (!page) {
        return;
    }

    const status = page.querySelector('[data-scanner-status]');
    const startButton = page.querySelector('[data-scanner-start]');
    const stopButton = page.querySelector('[data-scanner-stop]');
    const fileInput = page.querySelector('[data-scanner-file]');
    const manualForm = page.querySelector('[data-scanner-manual]');

    const { Html5Qrcode } = await import('html5-qrcode');
    const scanner = new Html5Qrcode('scanner-view');
    let handling = false;

    const setStatus = (text, isError = false) => {
        status.textContent = text;
        status.classList.toggle('text-red-600', isError);
        status.classList.toggle('text-slate-500', !isError);
    };

    const stopCamera = async () => {
        if (scanner.isScanning) {
            await scanner.stop();
        }

        startButton.classList.remove('hidden');
        stopButton.classList.add('hidden');
    };

    // Look the SKU up on the server and go to the item's page.
    const openItem = async (code) => {
        if (handling) {
            return;
        }

        handling = true;
        const sku = code.trim().toUpperCase();
        setStatus(`Looking up ${sku}…`);

        try {
            const response = await fetch(`${page.dataset.lookupUrl}?${new URLSearchParams({ sku })}`, {
                headers: { Accept: 'application/json' },
            });
            const result = await response.json();

            if (!response.ok) {
                setStatus(result.message ?? 'Item not found.', true);
                return;
            }

            // A short vibration confirms the scan on phones that support it.
            navigator.vibrate?.(150);
            await stopCamera();
            setStatus(`Found ${result.label}. Opening…`);
            window.location.href = result.url;
        } catch (error) {
            setStatus('The item could not be looked up. Please try again.', true);
            console.error(error);
        } finally {
            handling = false;
        }
    };

    startButton.addEventListener('click', async () => {
        setStatus('Starting camera…');

        try {
            await scanner.start(
                { facingMode: 'environment' }, // the back camera on phones
                { fps: 10, qrbox: { width: 220, height: 220 } },
                (decodedText) => openItem(decodedText),
                () => {}, // called for every frame without a code; nothing to do
            );
            startButton.classList.add('hidden');
            stopButton.classList.remove('hidden');
            setStatus('Point the camera at a label.');
        } catch (error) {
            const blocked = String(error).includes('NotAllowedError');
            setStatus(blocked
                ? 'Camera access was blocked. Allow the camera in your browser settings, or scan from a photo.'
                : 'No camera could be started on this device. Try scanning from a photo instead.', true);
            console.error(error);
        }
    });

    stopButton.addEventListener('click', async () => {
        await stopCamera();
        setStatus('Camera is off.');
    });

    fileInput.addEventListener('change', async () => {
        const file = fileInput.files[0];

        if (!file) {
            return;
        }

        await stopCamera();
        setStatus('Reading photo…');

        try {
            const decodedText = await scanner.scanFile(file, false);
            await openItem(decodedText);
        } catch {
            setStatus('No QR code or barcode was found in that photo.', true);
        }

        fileInput.value = '';
    });

    manualForm.addEventListener('submit', (event) => {
        event.preventDefault();
        openItem(manualForm.querySelector('input').value);
    });
}