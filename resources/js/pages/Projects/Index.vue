<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import * as projectRoute from '@/routes/projects';
import { ref, watch } from 'vue';
import Pagination from '@/components/Pagination.vue';
import { Building2, Coins, MapPin } from 'lucide-vue-next';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Projects', href: projectRoute.index() },
        ],
    },
});

type Project = {
    id: number;
    name: string;
    location: string | null;
    status: string;
    lots_count: number;
    active_lots_count: number;
    sold_lots_count: number;
    delinquent_lots_count: number;
    total_contract_value: number | null;
};

type PaginatedProjects = {
    data: Project[];
    total: number;
    from: number | null;
    to: number | null;
    links: { url: string | null; label: string; active: boolean }[];
};

const props = defineProps<{
    projects: PaginatedProjects;
    filters: { search?: string };
}>();

const search = ref(props.filters.search ?? '');

let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(projectRoute.index().url, { search: search.value || undefined }, {
            preserveState: true,
            replace: true,
        });
    }, 400);
});

const formatPeso = (amount: number | null) =>
    '₱ ' + Number(amount ?? 0).toLocaleString('en-PH', { minimumFractionDigits: 2 });
</script>

<template>

    <Head title="Projects" />

    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Projects</h1>
                <p class="mt-1 text-base text-gray-500 dark:text-gray-400">{{ projects.total }} subdivisions</p>
            </div>
        </div>

        <div class="relative max-w-sm">
            <svg class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" fill="none"
                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" />
            </svg>
            <input v-model="search" type="text" placeholder="Search project..."
                class="w-full rounded-lg border border-gray-200 bg-white py-2.5 pl-10 pr-3 text-base text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white dark:placeholder:text-gray-500" />
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <Link v-for="project in projects.data" :key="project.id" :href="projectRoute.show({ project: project.id }).url"
                class="group overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-amber-300 hover:shadow-lg dark:border-zinc-700 dark:bg-zinc-900">

                <!-- Header -->
                <div class="flex items-start gap-3 border-b border-gray-100 p-5 dark:border-zinc-800">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
                        <Building2 class="h-5 w-5" />
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-lg font-bold text-gray-800 transition group-hover:text-amber-700 dark:text-gray-100 dark:group-hover:text-amber-400">
                            {{ project.name }}
                        </p>
                        <p v-if="project.location" class="mt-0.5 flex items-center gap-1 truncate text-sm text-gray-400">
                            <MapPin class="h-3.5 w-3.5 shrink-0" />
                            {{ project.location }}
                        </p>
                    </div>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-3 divide-x divide-gray-100 dark:divide-zinc-800">
                    <div class="flex flex-col items-center gap-0.5 py-4">
                        <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ project.active_lots_count }}</p>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Active</p>
                    </div>
                    <div class="flex flex-col items-center gap-0.5 py-4">
                        <p class="text-2xl font-bold text-red-500 dark:text-red-400">{{ project.delinquent_lots_count }}</p>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Delinquent</p>
                    </div>
                    <div class="flex flex-col items-center gap-0.5 py-4">
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ project.sold_lots_count }}</p>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Fully Paid</p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-between bg-gray-50 px-5 py-3.5 dark:bg-zinc-800/50">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ project.lots_count }} lot(s)</span>
                    <span class="flex items-center gap-1.5 text-base font-bold text-gray-800 dark:text-gray-100">
                        <Coins class="h-4 w-4 text-amber-500" />
                        {{ formatPeso(project.total_contract_value) }}
                    </span>
                </div>
            </Link>
        </div>

        <div v-if="projects.data.length === 0" class="rounded-xl border border-gray-200 bg-white px-5 py-16 text-center dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-base font-medium text-gray-400 dark:text-gray-500">No projects found</p>
        </div>

        <Pagination :links="projects.links" :from="projects.from" :to="projects.to" :total="projects.total"
            label="projects" />

    </div>
</template>
