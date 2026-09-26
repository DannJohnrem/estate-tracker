<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import * as projectRoute from '@/routes/projects';
import * as lotRoute from '@/routes/lots';
import * as clientRoute from '@/routes/clients';
import Pagination from '@/components/Pagination.vue';

type Client = { id: number; first_name: string; middle_name: string | null; last_name: string };

type Lot = {
    id: number;
    client_id: number;
    lot_number: string;
    block_number: string | null;
    phase: string | null;
    total_contract_price: number;
    monthly_amortization: number;
    months_paid: number;
    status: 'active' | 'delinquent' | 'fully_paid' | 'cancelled';
    client: Client;
};

type PaginatedLots = {
    data: Lot[];
    total: number;
    from: number | null;
    to: number | null;
    links: { url: string | null; label: string; active: boolean }[];
};

const props = defineProps<{
    project: { id: number; name: string; location: string | null; status: string };
    stats: {
        total_lots: number;
        active_lots: number;
        delinquent_lots: number;
        fully_paid_lots: number;
        cancelled_lots: number;
        total_contract_value: number;
        total_collected: number;
    };
    lots: PaginatedLots;
    breadcrumbs: { title: string; href: string }[];
}>();

const formatPeso = (amount: number | string) =>
    '₱ ' + Number(amount).toLocaleString('en-PH', { minimumFractionDigits: 2 });

const clientFullName = (client: Client) =>
    [client.first_name, client.middle_name, client.last_name].filter(Boolean).join(' ');

const LOT_STATUS: Record<string, { label: string; classes: string; dot: string }> = {
    active: { label: 'Active', classes: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:ring-emerald-800', dot: 'bg-emerald-500' },
    delinquent: { label: 'Delinquent', classes: 'bg-red-50 text-red-700 ring-1 ring-red-200 dark:bg-red-900/30 dark:text-red-400 dark:ring-red-800', dot: 'bg-red-500' },
    fully_paid: { label: 'Fully Paid', classes: 'bg-blue-50 text-blue-700 ring-1 ring-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:ring-blue-800', dot: 'bg-blue-500' },
    cancelled: { label: 'Cancelled', classes: 'bg-gray-100 text-gray-500 ring-1 ring-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700', dot: 'bg-gray-400' },
};

const getLotStatus = (s: string) =>
    LOT_STATUS[s] ?? { label: s, classes: 'bg-gray-100 text-gray-500', dot: 'bg-gray-400' };
</script>

<template>

    <Head :title="project.name" />

    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">

        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">{{ project.name }}</h1>
                <p v-if="project.location" class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ project.location }}</p>
            </div>
            <Link :href="projectRoute.index().url"
                class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm text-gray-600 shadow-sm hover:bg-gray-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-gray-300 dark:hover:bg-zinc-800">
                Back to Projects
            </Link>
        </div>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Lots</p>
                <p class="mt-2 text-xl font-bold text-gray-800 dark:text-gray-100">{{ stats.total_lots }}</p>
                <p class="mt-1 text-xs text-gray-400">
                    {{ stats.active_lots }} active · {{ stats.delinquent_lots }} delinquent · {{ stats.fully_paid_lots }} paid
                </p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Contract Value</p>
                <p class="mt-2 text-xl font-bold text-gray-800 dark:text-gray-100">{{ formatPeso(stats.total_contract_value) }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Collected</p>
                <p class="mt-2 text-xl font-bold text-emerald-600 dark:text-emerald-400">{{ formatPeso(stats.total_collected) }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Cancelled</p>
                <p class="mt-2 text-xl font-bold text-gray-500 dark:text-gray-400">{{ stats.cancelled_lots }}</p>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50 dark:border-zinc-700 dark:bg-zinc-800/60">
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Lot</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Client</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Contract Price</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
                    <tr v-for="lot in lots.data" :key="lot.id" class="hover:bg-amber-50/40 dark:hover:bg-amber-900/10">
                        <td class="px-5 py-4">
                            <Link :href="lotRoute.show({ lot: lot.id }).url"
                                class="font-medium text-gray-900 hover:text-amber-700 dark:text-white dark:hover:text-amber-400">
                                Blk {{ lot.block_number }} Lot {{ lot.lot_number }}
                            </Link>
                            <p v-if="lot.phase" class="text-xs text-gray-400">{{ lot.phase }}</p>
                        </td>
                        <td class="px-5 py-4">
                            <Link :href="clientRoute.show({ client: lot.client_id })"
                                class="text-gray-700 hover:text-amber-700 dark:text-gray-300 dark:hover:text-amber-400">
                                {{ clientFullName(lot.client) }}
                            </Link>
                        </td>
                        <td class="px-5 py-4 text-gray-600 dark:text-gray-300">{{ formatPeso(lot.total_contract_price) }}</td>
                        <td class="px-5 py-4">
                            <span :class="getLotStatus(lot.status).classes"
                                class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium">
                                <span :class="getLotStatus(lot.status).dot" class="h-1.5 w-1.5 rounded-full"></span>
                                {{ getLotStatus(lot.status).label }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :links="lots.links" :from="lots.from" :to="lots.to" :total="lots.total" label="lots" />

    </div>
</template>
