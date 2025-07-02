import { ref, onMounted } from "vue";

export function useUserSettings() {
    let forcePasswordReset = ref(false);

    onMounted(async () => {
        try {
            const res = await fetch("/api/user/settings");
            if (!res.ok) throw new Error("Failed to fetch settings");
            const data = await res.json();
            forcePasswordReset.value = data.forcePasswordReset;
        } catch (e) {
            console.error(e);
        }
    });

    return { forcePasswordReset };
}
