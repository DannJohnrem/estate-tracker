import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import type { FlashToast } from '@/types/ui';

export function initializeFlashToast(): void {
    let lastKey: string | null = null;
    let lastTime = 0;

    router.on('flash', (event) => {
        const flash = (event as CustomEvent).detail?.flash;
        const data = flash?.toast as FlashToast | undefined;

        if (!data) {
            return;
        }

        // 🔧 Guard against the same flash payload firing more than once in
        // quick succession (observed with Inertia's built-in flash event
        // during redirect chains like login -> dashboard). Only suppress an
        // exact duplicate message within a 500ms window; distinct messages
        // always show normally.
        const key = `${data.type}:${data.message}`;
        const now = Date.now();

        if (key === lastKey && now - lastTime < 500) {
            return;
        }

        lastKey = key;
        lastTime = now;

        toast[data.type](data.message);
    });
}
