import './sw-shopwell-updates-info.scss';
import template from './sw-shopwell-updates-info.html.twig';

const { Component } = Shopwell;

/**
 * @sw-package framework
 * @private
 */
export default Component.wrapComponentConfig({
    template,

    props: {
        changelog: {
            type: String,
            required: true,
        },
        isLoading: {
            type: Boolean,
        },
    },
});
