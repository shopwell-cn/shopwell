/**
 * @sw-package framework
 */
import type { AxiosInstance } from 'axios';

const HttpClient = Shopwell.Classes._private.HttpFactory;

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default function initializeHttpClient(): AxiosInstance {
    return HttpClient(Shopwell.Context.api) as unknown as AxiosInstance;
}
