/**
 * @sw-package data-services
 *
 * @private
 */
export default function initUsageData(): Promise<void> {
    return new Promise<void>((resolve) => {
        const loginService = Shopwell.Service('loginService');
        const usageDataApiService = Shopwell.Service('usageDataService');

        if (!loginService.isLoggedIn()) {
            Shopwell.Store.get('usageData').resetConsent();

            resolve();

            return;
        }

        usageDataApiService
            .getConsent()
            .then((usageData) => {
                Shopwell.Store.get('usageData').updateConsent(usageData);
            })
            .catch(() => {
                Shopwell.Store.get('usageData').resetConsent();
            })
            .finally(() => {
                resolve();
            });
    });
}
