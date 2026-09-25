<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

// Shape of Laravel's paginator `links` array
type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

defineProps<{
    links: PaginationLink[];
    from: number | null;
    to: number | null;
    total: number;
    label?: string; // e.g. "lots", "clients"
}>();

// Laravel sends HTML entities ("&laquo; Previous", "Next &raquo;").
// Decoded here so we can render with {{ }} instead of v-html
// (v-html on <Link> can render blank).
const pageLabel = (label: string) =>
    label.replace(/&laquo;/g, '«').replace(/&raquo;/g, '»');
</script>

<template>
    <div class="flex flex-col items-center justify-between gap-3 sm:flex-row">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            <template v-if="total > 0">
                Showing
                <span class="font-medium text-gray-700 dark:text-gray-200">{{ from }}</span>
                –
                <span class="font-medium text-gray-700 dark:text-gray-200">{{ to }}</span>
                of
                <span class="font-medium text-gray-700 dark:text-gray-200">{{ total }}</span>
                {{ label ?? 'results' }}
            </template>
            <template v-else>No results</template>
        </p>

        <div class="flex items-center gap-1">
            <template v-for="link in links" :key="link.label">
                <Link v-if="link.url" :href="link.url" preserve-scroll preserve-state :class="[
                    'inline-flex h-8 min-w-[2rem] items-center justify-center rounded-lg px-2 text-sm transition',
                    link.active
                        ? 'bg-amber-600 font-semibold text-white shadow-sm'
                        : 'border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-gray-300 dark:hover:bg-zinc-800',
                ]">
                    {{ pageLabel(link.label) }}
                </Link>
                <span v-else
                    class="inline-flex h-8 min-w-[2rem] items-center justify-center rounded-lg border border-gray-100 px-2 text-sm text-gray-300 dark:border-zinc-800 dark:text-gray-600">
                    {{ pageLabel(link.label) }}
                </span>
            </template>
        </div>
    </div>
</template>
