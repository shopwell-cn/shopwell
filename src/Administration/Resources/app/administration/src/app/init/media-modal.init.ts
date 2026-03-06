/**
 * @sw-package framework
 *
 * @private
 */
export default function initializeMediaModal(): void {
    // eslint-disable-next-line @typescript-eslint/require-await
    Shopwell.ExtensionAPI.handle('uiMediaModalOpen', (modalConfig) => {
        Shopwell.Store.get('mediaModal').openModal(modalConfig);
    });

    // eslint-disable-next-line @typescript-eslint/require-await
    Shopwell.ExtensionAPI.handle('uiMediaModalOpenSaveMedia', (saveModalConfig) => {
        Shopwell.Store.get('mediaModal').openSaveModal(saveModalConfig);
    });
}
