/**
 * @sw-package framework
 */
import { useShopwellServicesStore } from '../store/shopwell-services.store';

/* eslint-disable import/prefer-default-export */
/**
 * @private
 */
export async function grantPermissions() {
    const shopwellServiceStore = useShopwellServicesStore();
    const currentRevision = shopwellServiceStore.currentRevision?.revision;

    if (!currentRevision) {
        throw new Error('No revision available');
    }

    await Shopwell.Service('shopwellServicesService').acceptRevision(currentRevision);

    window.location.reload();
}

/**
 * @private
 */
export async function revokePermissions() {
    await Shopwell.Service('shopwellServicesService').revokePermissions();

    window.location.reload();
}
/* eslint-enable import/prefer-default-export */
