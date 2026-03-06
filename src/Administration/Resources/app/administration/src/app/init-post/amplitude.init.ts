/**
 * @sw-package framework
 */
import createConsentEventHandler from 'src/core/consent/handlers';
import useConsentStore from 'src/core/consent/consent.store';
import type * as AmplitudeClient from '@amplitude/analytics-browser';
import { computed, watch } from 'vue';
import createTelemetryEventHandler from './amplitude.telemetry-handlers';

type AmplitudeModule = typeof AmplitudeClient;
type AnonymousAmplitudeClient = ReturnType<AmplitudeModule['createInstance']>;
type PrivacyAmplitudeClient = ReturnType<AmplitudeModule['createInstance']>;

let stopTelemetryConsentWatch: (() => void) | null = null;

/**
 * @private
 */
export default async function (): Promise<void> {
    const analyticsGatewayUrl = Shopwell.Store.get('context').app.analyticsGatewayUrl;

    if (!analyticsGatewayUrl) {
        return;
    }

    const consentStore = useConsentStore();
    const isTelemetryConsentAccepted = computed((): boolean => {
        try {
            return consentStore.isAccepted('product_analytics');
        } catch {
            return false;
        }
    });
    const amplitude = await import('@amplitude/analytics-browser');
    const anonymousAmplitude = amplitude.createInstance();
    const privacyAmplitude = amplitude.createInstance();
    const pushTelemetryEventToAmplitude = createTelemetryEventHandler(amplitude);
    let isTelemetryInitialized = false;
    let isTelemetryListenerRegistered = false;

    registerAnonymousLogoutListener(anonymousAmplitude);
    initAnonymousAmplitude(anonymousAmplitude, analyticsGatewayUrl);
    initPrivacyAmplitude(privacyAmplitude, analyticsGatewayUrl);

    const pushConsentEventToAmplitude = createConsentEventHandler(anonymousAmplitude);

    // eslint-disable-next-line listeners/no-missing-remove-event-listener
    Shopwell.Utils.EventBus.on('consent', pushConsentEventToAmplitude);

    const ensureTelemetryInitialized = async (): Promise<void> => {
        if (isTelemetryInitialized) {
            return;
        }

        registerTelemetryLogoutListener(amplitude);

        let defaultLanguageName = '';

        try {
            defaultLanguageName = await getDefaultLanguageName();
        } catch {
            defaultLanguageName = 'N/A';
        }

        addDefaultShopwellPropertiesPlugin(amplitude, defaultLanguageName);
        initTelemetryAmplitude(amplitude, analyticsGatewayUrl);

        isTelemetryInitialized = true;
    };

    const enableTelemetryTracking = async (): Promise<void> => {
        if (isTelemetryListenerRegistered) {
            return;
        }

        await ensureTelemetryInitialized();

        if (isTelemetryListenerRegistered || !isTelemetryConsentAccepted.value) {
            return;
        }

        amplitude.setOptOut(false);
        Shopwell.Utils.EventBus.on('telemetry', pushTelemetryEventToAmplitude);
        isTelemetryListenerRegistered = true;
    };

    const disableTelemetryTracking = (): void => {
        if (!isTelemetryInitialized) {
            return;
        }

        if (isTelemetryListenerRegistered) {
            Shopwell.Utils.EventBus.off('telemetry', pushTelemetryEventToAmplitude);
            isTelemetryListenerRegistered = false;
        }

        const shopId = Shopwell.Store.get('context').app.config.shopId;
        const userId = Shopwell.Store.get('session').currentUser?.id;

        if (typeof userId === 'string') {
            privacyAmplitude.track('delete_user', {
                shop_id: shopId,
                user_id: userId,
                amplitude_user_id: `${shopId}:${userId}`,
            });
            privacyAmplitude.flush();
        }
        amplitude.setOptOut(true);
        amplitude.flush();
        amplitude.reset();
    };

    const syncTelemetryTracking = async (consentAccepted: boolean): Promise<void> => {
        if (consentAccepted) {
            await enableTelemetryTracking();

            return;
        }

        disableTelemetryTracking();
    };

    await syncTelemetryTracking(isTelemetryConsentAccepted.value);
    stopTelemetryConsentWatch?.();
    stopTelemetryConsentWatch = watch(isTelemetryConsentAccepted, (consentAccepted) => {
        void syncTelemetryTracking(consentAccepted);
    });
}

function registerAnonymousLogoutListener(anonymousAmplitude: AnonymousAmplitudeClient): void {
    Shopwell.Service('loginService').addOnLogoutListener(() => {
        anonymousAmplitude.setTransport('beacon');
        anonymousAmplitude.flush();
        anonymousAmplitude.reset();
    });
}

function registerTelemetryLogoutListener(amplitude: AmplitudeModule): void {
    Shopwell.Service('loginService').addOnLogoutListener(() => {
        amplitude.setTransport('beacon');
        setTimeout(() => {
            amplitude.flush();
            amplitude.reset();
        }, 0);
    });
}

function initAnonymousAmplitude(anonymousAmplitude: AnonymousAmplitudeClient, analyticsGatewayUrl: string): void {
    // The real key will be added by the gateway
    anonymousAmplitude.init(
        'placeholder-apikey',
        undefined,
        createAmplitudeInitOptions(`${analyticsGatewayUrl}/event/anonymous`),
    );
}

function initTelemetryAmplitude(amplitude: AmplitudeModule, analyticsGatewayUrl: string): void {
    // The real key will be added by the gateway
    amplitude.init('placeholder-apikey', undefined, createAmplitudeInitOptions(`${analyticsGatewayUrl}/event`));
}

function initPrivacyAmplitude(privacyAmplitude: PrivacyAmplitudeClient, analyticsGatewayUrl: string): void {
    // The real key will be added by the gateway
    privacyAmplitude.init('placeholder-apikey', undefined, createAmplitudeInitOptions(`${analyticsGatewayUrl}/delete-user`));
}

function createAmplitudeInitOptions(serverUrl: string) {
    return {
        autocapture: false,
        serverZone: 'EU' as const,
        appVersion: Shopwell.Store.get('context').app.config.version as string,
        trackingOptions: {
            ipAddress: false,
            language: false,
            platform: false,
        },
        fetchRemoteConfig: false,
        serverUrl,
    };
}

function addDefaultShopwellPropertiesPlugin(amplitude: AmplitudeModule, defaultLanguageName: string): void {
    amplitude.add({
        name: 'DefaultShopwellProperties',
        execute: (amplitudeEvent) => {
            const route = Shopwell.Application.view?.router?.currentRoute
                ? {
                      sw_page_name: Shopwell.Application.view.router.currentRoute.value.name,
                      sw_page_path: Shopwell.Application.view.router.currentRoute.value.path,
                      sw_page_full_path: Shopwell.Application.view.router.currentRoute.value.fullPath,
                  }
                : {};

            amplitudeEvent.event_properties = {
                ...amplitudeEvent.event_properties,
                sw_version: Shopwell.Store.get('context').app.config.version,
                sw_shop_id: Shopwell.Store.get('context').app.config.shopId,
                sw_app_url: Shopwell.Store.get('context').app.config.appUrl,
                sw_browser_url: window.location.origin,
                sw_user_agent: window.navigator.userAgent,
                sw_default_language: defaultLanguageName,
                sw_default_currency: Shopwell.Context.app.systemCurrencyISOCode,
                sw_screen_width: window.screen.width,
                sw_screen_height: window.screen.height,
                sw_screen_orientation: window.screen.orientation.type.split('-')[0],
                ...route,
            };

            return Promise.resolve(amplitudeEvent);
        },
    });
}

async function getDefaultLanguageName(): Promise<string> {
    const languageRepository = Shopwell.Service('repositoryFactory').create('language');
    const defaultLanguage = await languageRepository.get(Shopwell.Context.api.systemLanguageId!);

    return defaultLanguage!.name;
}
