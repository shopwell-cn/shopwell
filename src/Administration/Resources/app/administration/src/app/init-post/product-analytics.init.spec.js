import initProductAnalytics from './product-analytics.init';

describe('src/app/init-post/product-analytics.init.ts', () => {
    it('calls Telemetry.init', async () => {
        jest.spyOn(Shopwell.Telemetry, 'initialize');

        await initProductAnalytics();

        expect(Shopwell.Telemetry.initialize).toHaveBeenCalled();
    });
});
