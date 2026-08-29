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
import { dashboard } from '@/routes';
import * as clientRoute from '@/routes/clients';

// Layout
defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Clients',   href: clientRoute.index() },
        ],
    },
});

// Types
type Lot = {
    id: number;
    lot_number: string;
    block_number: string | null;
    subdivision: string;
    status: 'active' | 'delinquent' | 'fully_paid' | 'cancelled';
    next_due_date: string | null;
};

type Client = {
    id: number;
    first_name: string;
    middle_name: string | null;
    last_name: string;
    email: string;
    phone_number: string | null;
    lots_count: number;
    lots: Lot[];
    created_at: string;
};

type PaginatedClients = {
    data: Client[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: { url: string | null; label: string; active: boolean }[];
};

// Props
const props = defineProps<{
    clients: PaginatedClients;
    filters: {
        search?: string;
        status?: string;
        sort?: string;
        direction?: 'asc' | 'desc';
    };
}>();

// State
const search  = ref(props.filters.search ?? '');
const status  = ref(props.filters.status ?? '');
const sorting = ref<SortingState>(
    props.filters.sort
        ? [{ id: props.filters.sort, desc: props.filters.direction === 'desc' }]
        : [],
);

// Search debounce
let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 400);
});

watch(status, () => applyFilters());

// Filters
const applyFilters = () => {
    router.get(
        clientRoute.index(),
        {
            search:    search.value || undefined,
            status:    status.value || undefined,
            sort:      sorting.value[0]?.id ?? undefined,
            direction: sorting.value[0] ? (sorting.value[0].desc ? 'desc' : 'asc') : undefined,
        },
        { preserveState: true, replace: true },
    );
};

const resetFilters = () => {
    search.value  = '';
    status.value  = '';
    sorting.value = [];
    applyFilters();
};

const hasActiveFilters = () => search.value || status.value;

// Sorting
const handleSortingChange = (updater: any) => {
    sorting.value = typeof updater === 'function' ? updater(sorting.value) : updater;
    applyFilters();
};

// Delete
const deleteClient = (id: number, name: string) => {
    if (!confirm(`Remove "${name}" from the system? This cannot be undone.`)) return;
    router.delete(clientRoute.destroy({ client: id }), {
        preserveScroll: true,
    });
};

//  Status config
const LOT_STATUS: Record<string, { label: string; classes: string }> = {
    active:     { label: 'Active',     classes: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:ring-emerald-800' },
    delinquent: { label: 'Delinquent', classes: 'bg-red-50 text-red-700 ring-1 ring-red-200 dark:bg-red-900/30 dark:text-red-400 dark:ring-red-800' },
    fully_paid: { label: 'Fully Paid', classes: 'bg-blue-50 text-blue-700 ring-1 ring-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:ring-blue-800' },
    cancelled:  { label: 'Cancelled',  classes: 'bg-gray-100 text-gray-500 ring-1 ring-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700' },
};

const getLotStatus = (s: string) =>
    LOT_STATUS[s] ?? { label: s, classes: 'bg-gray-100 text-gray-500' };

// Columns
const col = createColumnHelper<Client>();

const columns = [
    col.accessor('first_name', {
        id: 'name',
        header: 'Client',
        enableSorting: true,
    }),
    col.accessor('email', {
        header: 'Email',
        enableSorting: true,
    }),
    col.accessor('phone_number', {
        header: 'Phone',
        enableSorting: false,
    }),
    col.accessor('lots_count', {
        header: 'Lots',
        enableSorting: true,
    }),
    col.accessor('lots', {
        id: 'status',
        header: 'Lot Status',
        enableSorting: false,
    }),
    col.display({
        id: 'actions',
        header: '',
        enableSorting: false,
    }),
];

// Table
const table = useVueTable({
    get data()       { return props.clients.data; },
    columns,
    state:           { get sorting() { return sorting.value; } },
    getCoreRowModel: getCoreRowModel(),
    onSortingChange: handleSortingChange,
    manualPagination: true,
    manualSorting:    true,
    manualFiltering:  true,
    pageCount:        props.clients.last_page,
});
</script>

<template>
    <Head title="Clients" />

    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">

        <!-- Page Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">
                    Clients
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ clients.total }} total clients registered
                </p>
            </div>
            <Link
                :href="clientRoute.create()"
                class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Add Client
            </Link>
        </div>

        <!--Filters -->
        <div class="flex flex-wrap items-center gap-3">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" />
                </svg>
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search name or email..."
                    class="w-72 rounded-lg border border-gray-200 bg-white py-2 pl-9 pr-3 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white dark:placeholder:text-gray-500"
                />
            </div>

            <select
                v-model="status"
                class="rounded-lg border border-gray-200 bg-white py-2 pl-3 pr-8 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
            >
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="delinquent">Delinquent</option>
                <option value="fully_paid">Fully Paid</option>
                <option value="cancelled">Cancelled</option>
            </select>

            <button
                v-if="hasActiveFilters()"
                @click="resetFilters"
                class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-500 shadow-sm transition hover:bg-gray-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-gray-400 dark:hover:bg-zinc-800"
            >
                Clear filters
            </button>
        </div>

        <!--Table -->
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <table class="w-full text-sm">

                <!-- Head -->
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50 dark:border-zinc-700 dark:bg-zinc-800/60">
                        <th
                            v-for="header in table.getFlatHeaders()"
                            :key="header.id"
                            class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400"
                            :class="{ 'cursor-pointer select-none hover:text-gray-700 dark:hover:text-gray-200': header.column.getCanSort() }"
                            @click="header.column.getCanSort() ? header.column.toggleSorting() : null"
                        >
                            <div class="flex items-center gap-1.5">
                                <FlexRender
                                    :render="header.column.columnDef.header"
                                    :props="header.getContext()"
                                />
                                <span v-if="header.column.getCanSort()" class="text-gray-300 dark:text-gray-600">
                                    <span v-if="header.column.getIsSorted() === 'asc'">↑</span>
                                    <span v-else-if="header.column.getIsSorted() === 'desc'">↓</span>
                                    <span v-else>↕</span>
                                </span>
                            </div>
                        </th>
                    </tr>
                </thead>

                <!-- Body -->
                <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">

                    <!-- Empty state -->
                    <tr v-if="clients.data.length === 0">
                        <td :colspan="columns.length" class="px-5 py-16 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <p class="text-sm font-medium text-gray-400 dark:text-gray-500">No clients found</p>
                                <p class="text-xs text-gray-300 dark:text-gray-600">Try adjusting your search or filter</p>
                            </div>
                        </td>
                    </tr>

                    <!-- Rows -->
                    <tr
                        v-for="row in table.getRowModel().rows"
                        :key="row.id"
                        class="group transition-colors hover:bg-amber-50/40 dark:hover:bg-amber-900/10"
                    >
                        <!-- Client Name -->
                        <td class="px-5 py-4">
                            <Link
                                :href="clientRoute.show({ client: row.original.id })"
                                class="font-medium text-gray-900 hover:text-amber-700 dark:text-white dark:hover:text-amber-400"
                            >
                                {{ row.original.first_name }}
                                {{ row.original.middle_name ?? '' }}
                                {{ row.original.last_name }}
                            </Link>
                        </td>

                        <!-- Email -->
                        <td class="px-5 py-4 text-gray-600 dark:text-gray-300">
                            {{ row.original.email }}
                        </td>

                        <!-- Phone -->
                        <td class="px-5 py-4 text-gray-600 dark:text-gray-300">
                            {{ row.original.phone_number ?? '—' }}
                        </td>

                        <!-- Lots Count -->
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-amber-100 text-xs font-semibold text-amber-700 dark:bg-amber-900/40 dark:text-amber-400">
                                {{ row.original.lots_count }}
                            </span>
                        </td>

                        <!-- Lot Statuses -->
                        <td class="px-5 py-4">
                            <div class="flex flex-wrap gap-1.5">
                                <span
                                    v-for="lot in row.original.lots"
                                    :key="lot.id"
                                    :class="getLotStatus(lot.status).classes"
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                    :title="`${lot.block_number} ${lot.lot_number} — ${lot.subdivision}`"
                                >
                                    {{ getLotStatus(lot.status).label }}
                                </span>
                            </div>
                        </td>

                        <!-- Actions -->
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-3 opacity-0 transition-opacity group-hover:opacity-100">
                                <Link
                                    :href="clientRoute.show({ client: row.original.id })"
                                    class="text-xs font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                                >
                                    View
                                </Link>
                                <Link
                                    :href="clientRoute.edit({ client: row.original.id })"
                                    class="text-xs font-medium text-amber-600 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-300"
                                >
                                    Edit
                                </Link>
                                <button
                                    @click="deleteClient(
                                        row.original.id,
                                        `${row.original.first_name} ${row.original.last_name}`
                                    )"
                                    class="text-xs font-medium text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                >
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="flex flex-col items-center justify-between gap-3 sm:flex-row">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                <template v-if="clients.total > 0">
                    Showing
                    <span class="font-medium text-gray-700 dark:text-gray-200">{{ clients.from }}</span>
                    –
                    <span class="font-medium text-gray-700 dark:text-gray-200">{{ clients.to }}</span>
                    of
                    <span class="font-medium text-gray-700 dark:text-gray-200">{{ clients.total }}</span>
                    clients
                </template>
                <template v-else>No results</template>
            </p>

            <div class="flex items-center gap-1">
                <template v-for="link in clients.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        preserve-scroll
                        preserve-state
                        :class="[
                            'inline-flex h-8 min-w-[2rem] items-center justify-center rounded-lg px-2 text-sm transition',
                            link.active
                                ? 'bg-amber-600 font-semibold text-white shadow-sm'
                                : 'border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-gray-300 dark:hover:bg-zinc-800',
                        ]"
                        v-html="link.label"
                    />
                    <span
                        v-else
                        class="inline-flex h-8 min-w-[2rem] items-center justify-center rounded-lg border border-gray-100 px-2 text-sm text-gray-300 dark:border-zinc-800 dark:text-gray-600"
                        v-html="link.label"
                    />
                </template>
            </div>
        </div>

    </div>
</template>
