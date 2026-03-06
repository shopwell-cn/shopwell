/**
 * @sw-package framework
 */

import { mount } from '@vue/test-utils';

describe('src/app/component/meteor-wrapper/mt-datepicker', () => {
    beforeAll(() => {
        Shopwell.Store.get('system').registerAdminLocale('de-DE');
        Shopwell.Store.get('system').registerAdminLocale('en-GB');
    });

    beforeEach(() => {
        Shopwell.Store.get('session').setCurrentUser({
            firstName: 'John',
            lastName: 'Doe',
            timeZone: 'Europe/Berlin',
        });

        Shopwell.Store.get('session').setAdminLocale('de-DE');
    });

    it('should use the user timeZone', async () => {
        const wrapper = mount(await wrapTestComponent('mt-datepicker', { sync: true }));

        expect(wrapper.find('[data-testid="time-zone-hint"]').text()).toBe('Europe/Berlin');
    });

    it('should use the user locale (de)', async () => {
        const wrapper = mount(await wrapTestComponent('mt-datepicker', { sync: true }));

        // Click on input to open datepicker
        await wrapper.find('[data-test-id="dp-input"]').trigger('click');

        // Expect german locale to be used
        expect(document.body.textContent).toContain('MoDiMiDoFrSaSo');
    });

    it('should use the user locale (en)', async () => {
        // Set the user locale to english
        Shopwell.Store.get('session').setAdminLocale('en-GB');

        const wrapper = mount(await wrapTestComponent('mt-datepicker', { sync: true }));

        // Click on input to open datepicker
        await wrapper.find('[data-test-id="dp-input"]').trigger('click');

        // Expect german locale to be used
        expect(document.body.textContent).toContain('MoTuWeThFrSaSu');
    });

    it('should use custom format based on currentLocale (de)', async () => {
        const wrapper = mount(await wrapTestComponent('mt-datepicker', { sync: true }), {
            props: {
                modelValue: '2023-10-01T00:00:00+02:00',
            },
        });

        // Click on input to open datepicker
        await wrapper.find('[data-test-id="dp-input"]').trigger('click');

        // Expect german locale to be used
        expect(wrapper.find('[data-test-id="dp-input"]').element.value).toBe('01.10.2023, 00:00');
    });

    it('should use custom format based on currentLocale (en)', async () => {
        // Set the user locale to english
        Shopwell.Store.get('session').setAdminLocale('en-GB');

        const wrapper = mount(await wrapTestComponent('mt-datepicker', { sync: true }), {
            props: {
                modelValue: '2023-10-01T00:00:00+02:00',
            },
        });

        // Click on input to open datepicker
        await wrapper.find('[data-test-id="dp-input"]').trigger('click');

        // Expect german locale to be used
        expect(wrapper.find('[data-test-id="dp-input"]').element.value).toBe('01/10/2023, 00:00');
    });
});
