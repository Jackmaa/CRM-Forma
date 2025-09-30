export async function apiFetch(pathOrUrl, options = {}) {
    // Attendre le bootstrap JWT si dispo (évite la course au démarrage)
    if (
        typeof window !== "undefined" &&
        window.awaitJwt &&
        !localStorage.getItem("jwt_token")
    ) {
        try {
            await window.awaitJwt;
        } catch {}
    }

    const isAbs = /^https?:\/\//i.test(pathOrUrl);
    const url = isAbs
        ? pathOrUrl
        : `/api${pathOrUrl.startsWith("/") ? "" : "/"}${pathOrUrl}`;

    const headers = new Headers(options.headers || {});
    const hasJsonBody = options.body && !(options.body instanceof FormData);
    if (!headers.has("Content-Type") && hasJsonBody)
        headers.set("Content-Type", "application/json");
    if (!headers.has("Accept")) headers.set("Accept", "application/json");

    const addAuth = (h) => {
        const t = localStorage.getItem("jwt_token");
        if (t && !h.has("Authorization")) h.set("Authorization", `Bearer ${t}`);
        return h;
    };

    let res = await fetch(url, { ...options, headers: addAuth(headers) });

    // Retry unique si 401 -> on regénère un JWT puis retente
    if (
        res.status === 401 &&
        typeof window !== "undefined" &&
        window.awaitJwt
    ) {
        localStorage.removeItem("jwt_token");
        try {
            await window.awaitJwt;
        } catch {}
        const retryHeaders = addAuth(new Headers(options.headers || {}));
        if (!retryHeaders.has("Content-Type") && hasJsonBody)
            retryHeaders.set("Content-Type", "application/json");
        if (!retryHeaders.has("Accept"))
            retryHeaders.set("Accept", "application/json");
        res = await fetch(url, { ...options, headers: retryHeaders });
    }

    if (res.status === 401) throw new Error("Non authentifié");

    const ct = (res.headers.get("content-type") || "").toLowerCase();
    if (ct.includes("application/json")) {
        const data = await res.json();
        if (!res.ok) throw new Error(data?.message || `HTTP ${res.status}`);
        return data;
    }
    if (res.status === 204) return null;
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return res;
}
