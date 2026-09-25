<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Plus } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { create, edit, index } from '@/routes/admin/permissions';
import type { Permission } from '@/types';

defineOptions({ layout: AppLayout });

const props = defineProps<{
    permissions: Record<string, Permission[]>;
    pagination: {
        current_page: number;
        last_page: number;
        total: number;
    };
}>();

const loading = ref(false);

function goToPage(page: number) {
    if (page < 1 || page > props.pagination.last_page || page === props.pagination.current_page || loading.value) {
        return;
    }

    // Partial reload — only re-fetches permissions + pagination, not the full page.
    router.get(
        index().url,
        { page },
        {
            preserveScroll: true,
            preserveState: true,
            only: ['permissions', 'pagination'],
            onStart: () => (loading.value = true),
            onFinish: () => (loading.value = false),
        },
    );
}

// Builds the page list with ellipses, e.g. [1, '...', 4, 5, 6, '...', 12]
const pageItems = computed<(number | '...')[]>(() => {
    const current = props.pagination.current_page;
    const last = props.pagination.last_page;

    const keep = new Set<number>([1, last, current, current - 1, current + 1]);
    const sorted = [...keep].filter((p) => p >= 1 && p <= last).sort((a, b) => a - b);

    const items: (number | '...')[] = [];
    let prev = 0;
    for (const page of sorted) {
        if (prev && page - prev > 1) items.push('...');
        items.push(page);
        prev = page;
    }

    return items;
});

const skeletonCards = Array.from({ length: 6 }, (_, i) => i);
</script>

<template>
    <Head title="Permissions" />

    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">
        <!-- Page Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Permissions</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    A list of all available permissions, grouped by module. Assign them to Roles as needed.
                </p>
            </div>

            <Button as-child class="bg-amber-600 hover:bg-amber-700">
                <Link :href="create().url">
                    <Plus class="h-4 w-4" />
                    Add Permission
                </Link>
            </Button>
        </div>

        <!-- Skeleton Loading -->
        <div v-if="loading" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div v-for="i in skeletonCards" :key="i" class="rounded-xl border p-4">
                <div class="mb-3 h-4 w-24 animate-pulse rounded bg-gray-200 dark:bg-zinc-800" />
                <div class="flex flex-wrap gap-2">
                    <div v-for="j in 4" :key="j" class="h-6 w-20 animate-pulse rounded-full bg-gray-100 dark:bg-zinc-800/60" />
                </div>
            </div>
        </div>

        <!-- Groups Grid -->
        <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="(perms, group) in permissions"
                :key="group"
                class="rounded-xl border p-4 transition-colors hover:border-amber-300"
            >
                <h3 class="mb-3 text-sm font-semibold capitalize text-gray-800 dark:text-gray-100">
                    {{ group }}
                </h3>

                <div class="flex flex-wrap gap-2">
                    <Link
                        v-for="perm in perms"
                        :key="perm.id"
                        :href="edit({ permission: perm.id }).url"
                        class="group inline-flex items-center gap-1 rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-medium text-gray-600 transition-colors hover:border-amber-300 hover:bg-amber-50 hover:text-amber-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-gray-300"
                    >
                        {{ perm.name }}
                        <Pencil class="h-3 w-3 opacity-0 transition-opacity group-hover:opacity-100" />
                    </Link>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-if="Object.keys(permissions).length === 0"
                class="col-span-full flex flex-col items-center gap-2 rounded-xl border p-12 text-center"
            >
                <p class="text-sm font-medium text-gray-400 dark:text-gray-500">No permissions found</p>
                <p class="text-xs text-gray-300 dark:text-gray-600">Add a permission to get started.</p>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.last_page > 1" class="flex items-center justify-center gap-1 pt-2">
            <button
                type="button"
                class="rounded-md px-3 py-1.5 text-sm font-medium text-gray-500 transition-colors hover:bg-amber-50 hover:text-amber-700 disabled:pointer-events-none disabled:opacity-40 dark:text-gray-400"
                :disabled="pagination.current_page === 1"
                @click="goToPage(pagination.current_page - 1)"
            >
                Prev
            </button>

            <template v-for="(item, i) in pageItems" :key="i">
                <span v-if="item === '...'" class="px-2 text-sm text-gray-400">…</span>
                <button
                    v-else
                    type="button"
                    class="h-8 w-8 rounded-md text-sm font-medium transition-colors"
                    :class="
                        item === pagination.current_page
                            ? 'bg-amber-600 text-white'
                            : 'text-gray-600 hover:bg-amber-50 hover:text-amber-700 dark:text-gray-300'
                    "
                    @click="goToPage(item)"
                >
                    {{ item }}
                </button>
            </template>

            <button
                type="button"
                class="rounded-md px-3 py-1.5 text-sm font-medium text-gray-500 transition-colors hover:bg-amber-50 hover:text-amber-700 disabled:pointer-events-none disabled:opacity-40 dark:text-gray-400"
                :disabled="pagination.current_page === pagination.last_page"
                @click="goToPage(pagination.current_page + 1)"
            >
                Next
            </button>
        </div>
    </div>
</template>
