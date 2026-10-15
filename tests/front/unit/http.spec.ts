import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import { ApiError, http, setSessionLostHandler } from "@/shared/api/http";

const json = (body: unknown, status = 200) =>
  new Response(JSON.stringify(body), { status, headers: { "Content-Type": "application/json" } });

describe("http", () => {
  const fetchMock = vi.fn<typeof fetch>();
  const sessionLost = vi.fn();

  beforeEach(() => {
    vi.stubGlobal("fetch", fetchMock);
    setSessionLostHandler(sessionLost);
  });

  afterEach(() => {
    fetchMock.mockReset();
    sessionLost.mockReset();
    vi.unstubAllGlobals();
  });

  it("sends JSON with the session cookies", async () => {
    fetchMock.mockResolvedValueOnce(json({ id: "1" }, 201));

    await expect(http.post("/observations", { count: 2 })).resolves.toEqual({ id: "1" });

    const [url, init] = fetchMock.mock.calls[0]!;
    expect(url).toBe("/api/observations");
    expect(init).toMatchObject({
      method: "POST",
      credentials: "include",
      body: '{"count":2}',
      headers: { Accept: "application/json", "Content-Type": "application/json" },
    });
  });

  it("lets the browser set the type of a form", async () => {
    fetchMock.mockResolvedValueOnce(json({}));
    const body = new FormData();

    await http.post("/observations/1/photos", body);

    const init = fetchMock.mock.calls[0]![1]!;
    expect(init.body).toBe(body);
    expect(init.headers).toEqual({ Accept: "application/json" });
  });

  it("returns nothing for an empty answer", async () => {
    fetchMock.mockResolvedValueOnce(new Response(null, { status: 204 }));

    await expect(http.delete("/observations/1")).resolves.toBeUndefined();
  });

  it("refreshes the session once, then replays the request", async () => {
    fetchMock
      .mockResolvedValueOnce(json({}, 401))
      .mockResolvedValueOnce(new Response(null, { status: 204 }))
      .mockResolvedValueOnce(json([{ id: "1" }]));

    await expect(http.get("/observations/map")).resolves.toEqual([{ id: "1" }]);

    expect(fetchMock.mock.calls.map(([url]) => url)).toEqual([
      "/api/observations/map",
      "/api/auth/refresh",
      "/api/observations/map",
    ]);
    expect(sessionLost).not.toHaveBeenCalled();
  });

  it("shares one refresh between parallel requests", async () => {
    fetchMock.mockImplementation(async (url) =>
      url === "/api/auth/refresh" ? new Response(null, { status: 204 }) : json({}, 401),
    );

    await Promise.allSettled([http.get("/me"), http.get("/observations/map")]);

    expect(fetchMock.mock.calls.filter(([url]) => url === "/api/auth/refresh")).toHaveLength(1);
  });

  it("reports a lost session when the refresh fails", async () => {
    fetchMock.mockResolvedValueOnce(json({}, 401)).mockResolvedValueOnce(json({}, 401));

    const error = await http.get("/me").catch((caught: unknown) => caught);

    expect(error).toBeInstanceOf(ApiError);
    expect((error as ApiError).status).toBe(401);
    expect(sessionLost).toHaveBeenCalledOnce();
  });

  it("never refreshes for a wrong password", async () => {
    fetchMock.mockResolvedValueOnce(json({}, 401));

    await expect(http.post("/auth/login", {})).rejects.toMatchObject({ status: 401 });

    expect(fetchMock).toHaveBeenCalledOnce();
    expect(sessionLost).not.toHaveBeenCalled();
  });

  it("turns other errors into an ApiError with the status", async () => {
    fetchMock.mockResolvedValueOnce(json({ detail: "nope" }, 422));

    await expect(http.patch("/observations/1", {})).rejects.toMatchObject({ status: 422 });
  });
});
