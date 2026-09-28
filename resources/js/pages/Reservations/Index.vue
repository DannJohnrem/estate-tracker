<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import * as reservationRoute from '@/routes/reservations';
import {
    RESERVATION_STATUS,
    RESERVATION_STATUS_OPTIONS,
    formatDate,
    formatPeso,
    isPastExpiry,
    lotLabel,
    personName,
} from '@/lib/reservations';

// ── Types ──
type Person = { id: string; first_name: string; middle_name: string | null; last_name: string };

type Reservation = {
    id: string;
    lot_number: string;
    block_number: string | null;
    subdivision: string;
    reservation_fee: number | null;
    reservation_date: string | null;
    expiry_date: string | null;
    status: 'pending' | 'confirmed' | 'expired' | 'cancelled';
    client: Person;
    agent: Person | null;
};

type PageLink = { url: string | null; label: string; active: boolean };

const props = defineProps<{
    reservations: {
        data: Reservation[];
        links: PageLink[];
        total: number;
        from: number | null;
        to: number | null;
    };
    filters: { search?: string; status?: string };
}>();

// ── Filters ──
const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');

const applyFilters = () => {
    router.get(
        reservationRoute.index().url,
        { search: search.value || undefined, status: status.value || undefined },
        { preserveState: true, replace: true },
    );
};

let timer: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(timer);
    timer = setTimeout(applyFilters, 300);
});
watch(status, applyFilters);

const statusOf = (r: Reservation) => RESERVATION_STATUS[r.status] ?? RESERVATION_STATUS.pending;
</script>

<template>
    <Head title="Reservations" />

    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">
        <!-- ── Header ── -->
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Reservations</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Lots on hold for clients who have not signed a contract yet.</p>
            </div>
            <Link
                :href="reservationRoute.create().url"
                class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-amber-700"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                New Reservation
            </Link>
        </div>

        <!-- ── Filters ── -->
        <div class="flex flex-wrap items-center gap-3">
            <input
                v-model="search"
                type="text"
                placeholder="Search lot, subdivision, or client..."
                class="w-full max-w-xs rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
            />
            <select
                v-model="status"
                class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
            >
                <option value="">All statuses</option>
                <option v-for="s in RESERVATION_STATUS_OPTIONS" :key="s.value" :value="s.value">{{ s.label }}</option>
            </select>
        </div>

        <!-- ── Table ── -->
        <div class="rounded-xl border border-gray-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="border-b border-gray-200 px-5 py-4 dark:border-zinc-700">
                <h2 class="font-medium text-gray-700 dark:text-gray-300">All Reservations</h2>
                <p class="mt-0.5 text-xs text-gray-400">{{ reservations.total }} reservation(s)</p>
            </div>

            <div v-if="reservations.data.length === 0" class="px-5 py-10 text-center text-sm text-gray-400">
                No reservations found.
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50 dark:border-zinc-800 dark:bg-zinc-800/50">
                            <th class="px-5 py-3 text-left text-xs font-medium uppercase text-gray-500">Lot</th>
                            <th class="px-5 py-3 text-left text-xs font-medium uppercase text-gray-500">Client</th>
                            <th class="px-5 py-3 text-left text-xs font-medium uppercase text-gray-500">Agent</th>
                            <th class="px-5 py-3 text-left text-xs font-medium uppercase text-gray-500">Reserved</th>
                            <th class="px-5 py-3 text-left text-xs font-medium uppercase text-gray-500">Expires</th>
                            <th class="px-5 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                            <th class="px-5 py-3 text-right text-xs font-medium uppercase text-gray-500">Fee</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
                        <tr v-for="r in reservations.data" :key="r.id">
                            <td class="px-5 py-3">
                                <p class="font-medium text-gray-800 dark:text-gray-100">{{ lotLabel(r) }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ r.subdivision }}</p>
                            </td>
                            <td class="px-5 py-3 text-gray-700 dark:text-gray-300">{{ personName(r.client) }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-400">{{ r.agent ? personName(r.agent) : '—' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-400">{{ formatDate(r.reservation_date) }}</td>
                            <td class="px-5 py-3" :class="isPastExpiry(r) ? 'font-medium text-red-600 dark:text-red-400' : 'text-gray-600 dark:text-gray-400'">
                                {{ formatDate(r.expiry_date) }}
                            </td>
                            <td class="px-5 py-3">
                                <span :class="statusOf(r).classes" class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium">
                                    <span :class="statusOf(r).dot" class="h-1.5 w-1.5 rounded-full"></span>
                                    {{ statusOf(r).label }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right font-medium text-gray-800 dark:text-gray-100">{{ formatPeso(r.reservation_fee) }}</td>
                            <td class="px-5 py-3 text-right">
                                <Link
                                    :href="reservationRoute.show({ reservation: r.id }).url"
                                    class="text-xs font-medium text-amber-600 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-300"
                                >
                                    View
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- ── Pagination ── -->
            <div v-if="reservations.links.length > 3" class="flex flex-wrap items-center justify-between gap-2 border-t border-gray-200 px-5 py-3 dark:border-zinc-700">
                <p class="text-xs text-gray-400">Showing {{ reservations.from ?? 0 }}–{{ reservations.to ?? 0 }} of {{ reservations.total }}</p>
                <div class="flex items-center gap-1">
                    <template v-for="(link, i) in reservations.links" :key="i">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            preserve-scroll
                            class="rounded-md px-2.5 py-1 text-xs"
                            :class="link.active
                                ? 'bg-amber-600 font-medium text-white'
                                : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-zinc-800'"
                            v-html="link.label"
                        />
                        <span v-else class="rounded-md px-2.5 py-1 text-xs text-gray-300 dark:text-zinc-600" v-html="link.label" />
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>
