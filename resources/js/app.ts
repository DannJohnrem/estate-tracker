import { createInertiaApp } from '@inertiajs/vue3';
import { createApp } from 'vue';
import { Toaster } from '@/components/ui/sonner';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name === 'Welcome':
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();

// This will listen for flash toast data from the server...
initializeFlashToast();

// 🔧 Mount the Toaster as a SEPARATE, persistent Vue app outside of Inertia's
// page/layout lifecycle. Inertia tears down and rebuilds the #app tree on
// every navigation, so a Toaster mounted inside any layout gets destroyed
// mid-flight (this was causing toasts to randomly vanish on redirects like
// logout → Welcome). This #toaster-root mount survives every navigation
// because it's never touched by Inertia at all.
createApp(Toaster, { position: 'top-right' }).mount('#toaster-root');
