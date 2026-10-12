import type { ApiProblem } from "./entities";

class ApiError extends Error {
  readonly status: number;
  readonly problem: ApiProblem | null;

  constructor(status: number, problem: ApiProblem | null) {
    super(problem?.detail ?? `HTTP ${status}`);
    this.status = status;
    this.problem = problem;
  }
}

const NO_REFRESH = ["/auth/login", "/auth/register", "/auth/refresh"];

let refreshing: Promise<boolean> | null = null;
let onSessionLost: () => void = () => {};

function setSessionLostHandler(handler: () => void): void {
  onSessionLost = handler;
}

function refreshSession(): Promise<boolean> {
  refreshing ??= fetch("/api/auth/refresh", {
    method: "POST",
    credentials: "include",
  })
    .then((response) => response.ok)
    .catch(() => false)
    .finally(() => {
      refreshing = null;
    });

  return refreshing;
}

async function request<T>(
  method: string,
  path: string,
  body?: unknown,
  signal?: AbortSignal,
  retry = true,
): Promise<T> {
  const response = await fetch(`/api${path}`, {
    method,
    signal,
    credentials: "include",
    headers: {
      Accept: "application/json",
      ...(body !== undefined && { "Content-Type": "application/json" }),
    },
    body: body === undefined ? undefined : JSON.stringify(body),
  });

  if (
    response.status === 401 &&
    retry &&
    !NO_REFRESH.includes(path) &&
    (await refreshSession())
  )
    return request<T>(method, path, body, signal, false);

  if (response.status === 401 && !NO_REFRESH.includes(path)) onSessionLost();

  if (!response.ok)
    throw new ApiError(
      response.status,
      await response.json().catch(() => null),
    );

  return (response.status === 204 ? undefined : await response.json()) as T;
}

const http = {
  get: <T>(path: string, signal?: AbortSignal) =>
    request<T>("GET", path, undefined, signal),
  post: <T>(path: string, body?: unknown, signal?: AbortSignal) =>
    request<T>("POST", path, body, signal),
};

export { ApiError, setSessionLostHandler, http };
