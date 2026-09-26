<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    createColumnHelper,
    FlexRender,
    getCoreRowModel,
    useVueTable,
    type SortingState,
} from '@tanstack/vue-table';
import { ref, watch } from 'vue';
import Pagination from '@/components/Pagination.vue';
import { dashboard } from '@/routes';
import * as agentRoute from '@/routes/agents';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Agents', href: agentRoute.index() },
        ],
    },
});

type Agent = {
    id: string;
    first_name: string;
    middle_name: string | null;
    last_name: string;
    email: string;
    phone_number: string | null;
    commission_rate: number;
    status: 'active' | 'inactive';
    lots_count: number;
    created_at: string;
};

type PaginatedAgents = {
    data: Agent[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: { url: string | null; label: string; active: boolean }[];
};

const props = defineProps<{
    agents: PaginatedAgents;
    filters: {
        search?: string;
        status?: string;
        sort?: string;
        direction?: 'asc' | 'desc';
    };
}>();

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const sorting = ref<SortingState>(
    props.filters.sort
        ? [{ id: props.filters.sort, desc: props.filters.direction === 'desc' }]
        : [],
);

let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 400);
});

watch(status, () => applyFilters());

const applyFilters = () => {
    router.get(
        agentRoute.index().url,
        {
            search: search.value || undefined,
            status: status.value || undefined,
            sort: sorting.value[0]?.id ?? undefined,
            direction: sorting.value[0] ? (sorting.value[0].desc ? 'desc' : 'asc') : undefined,
        },
        { preserveState: true, replace: true },
    );
};

const resetFilters = () => {
    search.value = '';
    status.value = '';
    sorting.value = [];
    applyFilters();
};

const hasActiveFilters = () => search.value || status.value;

const handleSortingChange = (updater: any) => {
    sorting.value = typeof updater === 'function' ? updater(sorting.value) : updater;
    applyFilters();
};

const deleteAgent = (id: string, name: string) => {
    if (!confirm(`Remove "${name}" from the system? This cannot be undone.`)) return;
    router.delete(agentRoute.destroy({ agent: id }).url, { preserveScroll: true });
};

const AGENT_STATUS: Record<string, { label: string; classes: string; dot: string }> = {
    active:   { label: 'Active',   dot: 'bg-emerald-500', classes: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:ring-emerald-800' },
    inactive: { label: 'Inactive', dot: 'bg-gray-400',    classes: 'bg-gray-100 text-gray-500 ring-1 ring-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700' },
};

const getAgentStatus = (s: string) =>
    AGENT_STATUS[s] ?? { label: s, dot: 'bg-gray-400', classes: 'bg-gray-100 text-gray-500' };

const col = createColumnHelper<Agent>();

const columns = [
    col.accessor('first_name', { id: 'name', header: 'Agent', enableSorting: true }),
    col.accessor('email', { header: 'Email', enableSorting: true }),
    col.accessor('phone_number', { header: 'Phone', enableSorting: false }),
    col.accessor('commission_rate', { header: 'Commission', enableSorting: false }),
    col.accessor('lots_count', { header: 'Lots', enableSorting: true }),
    col.accessor('status', { header: 'Status', enableSorting: false }),
    col.display({ id: 'actions', header: '', enableSorting: false }),
];

const table = useVueTable({
    get data() { return props.agents.data; },
    columns,
    state: { get sorting() { return sorting.value; } },
    getCoreRowModel: getCoreRowModel(),
    onSortingChange: handleSortingChange,
    manualPagination: true,
    manualSorting: true,
    manualFiltering: true,
    pageCount: props.agents.last_page,
});
</script>

<template>
    <Head title="Agents" />

    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Agents</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ agents.total }} total agents registered</p>
            </div>
            <Link :href="agentRoute.create().url"
                class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Add Agent
            </Link>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" />
                </svg>
                <input v-model="search" type="text" placeholder="Search name or email..."
                    class="w-72 rounded-lg border border-gray-200 bg-white py-2 pl-9 pr-3 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white dark:placeholder:text-gray-500" />
            </div>

            <select v-model="status"
                class="rounded-lg border border-gray-200 bg-white py-2 pl-3 pr-8 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>

            <button v-if="hasActiveFilters()" @click="resetFilters"
                class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-500 shadow-sm transition hover:bg-gray-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-gray-400 dark:hover:bg-zinc-800">
                Clear filters
            </button>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50 dark:border-zinc-700 dark:bg-zinc-800/60">
                        <th v-for="header in table.getFlatHeaders()" :key="header.id"
                            class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400"
                            :class="{ 'cursor-pointer select-none hover:text-gray-700 dark:hover:text-gray-200': header.column.getCanSort() }"
                            @click="header.column.getCanSort() ? header.column.toggleSorting() : null">
                            <div class="flex items-center gap-1.5">
                                <FlexRender :render="header.column.columnDef.header" :props="header.getContext()" />
                                <span v-if="header.column.getCanSort()" class="text-gray-300 dark:text-gray-600">
                                    <span v-if="header.column.getIsSorted() === 'asc'">↑</span>
                                    <span v-else-if="header.column.getIsSorted() === 'desc'">↓</span>
                                    <span v-else>↕</span>
                                </span>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
                    <tr v-if="agents.data.length === 0">
                        <td :colspan="columns.length" class="px-5 py-16 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <p class="text-sm font-medium text-gray-400 dark:text-gray-500">No agents found</p>
                                <p class="text-xs text-gray-300 dark:text-gray-600">Try adjusting your search or filter</p>
                            </div>
                        </td>
                    </tr>

                    <tr v-for="row in table.getRowModel().rows" :key="row.id"
                        class="group transition-colors hover:bg-amber-50/40 dark:hover:bg-amber-900/10">
                        <td class="px-5 py-4">
                            <Link :href="agentRoute.show({ agent: row.original.id }).url"
                                class="block max-w-[200px] truncate font-medium text-gray-900 hover:text-amber-700 dark:text-white dark:hover:text-amber-400">
                                {{ row.original.first_name }} {{ row.original.middle_name ?? '' }} {{ row.original.last_name }}
                            </Link>
                        </td>
                        <td class="px-5 py-4 text-gray-600 dark:text-gray-300">{{ row.original.email }}</td>
                        <td class="px-5 py-4 text-gray-600 dark:text-gray-300">{{ row.original.phone_number ?? '—' }}</td>
                        <td class="px-5 py-4 text-gray-600 dark:text-gray-300">{{ row.original.commission_rate }}%</td>
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-amber-100 text-xs font-semibold text-amber-700 dark:bg-amber-900/40 dark:text-amber-400">
                                {{ row.original.lots_count }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <span :class="getAgentStatus(row.original.status).classes"
                                class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium">
                                <span :class="getAgentStatus(row.original.status).dot" class="h-1.5 w-1.5 rounded-full"></span>
                                {{ getAgentStatus(row.original.status).label }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-3 opacity-0 transition-opacity group-hover:opacity-100">
                                <Link :href="agentRoute.show({ agent: row.original.id }).url"
                                    class="text-xs font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">View</Link>
                                <Link :href="agentRoute.edit({ agent: row.original.id }).url"
                                    class="text-xs font-medium text-amber-600 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-300">Edit</Link>
                                <button @click="deleteAgent(row.original.id, `${row.original.first_name} ${row.original.last_name}`)"
                                    class="text-xs font-medium text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">Delete</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :links="agents.links" :from="agents.from" :to="agents.to" :total="agents.total" label="agents" />
    </div>
</template>
