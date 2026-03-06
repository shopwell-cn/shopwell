/**
 * @sw-package innovation
 */

import useUsageData from '../composables/use-usage-data';

const usageData = Shopwell.Store.register('usageData', useUsageData);

/**
 * @private
 */
export type UsageData = ReturnType<typeof usageData>;

/**
 * @private
 */
export default usageData;
