<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import * as clientRoute from '@/routes/clients';

// ─── Types ────────────────────────────────────────────────────────────────────
type Breadcrumb = {
    title: string;
    href: string;
};

type Lot = {
    id: number;
    lot_number: string;
    block_number: string | null;
    subdivision: string;
    phase: string | null;
    lot_area: number;
    total_contract_price: number;
    down_payment: number;
    monthly_amortization: number;
    term_months: number;
    months_paid: number;
    start_date: string;
    next_due_date: string | null;
    status: 'active' | 'delinquent' | 'fully_paid' | 'cancelled';
};

type Client = {
    id: number;
    first_name: string;
    middle_name: string | null;
    last_name: string;
    email: string;
    phone_number: string | null;
    address: string | null;
    valid_id_type: string | null;
    valid_id_number: string | null;
    lots: Lot[];
    created_at: string;
};

// ─── Props ────────────────────────────────────────────────────────────────────
const props = defineProps<{
    client: Client;
    breadcrumbs: Breadcrumb[];      // ← dynamic, galing sa controller
}>();

// ─── Helpers ──────────────────────────────────────────────────────────────────
const fullName = [
    props.client.first_name,
    props.client.middle_name,
    props.client.last_name,
].filter(Boolean).join(' ');

const formatPeso = (amount: number) =>
    '₱ ' + amount.toLocaleString('en-PH', { minimumFractionDigits: 2 });

const formatDate = (date: string | null) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('en-PH', {
        year: 'numeric', month: 'long', day: 'numeric',
    });
};

const amountPaid = (lot: Lot) => (lot.months_paid * lot.monthly_amortization) + lot.down_payment;
const remainingBalance = (lot: Lot) => Math.max(0, lot.total_contract_price - amountPaid(lot));
const progressPercent = (lot: Lot) => Math.min(100, Math.round((amountPaid(lot) / lot.total_contract_price) * 100));
const remainingMonths = (lot: Lot) => Math.max(0, lot.term_months - lot.months_paid);

// ─── Status config ────────────────────────────────────────────────────────────
const LOT_STATUS: Record<string, { label: string; classes: string; dot: string }> = {
    active: { label: 'Active', classes: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:ring-emerald-800', dot: 'bg-emerald-500' },
    delinquent: { label: 'Delinquent', classes: 'bg-red-50 text-red-700 ring-1 ring-red-200 dark:bg-red-900/30 dark:text-red-400 dark:ring-red-800', dot: 'bg-red-500' },
    fully_paid: { label: 'Fully Paid', classes: 'bg-blue-50 text-blue-700 ring-1 ring-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:ring-blue-800', dot: 'bg-blue-500' },
    cancelled: { label: 'Cancelled', classes: 'bg-gray-100 text-gray-500 ring-1 ring-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700', dot: 'bg-gray-400' },
};

const getLotStatus = (s: string) =>
    LOT_STATUS[s] ?? { label: s, classes: 'bg-gray-100 text-gray-500', dot: 'bg-gray-400' };

// ─── Delete ───────────────────────────────────────────────────────────────────
const deleteClient = () => {
    if (!confirm(`Remove "${fullName}" from the system? This cannot be undone.`)) return;
    router.delete(clientRoute.destroy({ client: props.client.id }));
};
</script>

<template>

    <Head :title="fullName" />

    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">

        <!-- Page Header -->
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">
                    {{ fullName }}
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Client since {{ formatDate(client.created_at) }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <Link :href="clientRoute.edit({ client: client.id })"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-600 shadow-sm transition hover:bg-gray-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-gray-300 dark:hover:bg-zinc-800">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </Link>
                <button @click="deleteClient"
                    class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 shadow-sm transition hover:bg-red-50 dark:border-red-800 dark:bg-zinc-900 dark:text-red-400 dark:hover:bg-red-900/20">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Delete
                </button>
            </div>
        </div>

        <!-- Client Info Cards -->
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">

            <!-- Personal Info -->
            <div class="rounded-xl border border-gray-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-zinc-700">
                    <h2 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Personal Information
                    </h2>
                </div>
                <div class="space-y-4 p-5">
                    <div>
                        <p class="text-xs text-gray-400">Full Name</p>
                        <p class="mt-0.5 text-sm font-medium text-gray-800 dark:text-gray-100">{{ fullName }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Email</p>
                        <p class="mt-0.5 text-sm text-gray-700 dark:text-gray-300">{{ client.email }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Phone</p>
                        <p class="mt-0.5 text-sm text-gray-700 dark:text-gray-300">{{ client.phone_number ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Address</p>
                        <p class="mt-0.5 text-sm text-gray-700 dark:text-gray-300">{{ client.address ?? '—' }}</p>
                    </div>
                </div>
            </div>

            <!-- ID Verification -->
            <div class="rounded-xl border border-gray-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-zinc-700">
                    <h2 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        ID Verification
                    </h2>
                </div>
                <div class="space-y-4 p-5">
                    <div>
                        <p class="text-xs text-gray-400">ID Type</p>
                        <p class="mt-0.5 text-sm font-medium text-gray-800 dark:text-gray-100">
                            {{ client.valid_id_type ?? '—' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">ID Number</p>
                        <p class="mt-0.5 text-sm text-gray-700 dark:text-gray-300">
                            {{ client.valid_id_number ?? '—' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Lots Summary -->
            <div class="rounded-xl border border-gray-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-zinc-700">
                    <h2 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Lots Summary
                    </h2>
                </div>
                <div class="space-y-4 p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-xs text-gray-400">Total Lots</p>
                        <span
                            class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-amber-100 text-xs font-semibold text-amber-700 dark:bg-amber-900/40 dark:text-amber-400">
                            {{ client.lots.length }}
                        </span>
                    </div>
                    <div v-for="lot in client.lots" :key="lot.id" class="flex items-center justify-between">
                        <p class="text-xs text-gray-600 dark:text-gray-400">
                            Blk {{ lot.block_number }} Lot {{ lot.lot_number }}
                        </p>
                        <span :class="getLotStatus(lot.status).classes"
                            class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium">
                            <span :class="getLotStatus(lot.status).dot" class="h-1.5 w-1.5 rounded-full"></span>
                            {{ getLotStatus(lot.status).label }}
                        </span>
                    </div>
                    <div v-if="client.lots.length === 0" class="text-xs text-gray-400">
                        No lots assigned yet.
                    </div>
                </div>
            </div>

        </div>

        <!-- Lots Detail -->
        <div>
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                    Lot Records
                </h2>
                <!-- Future: Add Lot button here -->
            </div>

            <!-- Empty state -->
            <div v-if="client.lots.length === 0"
                class="flex flex-col items-center justify-center rounded-xl border border-dashed border-gray-200 py-16 dark:border-zinc-700">
                <svg class="h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                </svg>
                <p class="mt-2 text-sm font-medium text-gray-400 dark:text-gray-500">No lots assigned</p>
                <p class="text-xs text-gray-300 dark:text-gray-600">Lots will appear here once added</p>
            </div>

            <!-- Lot Cards -->
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                <div v-for="lot in client.lots" :key="lot.id"
                    class="rounded-xl border border-gray-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <!-- Lot Card Header -->
                    <div
                        class="flex items-start justify-between border-b border-gray-100 px-5 py-4 dark:border-zinc-700">
                        <div>
                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                                Block {{ lot.block_number }} · Lot {{ lot.lot_number }}
                            </p>
                            <p class="mt-0.5 text-xs text-gray-400">
                                {{ lot.subdivision }}
                                <span v-if="lot.phase"> · {{ lot.phase }}</span>
                            </p>
                        </div>
                        <span :class="getLotStatus(lot.status).classes"
                            class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium">
                            <span :class="getLotStatus(lot.status).dot" class="h-1.5 w-1.5 rounded-full"></span>
                            {{ getLotStatus(lot.status).label }}
                        </span>
                    </div>

                    <!-- Lot Card Body -->
                    <div class="p-5">

                        <!-- Payment Progress -->
                        <div class="mb-5">
                            <div class="mb-1.5 flex items-center justify-between">
                                <p class="text-xs text-gray-400">Payment Progress</p>
                                <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                                    {{ progressPercent(lot) }}%
                                </p>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-zinc-700">
                                <div class="h-full rounded-full transition-all" :class="lot.status === 'fully_paid'
                                    ? 'bg-blue-500'
                                    : lot.status === 'delinquent'
                                        ? 'bg-red-500'
                                        : 'bg-amber-500'" :style="{ width: `${progressPercent(lot)}%` }" />
                            </div>
                            <div class="mt-1.5 flex justify-between text-xs text-gray-400">
                                <span>{{ lot.months_paid }} of {{ lot.term_months }} months paid</span>
                                <span>{{ remainingMonths(lot) }} months left</span>
                            </div>
                        </div>

                        <!-- Financial Info Grid -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-400">Contract Price</p>
                                <p class="mt-0.5 text-sm font-semibold text-gray-800 dark:text-gray-100">
                                    {{ formatPeso(lot.total_contract_price) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">Down Payment</p>
                                <p class="mt-0.5 text-sm font-semibold text-gray-800 dark:text-gray-100">
                                    {{ formatPeso(lot.down_payment) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">Monthly Amortization</p>
                                <p class="mt-0.5 text-sm font-semibold text-gray-800 dark:text-gray-100">
                                    {{ formatPeso(lot.monthly_amortization) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">Total Paid</p>
                                <p class="mt-0.5 text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                                    {{ formatPeso(amountPaid(lot)) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">Remaining Balance</p>
                                <p class="mt-0.5 text-sm font-semibold" :class="remainingBalance(lot) > 0
                                    ? 'text-red-600 dark:text-red-400'
                                    : 'text-emerald-600 dark:text-emerald-400'">
                                    {{ formatPeso(remainingBalance(lot)) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">Lot Area</p>
                                <p class="mt-0.5 text-sm font-semibold text-gray-800 dark:text-gray-100">
                                    {{ lot.lot_area }} sqm
                                </p>
                            </div>
                        </div>

                        <!-- Dates -->
                        <div class="mt-4 grid grid-cols-2 gap-4 border-t border-gray-100 pt-4 dark:border-zinc-700">
                            <div>
                                <p class="text-xs text-gray-400">Start Date</p>
                                <p class="mt-0.5 text-xs font-medium text-gray-700 dark:text-gray-300">
                                    {{ formatDate(lot.start_date) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">Next Due Date</p>
                                <p class="mt-0.5 text-xs font-medium" :class="lot.next_due_date && new Date(lot.next_due_date) < new Date()
                                    ? 'text-red-600 dark:text-red-400'
                                    : 'text-gray-700 dark:text-gray-300'">
                                    {{ formatDate(lot.next_due_date) }}
                                    <span
                                        v-if="lot.next_due_date && new Date(lot.next_due_date) < new Date() && lot.status !== 'fully_paid'"
                                        class="ml-1 text-red-500">
                                        (Overdue)
                                    </span>
                                </p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
</template>
