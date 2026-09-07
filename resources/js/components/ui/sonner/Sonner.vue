<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance';
import { Toaster as SonnerPrimitive } from 'vue-sonner';
import 'vue-sonner/style.css';

const { appearance } = useAppearance();

withDefaults(
    defineProps<{
        position?: 'top-right' | 'top-left' | 'bottom-right' | 'bottom-left' | 'top-center' | 'bottom-center';
    }>(),
    {
        position: 'top-right',
    },
);
</script>

<template>
    <SonnerPrimitive
        :theme="appearance"
        class="toaster group"
        :position="position"
        rich-colors
        :style="{
            '--normal-bg': 'var(--popover)',
            '--normal-text': 'var(--popover-foreground)',
            '--normal-border': 'var(--border)',
        }"
    />
</template>
