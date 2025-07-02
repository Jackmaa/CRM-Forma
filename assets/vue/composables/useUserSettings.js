import { ref, onMounted } from "vue";

export function useUserSettings() {
    const forcePasswordReset = ref(false);

    onMounted(async () => {
        const token = localStorage.getItem("auth_token");
        if (!token) return;

        try {
            const res = await fetch("/api/user/settings", {
                headers: { Authorization: `Bearer ${token}` },
            });
            if (!res.ok) throw new Error("Failed to fetch settings");
            const data = await res.json();
            forcePasswordReset.value = data.forcePasswordReset;
        } catch (e) {
            console.error(e);
        }
    });

    return { forcePasswordReset };
}
