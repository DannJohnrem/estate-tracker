<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import * as reservationRoute from '@/routes/reservations';
import * as clientRoute from '@/routes/clients';
import * as agentRoute from '@/routes/agents';
import * as lotRoute from '@/routes/lots';
import {
    RESERVATION_STATUS,
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
    client_id: string;
    agent_id: string | null;
    project_id: string | null;
    converted_lot_id: string | null;
    lot_number: string;
    block_number: string | null;
    subdivision: string;
    phase: string | null;
    lot_area: number | null;
    reservation_fee: number | null;
    reservation_date: string | null;
    expiry_date: string | null;
    status: 'pending' | 'confirmed' | 'expired' | 'cancelled';
    notes: string | null;
    client: Person & { email?: string; phone_number?: string | null };
    agent: (Person & { commission_rate?: number }) | null;
    project: { id: string; name: string } | null;
    converted_lot: { id: string; lot_number: string; block_number: string | null } | null;
};

const props = defineProps<{
    reservation: Reservation;
    breadcrumbs: { title: string; href: string }[];
}>();

// ── Computed ──
const status = computed(() => RESERVATION_STATUS[props.reservation.status] ?? RESERVATION_STATUS.pending);
const pastExpiry = computed(() => isPastExpiry(props.reservation));
const isPending = computed(() => props.reservation.status === 'pending');

// ── Actions ──
const cancelReservation = () => {
    if (!confirm('Cancel this reservation? This cannot be undone.')) return;
    router.patch(reservationRoute.cancel({ reservation: props.reservation.id }).url, {}, { preserveScroll: true });
};
</script>

<template>
    <Head :title="lotLabel(reservation)" />

    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">
        <!-- ── Header ── -->
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">{{ lotLabel(reservation) }}</h1>
                    <span :class="status.classes" class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium">
                        <span :class="status.dot" class="h-1.5 w-1.5 rounded-full"></span>
                        {{ status.label }}
                    </span>
                    <span
                        v-if="pastExpiry"
                        class="inline-flex items-center rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-700 ring-1 ring-red-200 dark:bg-red-900/30 dark:text-red-400 dark:ring-red-800"
                    >
                        Hold period ended
                    </span>
                </div>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ reservation.subdivision }}
                    <span v-if="reservation.phase"> · {{ reservation.phase }}</span>
                    <span v-if="reservation.lot_area"> · {{ reservation.lot_area }} sqm</span>
                </p>
            </div>

            <div v-if="isPending" class="flex flex-wrap items-center gap-2">
                <Link
                    :href="lotRoute.create({ query: { reservation_id: reservation.id } }).url"
                    class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-emerald-700"
                >
                    Confirm &amp; Create Lot
                </Link>
                <Link
                    :href="reservationRoute.edit({ reservation: reservation.id }).url"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-gray-300 dark:hover:bg-zinc-800"
                >
                    Edit
                </Link>
                <button
                    @click="cancelReservation"
                    class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 shadow-sm transition hover:bg-red-50 dark:border-red-800 dark:bg-zinc-900 dark:text-red-400 dark:hover:bg-red-900/20"
                >
                    Cancel Reservation
                </button>
            </div>
        </div>

        <!-- ── Client + Agent cards ── -->
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <p class="mb-2 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Client</p>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <p class="text-lg font-semibold text-gray-800 dark:text-gray-100">{{ personName(reservation.client) }}</p>
                        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                            {{ reservation.client.email }}
                            <span v-if="reservation.client.phone_number"> · {{ reservation.client.phone_number }}</span>
                        </p>
                    </div>
                    <Link
                        :href="clientRoute.show({ client: reservation.client_id }).url"
                        class="text-sm font-medium text-amber-600 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-300"
                    >
                        View Client →
                    </Link>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <p class="mb-2 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Handling Agent</p>
                <div v-if="reservation.agent" class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <p class="text-lg font-semibold text-gray-800 dark:text-gray-100">{{ personName(reservation.agent) }}</p>
                        <p v-if="reservation.agent.commission_rate !== undefined" class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                            {{ reservation.agent.commission_rate }}% commission
                        </p>
                    </div>
                    <Link
                        :href="agentRoute.show({ agent: reservation.agent.id }).url"
                        class="text-sm font-medium text-amber-600 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-300"
                    >
                        View Agent →
                    </Link>
                </div>
                <p v-else class="text-sm text-gray-400">No agent assigned</p>
            </div>
        </div>

        <!-- ── Reservation details ── -->
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="mb-4 text-sm font-medium text-gray-700 dark:text-gray-300">Reservation Details</p>
            <dl class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm lg:grid-cols-4">
                <div>
                    <dt class="text-xs text-gray-400 dark:text-gray-500">Reservation Fee</dt>
                    <dd class="mt-1 font-medium text-gray-800 dark:text-gray-100">{{ formatPeso(reservation.reservation_fee) }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400 dark:text-gray-500">Reservation Date</dt>
                    <dd class="mt-1 font-medium text-gray-800 dark:text-gray-100">{{ formatDate(reservation.reservation_date) }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400 dark:text-gray-500">Expiry Date</dt>
                    <dd class="mt-1 font-medium" :class="pastExpiry ? 'text-red-600 dark:text-red-400' : 'text-gray-800 dark:text-gray-100'">
                        {{ formatDate(reservation.expiry_date) }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400 dark:text-gray-500">Project</dt>
                    <dd class="mt-1 font-medium text-gray-800 dark:text-gray-100">{{ reservation.project?.name ?? '—' }}</dd>
                </div>
                <div class="col-span-full">
                    <dt class="text-xs text-gray-400 dark:text-gray-500">Notes</dt>
                    <dd class="mt-1 text-gray-700 dark:text-gray-300">{{ reservation.notes || '—' }}</dd>
                </div>
            </dl>
        </div>

        <!-- ── Converted lot (after confirm) ── -->
        <div
            v-if="reservation.converted_lot"
            class="rounded-xl border border-emerald-200 bg-emerald-50/40 p-5 dark:border-emerald-800 dark:bg-emerald-900/10"
        >
            <p class="text-sm font-medium text-gray-800 dark:text-gray-100">This reservation was converted to a lot.</p>
            <Link
                :href="lotRoute.show({ lot: reservation.converted_lot.id }).url"
                class="mt-1 inline-block text-sm font-medium text-emerald-700 hover:text-emerald-900 dark:text-emerald-400 dark:hover:text-emerald-300"
            >
                View Lot →
            </Link>
        </div>
    </div>
</template>
