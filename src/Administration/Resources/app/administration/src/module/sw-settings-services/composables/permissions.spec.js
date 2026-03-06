import { createPinia, setActivePinia } from 'pinia';
import { revokePermissions, grantPermissions } from './permissions';
import { useShopwellServicesStore } from '../store/shopwell-services.store';

describe('src/module/sw-settings-services/composables/permissions', () => {
    let originalLocation;

    beforeAll(() => {
        Shopwell.Service().register('shopwellServicesService', () => ({
            acceptRevision: jest.fn(),
            revokePermissions: jest.fn(),
        }));

        originalLocation = window.location;

        Object.defineProperty(window, 'location', { configurable: true, value: { reload: jest.fn() } });
    });

    beforeEach(() => {
        setActivePinia(createPinia());
        useShopwellServicesStore();
    });

    afterAll(() => {
        Object.defineProperty(window, 'location', { configurable: true, value: originalLocation });
    });

    it('calls shopwell service and reloads', async () => {
        const shopwellServicesStore = useShopwellServicesStore();

        shopwellServicesStore.revisions = {
            'latest-revision': '2025-06-25',
            'available-revisions': [
                {
                    revision: '2025-06-25',
                    links: {},
                },
            ],
        };

        await grantPermissions();

        expect(Shopwell.Service('shopwellServicesService').acceptRevision).toHaveBeenCalledWith('2025-06-25');
        expect(window.location.reload).toHaveBeenCalled();
    });

    it('throws exception if there is no current revision', async () => {
        await expect(() => grantPermissions()).rejects.toThrow(new Error('No revision available'));
    });

    it('calls shopwell service to revoke permissions and reloads', async () => {
        await revokePermissions();

        expect(Shopwell.Service('shopwellServicesService').revokePermissions).toHaveBeenCalled();
        expect(window.location.reload).toHaveBeenCalled();
    });
});
