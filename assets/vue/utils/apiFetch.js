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
