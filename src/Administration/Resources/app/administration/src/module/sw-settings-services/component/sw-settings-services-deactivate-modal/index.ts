import template from './sw-settings-services-deactivate-modal.html.twig';
import './sw-settings-services-deactivate-modal.scss';
import extractError from '../../composables/extract-error';

/**
 * @sw-package framework
 * @private
 */
export default Shopwell.Component.wrapComponentConfig({
    name: 'sw-settings-services-deactivate-modal',
    template,

    props: {
        feedbackLink: {
            type: String,
        },
    },

    data() {
        return {
            isLoading: false,
        };
    },

    methods: {
        async disableServices(done: () => void) {
            this.isLoading = true;

            try {
                const shopwellServicesService = Shopwell.Service('shopwellServicesService');

                await shopwellServicesService.disableAllServices();

                window.location.reload();
            } catch (exceptionResponse) {
                Shopwell.Store.get('notification').createNotification({
                    title: this.$t('global.default.error'),
                    variant: 'critical',
                    message: extractError(exceptionResponse),
                    autoClose: false,
                });
            } finally {
                this.isLoading = false;
            }

            done();
        },
    },
});
