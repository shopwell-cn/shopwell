import type { UniRequestMethod, UniRequestOptions } from "./types";
import { appendQuery, joinUrl, normalizeHeaderRecord } from "./utils";

export type UniRequestAdapter = <T>(
  options: UniRuntimeRequestOptions<T>,
) => UniRequestTask;

export type UniRequestTask = {
  abort?: () => void;
};

export type UniRuntimeRequestOptions<T> = {
  url: string;
  method?: UniRequestMethod;
  data?: unknown;
  header?: Record<string, string>;
  timeout?: number;
  dataType?: "json" | "text";
  responseType?: "text" | "arraybuffer";
  success?: (res: UniRequestSuccess<T>) => void;
  fail?: (err: UniRequestError) => void;
  complete?: (res: UniRequestSuccess<T> | UniRequestError) => void;
};

export type UniRequestSuccess<T> = {
  data: T;
  statusCode: number;
  header?: Record<string, string | string[]>;
  errMsg?: string;
  cookies?: string[];
};

export type UniRequestError = {
  errMsg: string;
};

export type UniResponse<T> = {
  ok: boolean;
  status: number;
  statusText?: string;
  url: string;
  headers: Record<string, string>;
  data: T;
  _data: T;
  raw: UniRequestSuccess<T>;
};

export type UniRequestContext = {
  url: string;
  method: UniRequestMethod;
  headers: Record<string, string>;
  body?: unknown;
  query?: Record<string, unknown>;
  timeout?: number;
};

type RequestConfig = {
  baseURL?: string;
  path: string;
  method: UniRequestMethod;
  headers?: Record<string, string>;
  query?: Record<string, unknown>;
  body?: unknown;
  request: UniRequestAdapter;
  options?: UniRequestOptions;
};

const defaultRetryStatusCodes = [408, 409, 425, 429, 500, 502, 503, 504];

export function resolveUniRequest(request?: UniRequestAdapter): UniRequestAdapter {
  if (request) return request;
  const globalAny = globalThis as typeof globalThis & {
    uni?: { request: UniRequestAdapter };
  };
  if (globalAny.uni?.request) {
    return globalAny.uni.request.bind(globalAny.uni);
  }
  throw new Error(
    "[ApiClientError] uni.request is not available. Provide a request adapter.",
  );
}

export async function requestWithUni<T>(config: RequestConfig): Promise<UniResponse<T>> {
  const requestOptions = config.options ?? {};
  const url = appendQuery(
    joinUrl(config.baseURL, config.path),
    config.query,
  );
  const headers = config.headers ?? {};

  const requestOnce = () =>
    rawRequest<T>({
      request: config.request,
      url,
      method: config.method,
      headers,
      body: config.body,
      timeout: requestOptions.timeout,
      signal: requestOptions.signal,
    });

  return requestWithRetry(requestOnce, requestOptions);
}

type RawRequestConfig = {
  request: UniRequestAdapter;
  url: string;
  method: UniRequestMethod;
  headers: Record<string, string>;
  body?: unknown;
  timeout?: number;
  signal?: AbortSignal;
};

function rawRequest<T>(config: RawRequestConfig): Promise<UniResponse<T>> {
  return new Promise((resolve, reject) => {
    if (config.signal?.aborted) {
      reject(createAbortError());
      return;
    }

    let settled = false;
    const onAbort = () => {
      if (settled) return;
      settled = true;
      task?.abort?.();
      cleanup();
      reject(createAbortError());
    };
    const cleanup = () => {
      if (!config.signal) return;
      config.signal.removeEventListener("abort", onAbort);
    };

    const task = config.request<T>({
      url: config.url,
      method: config.method,
      data: config.body,
      header: config.headers,
      timeout: config.timeout,
      dataType: "json",
      success: (res) => {
        if (settled) return;
        settled = true;
        cleanup();
        resolve(toUniResponse(res, config.url));
      },
      fail: (err) => {
        if (settled) return;
        settled = true;
        cleanup();
        reject(createRequestError(err));
      },
    });

    if (config.signal) {
      config.signal.addEventListener("abort", onAbort, { once: true });
    }
  });
}

function toUniResponse<T>(res: UniRequestSuccess<T>, url: string): UniResponse<T> {
  const status = typeof res.statusCode === "number" ? res.statusCode : 0;
  return {
    ok: status >= 200 && status < 300,
    status,
    statusText: undefined,
    url,
    headers: normalizeHeaderRecord(res.header),
    data: res.data,
    _data: res.data,
    raw: res,
  };
}

function createAbortError(): Error {
  const error = new Error("The request was aborted");
  error.name = "AbortError";
  return error;
}

function createRequestError(error: UniRequestError): Error {
  return new Error(error.errMsg || "Request failed");
}

function normalizeRetryCount(retry?: number | boolean): number {
  if (retry === true) return 1;
  if (retry === false || retry === undefined) return 0;
  if (Number.isFinite(retry)) return Math.max(0, Math.trunc(retry));
  return 0;
}

function getRetryDelay(
  retryDelay: UniRequestOptions["retryDelay"],
  attempt: number,
): number {
  if (typeof retryDelay === "function") return Math.max(0, retryDelay(attempt));
  if (typeof retryDelay === "number") return Math.max(0, retryDelay);
  return 0;
}

function isAbortError(error: unknown): boolean {
  return error instanceof Error && error.name === "AbortError";
}

async function requestWithRetry<T>(
  requestOnce: () => Promise<UniResponse<T>>,
  options: UniRequestOptions,
): Promise<UniResponse<T>> {
  const maxRetries = normalizeRetryCount(options.retry);
  const retryStatusCodes = options.retryStatusCodes ?? defaultRetryStatusCodes;
  let attempt = 0;

  while (true) {
    try {
      const response = await requestOnce();
      if (
        !response.ok &&
        attempt < maxRetries &&
        retryStatusCodes.includes(response.status)
      ) {
        attempt += 1;
        await delay(getRetryDelay(options.retryDelay, attempt), options.signal);
        continue;
      }
      return response;
    } catch (error) {
      if (isAbortError(error)) throw error;
      if (attempt < maxRetries) {
        attempt += 1;
        await delay(getRetryDelay(options.retryDelay, attempt), options.signal);
        continue;
      }
      throw error;
    }
  }
}

function delay(ms: number, signal?: AbortSignal): Promise<void> {
  if (ms <= 0) return Promise.resolve();
  return new Promise((resolve, reject) => {
    if (signal?.aborted) {
      reject(createAbortError());
      return;
    }
    const timer = setTimeout(() => {
      cleanup();
      resolve();
    }, ms);

    const onAbort = () => {
      clearTimeout(timer);
      cleanup();
      reject(createAbortError());
    };

    const cleanup = () => {
      if (signal) signal.removeEventListener("abort", onAbort);
    };

    if (signal) signal.addEventListener("abort", onAbort, { once: true });
  });
}
