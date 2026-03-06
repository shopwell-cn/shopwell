describe('tabs.store', () => {
    const store = Shopwell.Store.get('tabs');

    beforeEach(() => {
        store.$reset();
    });

    it('has initial state', () => {
        expect(store.tabItems).toStrictEqual({});
    });

    it('should add a new tab item', () => {
        Shopwell.Store.get('tabs').addTabItem({
            label: 'Test',
            positionId: 'examplePositionId',
            componentSectionId: 'exampleComponentSectionId',
        });

        expect(store.tabItems).toStrictEqual({
            examplePositionId: [
                {
                    label: 'Test',
                    componentSectionId: 'exampleComponentSectionId',
                },
            ],
        });
    });
});
