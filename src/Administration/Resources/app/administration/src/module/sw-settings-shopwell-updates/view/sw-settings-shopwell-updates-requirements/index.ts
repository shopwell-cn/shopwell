import template from './sw-shopwell-updates-requirements.html.twig';

/**
 * @sw-package framework
 * @private
 */
export default Shopwell.Component.wrapComponentConfig({
    template,

    props: {
        updateInfo: {
            type: Object,
            required: true,
            default: () => {},
        },
        requirements: {
            type: Array,
            required: true,
            default: () => [],
        },
        isLoading: {
            type: Boolean,
        },
    },

    data() {
        return {
            columns: [
                {
                    property: 'message',
                    label: this.$t('sw-settings-shopwell-updates.requirements.columns.message'),
                    rawData: true,
                },
                {
                    property: 'result',
                    label: this.$t('sw-settings-shopwell-updates.requirements.columns.status'),
                    rawData: true,
                },
            ],
        };
    },
});
