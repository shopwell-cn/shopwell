import template from './sw-landing-page-view.html.twig';

const { Mixin } = Shopwell;

/**
 * @sw-package discovery
 */
// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default {
    template,

    inject: ['acl'],

    mixins: [
        Mixin.getByName('placeholder'),
    ],

    props: {
        isLoading: {
            type: Boolean,
            required: true,
            default: false,
        },
    },

    computed: {
        landingPage() {
            return Shopwell.Store.get('swCategoryDetail').landingPage;
        },

        cmsPage() {
            return Shopwell.Store.get('cmsPage').currentPage;
        },
    },
};
