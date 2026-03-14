import type { ApiError } from "./ApiError";
import { ApiClientError } from "./ApiError";
import type { UniResponse } from "./uniRequest";

type ErrorResponse = { errors: Array<ApiError> };

export function errorInterceptor(
  response: UniResponse<unknown>,
): asserts response is UniResponse<ErrorResponse> {
  throw new ApiClientError(response as UniResponse<ErrorResponse>);
}
