export async function apiFetch(pathOrUrl, options = {}) {
    const isAbs = /^https?:\/\//i.test(pathOrUrl);
    const url = isAbs
        ? pathOrUrl
        : `/api${pathOrUrl.startsWith("/") ? "" : "/"}${pathOrUrl}`;

    const token = localStorage.getItem("jwt_token");
    const headers = new Headers(options.headers || {});
    if (
        !headers.has("Content-Type") &&
        options.body &&
        !(options.body instanceof FormData)
    ) {
        headers.set("Content-Type", "application/json");
    }
    if (token && !headers.has("Authorization")) {
        headers.set("Authorization", `Bearer ${token}`);
    }

    const res = await fetch(url, { ...options, headers });
    if (res.status === 401) {
        localStorage.removeItem("jwt_token");
        throw new Error("Non authentifié");
    }

    const ct = res.headers.get("content-type") || "";
    if (ct.includes("application/json")) {
        const data = await res.json();
        if (!res.ok) throw new Error(data?.message || `HTTP ${res.status}`);
        return data;
    }
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return res;
}
// Normalize apiFetch return:
// - if apiFetch returned a Response (e.g., 204 No Content), give back null
// - if it returned parsed JSON, keep it as-is
const unwrap = (r) =>
    r && typeof r === "object" && "ok" in r && "headers" in r ? null : r;

// Convenience helpers
export const getJson = (u, opt = {}) =>
    apiFetch(u, { ...opt, method: "GET" }).then(unwrap);

export const postJson = (u, body, opt = {}) =>
    apiFetch(u, {
        ...opt,
        method: "POST",
        // keep FormData as-is, JSONify plain objects
        body: body instanceof FormData ? body : JSON.stringify(body),
    }).then(unwrap);

export const patchJson = (u, body, opt = {}) =>
    apiFetch(u, {
        ...opt,
        method: "PATCH",
        body: body instanceof FormData ? body : JSON.stringify(body),
    }).then(unwrap);

// (optional but handy)
export const putJson = (u, body, opt = {}) =>
    apiFetch(u, {
        ...opt,
        method: "PUT",
        body: body instanceof FormData ? body : JSON.stringify(body),
    }).then(unwrap);

export const deleteJson = (u, opt = {}) =>
    apiFetch(u, { ...opt, method: "DELETE" }).then(unwrap);
