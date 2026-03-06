/**
 * @sw-package framework
 */
import type { AppModuleDefinition } from 'src/core/service/api/app-modules.service';

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export interface ShopwellAppsState {
    apps: AppModuleDefinition[];
    selectedIds: string[];
}

const shopwellApps = Shopwell.Store.register({
    id: 'shopwellApps',

    state: (): {
        apps: AppModuleDefinition[];
        selectedIds: string[];
    } => ({
        apps: [],
        selectedIds: [],
    }),
});

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export type ShopwellApps = ReturnType<typeof shopwellApps>;

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default shopwellApps;
