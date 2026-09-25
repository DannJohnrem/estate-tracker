<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import * as paymentRoute from '@/routes/payments';
import * as lotRoute from '@/routes/lots';
import * as clientRoute from '@/routes/clients';
import {
    createColumnHelper,
    FlexRender,
    getCoreRowModel,
    useVueTable,
    type SortingState,
} from '@tanstack/vue-table';
import { ref, watch } from 'vue';
import Pagination from '@/components/Pagination.vue';
import { PAYMENT_METHODS, PAYMENT_STATUS, methodLabel } from '@/lib/payments';

// Layout
defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Payments', href: paymentRoute.index() },
        ],
    },
});

// Types
type Client = {
    id: number;
    first_name: string;
    middle_name: string | null;
    last_name: string;
};

type Payment = {
    id: string;
    or_number: string | null;
    amount: string | number;
    paid_at: string;
    method: string | null;
    status: 'posted' | 'voided';
    lot: {
        id: number;
        client_id: number;
        lot_number: string;
        block_number: string | null;
        subdivision: string;
        client: Client;
    } | null;
};

type PaginatedPayments = {
    data: Payment[];
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
    payments: PaginatedPayments;
    subdivisions: string[];
    summary: { total_collected: number; count: number };
    can: { create: boolean };
    filters: {
        search?: string;
        method?: string;
        status?: string;
        subdivision?: string;
        date_from?: string;
        date_to?: string;
        sort?: string;
        direction?: 'asc' | 'desc';
    };
}>();

// State
const search = ref(props.filters.search ?? '');
const method = ref(props.filters.method ?? '');
const status = ref(props.filters.status ?? '');
const subdivision = ref(props.filters.subdivision ?? '');
const dateFrom = ref(props.filters.date_from ?? '');
const dateTo = ref(props.filters.date_to ?? '');

const defaultSorting: SortingState = [{ id: 'paid_at', desc: true }];
const sorting = ref<SortingState>(
    props.filters.sort
        ? [{ id: props.filters.sort, desc: props.filters.direction !== 'asc' }]
        : defaultSorting,
);

// Search debounce
let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 400);
});

watch([method, status, subdivision, dateFrom, dateTo], () => applyFilters());

// Filters
const applyFilters = () => {
    router.get(
        paymentRoute.index().url,
        {
            search: search.value || undefined,
            method: method.value || undefined,
            status: status.value || undefined,
            subdivision: subdivision.value || undefined,
            date_from: dateFrom.value || undefined,
            date_to: dateTo.value || undefined,
            sort: sorting.value[0]?.id ?? undefined,
            direction: sorting.value[0] ? (sorting.value[0].desc ? 'desc' : 'asc') : undefined,
        },
        { preserveState: true, replace: true },
    );
};

const resetFilters = () => {
    search.value = '';
    method.value = '';
    status.value = '';
    subdivision.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    sorting.value = defaultSorting;
    applyFilters();
};

const hasActiveFilters = () =>
    search.value || method.value || status.value || subdivision.value || dateFrom.value || dateTo.value;

// Sorting
const handleSortingChange = (updater: any) => {
    sorting.value = typeof updater === 'function' ? updater(sorting.value) : updater;
    applyFilters();
};

// Helpers
const clientFullName = (client: Client) =>
    [client.first_name, client.middle_name, client.last_name].filter(Boolean).join(' ');

const formatPeso = (amount: number | string) =>
    '₱ ' + Number(amount).toLocaleString('en-PH', { minimumFractionDigits: 2 });

const formatDate = (date: string | null) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' });
};

const getStatus = (s: string) =>
    PAYMENT_STATUS[s] ?? { label: s, classes: 'bg-gray-100 text-gray-500', dot: 'bg-gray-400' };

// Columns
const col = createColumnHelper<Payment>();

const columns = [
    col.accessor('paid_at', { header: 'Date', enableSorting: true }),
    col.accessor('or_number', { header: 'OR No.', enableSorting: false }),
    col.display({ id: 'client', header: 'Client', enableSorting: false }),
    col.display({ id: 'lot', header: 'Lot', enableSorting: false }),
    col.accessor('method', { header: 'Method', enableSorting: false }),
    col.accessor('amount', { header: 'Amount', enableSorting: true }),
    col.accessor('status', { header: 'Status', enableSorting: false }),
    col.display({ id: 'actions', header: '', enableSorting: false }),
];

// Table
const table = useVueTable({
    get data() { return props.payments.data; },
    columns,
    state: { get sorting() { return sorting.value; } },
    getCoreRowModel: getCoreRowModel(),
    onSortingChange: handleSortingChange,
    manualPagination: true,
    manualSorting: true,
    manualFiltering: true,
    pageCount: props.payments.last_page,
});
</script>

<template>

    <Head title="Payments" />

    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">

        <!-- ── Page Header ── -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Payments</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ payments.total }} payment records
                </p>
            </div>
            <Link v-if="can.create" :href="paymentRoute.create().url"
                class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-amber-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Record Payment
            </Link>
        </div>

        <!-- ── Summary ── -->
        <div class="grid grid-cols-2 gap-4">
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Collected</p>
                <p class="mt-2 text-xl font-bold text-emerald-600 dark:text-emerald-400">
                    {{ formatPeso(summary.total_collected) }}
                </p>
                <p class="mt-1 text-xs text-gray-400">Posted payments matching the filters below</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Payments</p>
                <p class="mt-2 text-xl font-bold text-gray-800 dark:text-gray-100">{{ summary.count }}</p>
                <p class="mt-1 text-xs text-gray-400">Voided payments are not counted</p>
            </div>
        </div>

        <!-- ── Filters ── -->
        <div class="flex flex-wrap items-center gap-3">

            <!-- Search -->
            <div class="relative">
                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" />
                </svg>
                <input v-model="search" type="text" placeholder="Search OR no., client, lot..."
                    class="w-72 rounded-lg border border-gray-200 bg-white py-2 pl-9 pr-3 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white dark:placeholder:text-gray-500" />
            </div>

            <!-- Date range -->
            <div class="flex items-center gap-2">
                <input v-model="dateFrom" type="date" aria-label="From date"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white" />
                <span class="text-sm text-gray-400">to</span>
                <input v-model="dateTo" type="date" aria-label="To date"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white" />
            </div>

            <!-- Method filter -->
            <select v-model="method"
                class="rounded-lg border border-gray-200 bg-white py-2 pl-3 pr-8 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                <option value="">All Methods</option>
                <option v-for="m in PAYMENT_METHODS" :key="m.value" :value="m.value">{{ m.label }}</option>
            </select>

            <!-- Status filter -->
            <select v-model="status"
                class="rounded-lg border border-gray-200 bg-white py-2 pl-3 pr-8 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                <option value="">All Status</option>
                <option value="posted">Posted</option>
                <option value="voided">Voided</option>
            </select>

            <!-- Subdivision filter -->
            <select v-model="subdivision"
                class="rounded-lg border border-gray-200 bg-white py-2 pl-3 pr-8 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                <option value="">All Subdivisions</option>
                <option v-for="sub in subdivisions" :key="sub" :value="sub">{{ sub }}</option>
            </select>

            <!-- Clear -->
            <button v-if="hasActiveFilters()" @click="resetFilters"
                class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-500 shadow-sm transition hover:bg-gray-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-gray-400 dark:hover:bg-zinc-800">
                Clear filters
            </button>
        </div>

        <!-- ── Table ── -->
        <div
            class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <table class="w-full text-sm">

                <!-- Head -->
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

                <!-- Body -->
                <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">

                    <!-- Empty state -->
                    <tr v-if="payments.data.length === 0">
                        <td :colspan="columns.length" class="px-5 py-16 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <p class="text-sm font-medium text-gray-400 dark:text-gray-500">No payments found</p>
                                <p class="text-xs text-gray-300 dark:text-gray-600">Try adjusting your search or filter
                                </p>
                            </div>
                        </td>
                    </tr>

                    <!-- Rows -->
                    <tr v-for="row in table.getRowModel().rows" :key="row.id"
                        class="group transition-colors hover:bg-amber-50/40 dark:hover:bg-amber-900/10"
                        :class="{ 'opacity-60': row.original.status === 'voided' }">

                        <!-- Date -->
                        <td class="px-5 py-4 text-gray-700 dark:text-gray-300">
                            {{ formatDate(row.original.paid_at) }}
                        </td>

                        <!-- OR No. -->
                        <td class="px-5 py-4 text-gray-600 dark:text-gray-300">
                            {{ row.original.or_number ?? '—' }}
                        </td>

                        <!-- Client -->
                        <td class="px-5 py-4">
                            <Link v-if="row.original.lot"
                                :href="clientRoute.show({ client: row.original.lot.client_id }).url"
                                class="text-sm font-medium text-gray-700 hover:text-amber-700 dark:text-gray-300 dark:hover:text-amber-400">
                                {{ clientFullName(row.original.lot.client) }}
                            </Link>
                            <span v-else class="text-gray-400">—</span>
                        </td>

                        <!-- Lot -->
                        <td class="px-5 py-4">
                            <template v-if="row.original.lot">
                                <Link :href="lotRoute.show({ lot: row.original.lot.id }).url"
                                    class="font-medium text-gray-900 hover:text-amber-700 dark:text-white dark:hover:text-amber-400">
                                    Blk {{ row.original.lot.block_number }} Lot {{ row.original.lot.lot_number }}
                                </Link>
                                <p class="text-xs text-gray-400">{{ row.original.lot.subdivision }}</p>
                            </template>
                            <span v-else class="text-gray-400">—</span>
                        </td>

                        <!-- Method -->
                        <td class="px-5 py-4 text-gray-600 dark:text-gray-300">
                            {{ methodLabel(row.original.method) }}
                        </td>

                        <!-- Amount -->
                        <td class="px-5 py-4 font-medium text-gray-800 dark:text-gray-100"
                            :class="{ 'line-through': row.original.status === 'voided' }">
                            {{ formatPeso(row.original.amount) }}
                        </td>

                        <!-- Status -->
                        <td class="px-5 py-4">
                            <span :class="getStatus(row.original.status).classes"
                                class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium">
                                <span :class="getStatus(row.original.status).dot"
                                    class="h-1.5 w-1.5 rounded-full"></span>
                                {{ getStatus(row.original.status).label }}
                            </span>
                        </td>

                        <!-- Actions -->
                        <td class="px-5 py-4">
                            <div
                                class="flex items-center justify-end gap-3 opacity-0 transition-opacity group-hover:opacity-100">
                                <Link :href="paymentRoute.show({ payment: row.original.id }).url"
                                    class="text-xs font-medium text-amber-600 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-300">
                                    View
                                </Link>
                            </div>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>

        <!-- ── Pagination ── -->
        <Pagination :links="payments.links" :from="payments.from" :to="payments.to" :total="payments.total"
            label="payments" />

    </div>
</template>
