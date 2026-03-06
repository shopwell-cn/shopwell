/**
 * @sw-package framework
 */

import './acl';

const { Component, Module } = Shopwell;

/** @private */
Component.register(
    'sw-settings-shopwell-updates-requirements',
    () => import('./view/sw-settings-shopwell-updates-requirements'),
);
/** @private */
Component.register('sw-settings-shopwell-updates-plugins', () => import('./view/sw-settings-shopwell-updates-plugins'));
/** @private */
Component.register('sw-settings-shopwell-updates-info', () => import('./view/sw-settings-shopwell-updates-info'));
/** @private */
Component.register('sw-settings-shopwell-updates-index', () => import('./page/sw-settings-shopwell-updates-index'));
/** @private */
Component.register('sw-settings-shopwell-updates-wizard', () => import('./page/sw-settings-shopwell-updates-wizard'));

/**
 * @private
 */
Module.register('sw-settings-shopwell-updates', {
    type: 'core',
    name: 'settings-shopwell-updates',
    title: 'sw-settings-shopwell-updates.general.emptyTitle',
    description: 'sw-settings-shopwell-updates.general.emptyTitle',
    version: '1.0.0',
    targetVersion: '1.0.0',
    color: '#9AA8B5',
    icon: 'solid-cog',
    favicon: 'icon-module-settings.png',

    routes: {
        wizard: {
            component: 'sw-settings-shopwell-updates-wizard',
            path: 'wizard',
            meta: {
                parentPath: 'sw.settings.index.system',
                privilege: 'system.core_update',
            },
        },
    },

    settingsItem: {
        privilege: 'system.core_update',
        group: 'system',
        to: 'sw.settings.shopwell.updates.wizard',
        icon: 'regular-sync',
    },
});
