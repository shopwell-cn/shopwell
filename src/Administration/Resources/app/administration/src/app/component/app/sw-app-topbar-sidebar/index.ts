import template from './sw-app-topbar-sidebar.html.twig';
import './sw-app-topbar-sidebar.scss';

/**
 * @sw-package framework
 *
 * @private
 */
export default {
    template,

    computed: {
        sidebars() {
            return Shopwell.Store.get('sidebar').sidebars;
        },
    },

    methods: {
        setActiveSidebar(locationId: string) {
            Shopwell.Store.get('sidebar').setActiveSidebar(locationId);
        },
    },
};
