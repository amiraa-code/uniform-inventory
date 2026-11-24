/**
 * Draws a QR code containing the item's SKU on its page, so a printed label can be
 * scanned with the camera on the Scan page. The qrcode library is only downloaded
 * on pages that have a label.
 */
export async function initQrLabels() {
    const canvases = document.querySelectorAll('canvas[data-qr-code]');

    if (canvases.length === 0) {
        return;
    }

    const { default: QRCode } = await import('qrcode');

    canvases.forEach((canvas) => {
        const sku = canvas.dataset.qrCode;

        QRCode.toCanvas(canvas, sku, { width: 224, margin: 1 }, (error) => {
            if (error) {
                console.error(error);
                return;
            }

            // The download link saves the same QR code as a PNG image, ready to print.
            const link = document.querySelector(`[data-qr-download="${sku}"]`);

            if (link) {
                link.href = canvas.toDataURL('image/png');
                link.download = `label-${sku}.png`;
            }
        });
    });
}