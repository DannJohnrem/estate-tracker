<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { dashboard } from '@/routes';
import * as paymentRoute from '@/routes/payments';
import { PAYMENT_METHODS, todayISO } from '@/lib/payments';

// Layout
defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Payments', href: paymentRoute.index() },
            { title: 'Record Payment', href: '#' },
        ],
    },
});

type LotOption = {
    id: string;
    label: string;
    subdivision: string;
    client_name: string;
    monthly_amortization: number;
    remaining_balance: number;
    next_due_date: string | null;
    status: string;
};

const props = defineProps<{
    lots: LotOption[];
    selected_lot_id: number | null;
}>();

// Form
const form = useForm({
    lot_id: (props.lots.some((l) => l.id === props.selected_lot_id)
        ? props.selected_lot_id
        : null) as number | null,
    amount: '',
    paid_at: todayISO(),
    method: '',
    or_number: '',
    reference_number: '',
    notes: '',
});

// Lot picker
const lotSearch = ref('');
const showResults = ref(false);

const selectedLot = computed(() => props.lots.find((l) => l.id === form.lot_id) ?? null);

const results = computed(() => {
    const q = lotSearch.value.trim().toLowerCase();
    const list = q
        ? props.lots.filter((l) =>
            `${l.label} ${l.subdivision} ${l.client_name}`.toLowerCase().includes(q),
        )
        : props.lots;
    return list.slice(0, 8);
});

const pickLot = (lot: LotOption) => {
    form.lot_id = lot.id;
    lotSearch.value = '';
    showResults.value = false;
};

const clearLot = () => {
    form.lot_id = null;
};

// Same rule as the backend: whole months only
const monthsCovered = computed(() => {
    const lot = selectedLot.value;
    const amount = Number(form.amount);
    if (!lot || !amount || lot.monthly_amortization <= 0) return 0;
    return Math.floor(Math.round((amount / lot.monthly_amortization) * 1e6) / 1e6);
});

// Helpers
const formatPeso = (amount: number | string) =>
    '₱ ' + Number(amount).toLocaleString('en-PH', { minimumFractionDigits: 2 });

const formatDate = (date: string | null) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' });
};

const submit = () => {
    form.post(paymentRoute.store().url, { preserveScroll: true });
};
</script>

<template>

    <Head title="Record Payment" />

    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">

        <div>
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Record Payment</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Choose the lot, then enter the payment details</p>
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-6">

            <!-- ── Lot picker ── -->
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">Lot</p>

                <!-- Search (when no lot selected) -->
                <div v-if="!selectedLot" class="relative max-w-xl">
                    <input v-model="lotSearch" type="text" placeholder="Search client, lot, or subdivision..."
                        @focus="showResults = true" @blur="showResults = false" @input="showResults = true"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white dark:placeholder:text-gray-500" />

                    <ul v-if="showResults"
                        class="absolute z-10 mt-1 max-h-72 w-full overflow-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-900">
                        <li v-for="lot in results" :key="lot.id" @mousedown.prevent="pickLot(lot)"
                            class="cursor-pointer px-3 py-2 hover:bg-amber-50 dark:hover:bg-amber-900/10">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ lot.label }} · {{ lot.subdivision }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ lot.client_name }}</p>
                        </li>
                        <li v-if="results.length === 0" class="px-3 py-3 text-sm text-gray-400">
                            No matching lots
                        </li>
                    </ul>
                </div>

                <!-- Selected lot summary -->
                <div v-else class="flex flex-wrap items-start justify-between gap-4">
                    <div class="grid flex-1 grid-cols-2 gap-x-6 gap-y-3 text-sm lg:grid-cols-4">
                        <div>
                            <p class="text-xs text-gray-400 dark:text-gray-500">Lot</p>
                            <p class="mt-1 font-medium text-gray-800 dark:text-gray-100">
                                {{ selectedLot.label }}
                            </p>
                            <p class="text-xs text-gray-400">{{ selectedLot.subdivision }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 dark:text-gray-500">Client</p>
                            <p class="mt-1 font-medium text-gray-800 dark:text-gray-100">
                                {{ selectedLot.client_name }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 dark:text-gray-500">Monthly Amortization</p>
                            <p class="mt-1 font-medium text-gray-800 dark:text-gray-100">
                                {{ formatPeso(selectedLot.monthly_amortization) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 dark:text-gray-500">Balance / Next Due</p>
                            <p class="mt-1 font-medium text-red-600 dark:text-red-400">
                                {{ formatPeso(selectedLot.remaining_balance) }}
                            </p>
                            <p class="text-xs text-gray-400">Due {{ formatDate(selectedLot.next_due_date) }}</p>
                        </div>
                    </div>
                    <button type="button" @click="clearLot"
                        class="text-sm font-medium text-amber-600 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-300">
                        Change lot
                    </button>
                </div>

                <p v-if="form.errors.lot_id" class="mt-2 text-xs text-red-500">{{ form.errors.lot_id }}</p>
            </div>

            <!-- ── Payment details ── -->
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="mb-4 text-sm font-medium text-gray-700 dark:text-gray-300">Payment Details</p>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Amount</label>
                        <input v-model="form.amount" type="number" step="0.01" min="1" required
                            class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white" />
                        <p v-if="form.errors.amount" class="mt-1 text-xs text-red-500">{{ form.errors.amount }}</p>
                        <p v-else-if="selectedLot && form.amount" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            <template v-if="monthsCovered > 0">
                                Covers {{ monthsCovered }} month{{ monthsCovered > 1 ? 's' : '' }}; due date moves forward.
                            </template>
                            <template v-else>
                                Below the monthly amortization: it will be recorded but won't advance the due date.
                            </template>
                        </p>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Date Paid</label>
                        <input v-model="form.paid_at" type="date" required
                            class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white" />
                        <p v-if="form.errors.paid_at" class="mt-1 text-xs text-red-500">{{ form.errors.paid_at }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Method (optional)</label>
                        <select v-model="form.method"
                            class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                            <option value="">— Select —</option>
                            <option v-for="m in PAYMENT_METHODS" :key="m.value" :value="m.value">{{ m.label }}</option>
                        </select>
                        <p v-if="form.errors.method" class="mt-1 text-xs text-red-500">{{ form.errors.method }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">OR No. (optional)</label>
                        <input v-model="form.or_number" type="text"
                            class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white" />
                        <p v-if="form.errors.or_number" class="mt-1 text-xs text-red-500">{{ form.errors.or_number }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Reference No. (optional)</label>
                        <input v-model="form.reference_number" type="text"
                            class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white" />
                        <p v-if="form.errors.reference_number" class="mt-1 text-xs text-red-500">{{ form.errors.reference_number }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Notes (optional)</label>
                        <input v-model="form.notes" type="text"
                            class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white" />
                        <p v-if="form.errors.notes" class="mt-1 text-xs text-red-500">{{ form.errors.notes }}</p>
                    </div>
                </div>
            </div>

            <!-- ── Actions ── -->
            <div class="flex items-center gap-2">
                <button type="submit" :disabled="form.processing || !form.lot_id"
                    class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-amber-700 disabled:opacity-50">
                    {{ form.processing ? 'Saving...' : 'Save Payment' }}
                </button>
                <Link :href="paymentRoute.index().url"
                    class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm text-gray-600 shadow-sm hover:bg-gray-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-gray-300 dark:hover:bg-zinc-800">
                    Cancel
                </Link>
            </div>
        </form>
    </div>
</template>
