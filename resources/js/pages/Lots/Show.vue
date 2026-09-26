<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import * as lotRoute from '@/routes/lots';
import * as clientRoute from '@/routes/clients';
import * as agentRoute from '@/routes/agents';
import { ref, computed } from 'vue';
import * as paymentRoute from '@/routes/payments';
import { PAYMENT_METHODS, PAYMENT_STATUS, methodLabel, todayISO } from '@/lib/payments';

// ── Types ──
type Client = {
    id: number;
    first_name: string;
    middle_name: string | null;
    last_name: string;
    full_name: string;
    email?: string;
    phone_number?: string;
};

type Agent = {
    id: string;
    first_name: string;
    middle_name: string | null;
    last_name: string;
    commission_rate: number;
};

type Payment = {
    id: string;
    or_number: string | null;
    amount: string | number;
    paid_at: string;
    method: string | null;
    reference_number: string | null;
    notes: string | null;
    status: 'posted' | 'voided';
    created_at: string;
};

type Lot = {
    id: number;
    client_id: number;
    agent_id: string | null;
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
    start_date: string | null;
    next_due_date: string | null;
    status: 'active' | 'delinquent' | 'fully_paid' | 'cancelled';
    client: Client;
    agent: Agent | null;
    payments: Payment[];
};

const props = defineProps<{
    lot: Lot;
    breadcrumbs: { title: string; href: string }[];
}>();

// ── Computed money values ──
const amountPaid = computed(() =>
    (props.lot.months_paid * props.lot.monthly_amortization) + Number(props.lot.down_payment)
);

const remainingBalance = computed(() =>
    Math.max(0, props.lot.total_contract_price - amountPaid.value)
);

const remainingMonths = computed(() =>
    Math.max(0, props.lot.term_months - props.lot.months_paid)
);

const progressPercent = computed(() => {
    if (props.lot.total_contract_price <= 0) return 0;
    return Math.min(100, Math.round((amountPaid.value / props.lot.total_contract_price) * 100));
});

const agentFullName = computed(() => {
    if (!props.lot.agent) return null;
    return [props.lot.agent.first_name, props.lot.agent.middle_name, props.lot.agent.last_name]
        .filter(Boolean)
        .join(' ');
});

// ── Formatters ──
const formatPeso = (amount: number | string) =>
    '₱ ' + Number(amount).toLocaleString('en-PH', { minimumFractionDigits: 2 });

const formatDate = (date: string | null) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' });
};

// ── Status badge config (matches Index.vue) ──
const LOT_STATUS: Record<string, { label: string; classes: string; dot: string }> = {
    active:     { label: 'Active',     classes: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:ring-emerald-800', dot: 'bg-emerald-500' },
    delinquent: { label: 'Delinquent', classes: 'bg-red-50 text-red-700 ring-1 ring-red-200 dark:bg-red-900/30 dark:text-red-400 dark:ring-red-800', dot: 'bg-red-500' },
    fully_paid: { label: 'Fully Paid', classes: 'bg-blue-50 text-blue-700 ring-1 ring-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:ring-blue-800', dot: 'bg-blue-500' },
    cancelled:  { label: 'Cancelled',  classes: 'bg-gray-100 text-gray-500 ring-1 ring-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700', dot: 'bg-gray-400' },
};

const lotStatus = computed(() => LOT_STATUS[props.lot.status] ?? { label: props.lot.status, classes: 'bg-gray-100 text-gray-500', dot: 'bg-gray-400' });

const isOverdue = computed(() =>
    props.lot.status === 'active' &&
    props.lot.next_due_date &&
    new Date(props.lot.next_due_date) < new Date()
);

// ── Record Payment form ──
const showPaymentForm = ref(false);

const paymentForm = useForm({
    amount: '',
    paid_at: todayISO(),
    method: '',
    or_number: '',
    reference_number: '',
    notes: '',
});

const submitPayment = () => {
    paymentForm.post(`/lots/${props.lot.id}/payments`, {
        preserveScroll: true,
        onSuccess: () => {
            paymentForm.reset();
            paymentForm.paid_at = todayISO();
            showPaymentForm.value = false;
        },
    });
};

const cancelPaymentForm = () => {
    paymentForm.reset();
    paymentForm.clearErrors();
    showPaymentForm.value = false;
};
</script>

<template>
    <Head :title="`Blk ${lot.block_number} Lot ${lot.lot_number}`" />

    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">

        <!-- ── Header ── -->
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">
                        Blk {{ lot.block_number }} Lot {{ lot.lot_number }}
                    </h1>
                    <span
                        :class="lotStatus.classes"
                        class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium"
                    >
                        <span :class="lotStatus.dot" class="h-1.5 w-1.5 rounded-full"></span>
                        {{ lotStatus.label }}
                    </span>
                    <span
                        v-if="isOverdue"
                        class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-700 ring-1 ring-red-200 dark:bg-red-900/30 dark:text-red-400 dark:ring-red-800"
                    >
                        Overdue
                    </span>
                </div>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ lot.subdivision }}
                    <span v-if="lot.phase"> · {{ lot.phase }}</span>
                    · {{ lot.lot_area }} sqm
                </p>
            </div>

            <div class="flex items-center gap-2">
                <Link
                    :href="lotRoute.edit({ lot: lot.id }).url"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-gray-300 dark:hover:bg-zinc-800"
                >
                    Edit Lot
                </Link>
                <button
                    v-if="lot.status !== 'fully_paid' && lot.status !== 'cancelled'"
                    @click="showPaymentForm = !showPaymentForm"
                    class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-amber-700"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Record Payment
                </button>
            </div>
        </div>

        <!-- ── Client + Agent cards ── -->
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <p class="mb-2 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Client</p>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <Link
                            :href="clientRoute.show({ client: lot.client_id }).url"
                            class="text-lg font-semibold text-gray-800 hover:text-amber-700 dark:text-gray-100 dark:hover:text-amber-400"
                        >
                            {{ lot.client.full_name }}
                        </Link>
                        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                            {{ lot.client.email }}
                            <span v-if="lot.client.phone_number"> · {{ lot.client.phone_number }}</span>
                        </p>
                    </div>
                    <Link
                        :href="clientRoute.show({ client: lot.client_id }).url"
                        class="text-sm font-medium text-amber-600 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-300"
                    >
                        View Client →
                    </Link>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <p class="mb-2 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Handling Agent</p>
                <div v-if="lot.agent" class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <p class="text-lg font-semibold text-gray-800 dark:text-gray-100">{{ agentFullName }}</p>
                        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ lot.agent.commission_rate }}% commission</p>
                    </div>
                    <Link
                        :href="agentRoute.show({ agent: lot.agent.id }).url"
                        class="text-sm font-medium text-amber-600 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-300"
                    >
                        View Agent →
                    </Link>
                </div>
                <p v-else class="text-sm text-gray-400">No agent assigned</p>
            </div>
        </div>

        <!-- ── Contract summary ── -->
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Contract Price</p>
                <p class="mt-2 text-xl font-bold text-gray-800 dark:text-gray-100">{{ formatPeso(lot.total_contract_price) }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Amount Paid</p>
                <p class="mt-2 text-xl font-bold text-emerald-600 dark:text-emerald-400">{{ formatPeso(amountPaid) }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Remaining Balance</p>
                <p class="mt-2 text-xl font-bold text-red-600 dark:text-red-400">{{ formatPeso(remainingBalance) }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Next Due Date</p>
                <p class="mt-2 text-xl font-bold" :class="isOverdue ? 'text-red-600 dark:text-red-400' : 'text-gray-800 dark:text-gray-100'">
                    {{ formatDate(lot.next_due_date) }}
                </p>
            </div>
        </div>

        <!-- ── Progress bar ── -->
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-2 flex items-center justify-between text-sm">
                <p class="font-medium text-gray-700 dark:text-gray-300">Payment Progress</p>
                <p class="text-gray-500 dark:text-gray-400">
                    {{ lot.months_paid }} / {{ lot.term_months }} months paid
                    <span class="text-gray-400 dark:text-gray-500">({{ remainingMonths }} remaining)</span>
                </p>
            </div>
            <div class="h-2.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-zinc-800">
                <div
                    class="h-full rounded-full bg-amber-500 transition-all"
                    :style="{ width: progressPercent + '%' }"
                ></div>
            </div>
            <p class="mt-1.5 text-right text-xs text-gray-400 dark:text-gray-500">{{ progressPercent }}%</p>
        </div>

        <!-- ── Lot details ── -->
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="mb-4 text-sm font-medium text-gray-700 dark:text-gray-300">Lot Details</p>
            <dl class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm lg:grid-cols-4">
                <div>
                    <dt class="text-xs text-gray-400 dark:text-gray-500">Down Payment</dt>
                    <dd class="mt-1 font-medium text-gray-800 dark:text-gray-100">{{ formatPeso(lot.down_payment) }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400 dark:text-gray-500">Monthly Amortization</dt>
                    <dd class="mt-1 font-medium text-gray-800 dark:text-gray-100">{{ formatPeso(lot.monthly_amortization) }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400 dark:text-gray-500">Term</dt>
                    <dd class="mt-1 font-medium text-gray-800 dark:text-gray-100">{{ lot.term_months }} months</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400 dark:text-gray-500">Start Date</dt>
                    <dd class="mt-1 font-medium text-gray-800 dark:text-gray-100">{{ formatDate(lot.start_date) }}</dd>
                </div>
            </dl>
        </div>

        <!-- ── Record Payment form (toggle) ── -->
        <div
            v-if="showPaymentForm"
            class="rounded-xl border border-amber-200 bg-amber-50/40 p-5 dark:border-amber-800 dark:bg-amber-900/10"
        >
            <p class="mb-4 text-sm font-semibold text-gray-800 dark:text-gray-100">Record New Payment</p>
            <form @submit.prevent="submitPayment" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Amount</label>
                    <input
                        v-model="paymentForm.amount"
                        type="number"
                        step="0.01"
                        min="1"
                        required
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
                    />
                    <p v-if="paymentForm.errors.amount" class="mt-1 text-xs text-red-500">{{ paymentForm.errors.amount }}</p>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Date Paid</label>
                    <input
                        v-model="paymentForm.paid_at"
                        type="date"
                        required
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
                    />
                    <p v-if="paymentForm.errors.paid_at" class="mt-1 text-xs text-red-500">{{ paymentForm.errors.paid_at }}</p>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Method (optional)</label>
                    <select
                        v-model="paymentForm.method"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
                    >
                        <option value="">— Select —</option>
                        <option v-for="m in PAYMENT_METHODS" :key="m.value" :value="m.value">{{ m.label }}</option>
                    </select>
                    <p v-if="paymentForm.errors.method" class="mt-1 text-xs text-red-500">{{ paymentForm.errors.method }}</p>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">OR No. (optional)</label>
                    <input
                        v-model="paymentForm.or_number"
                        type="text"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
                    />
                    <p v-if="paymentForm.errors.or_number" class="mt-1 text-xs text-red-500">{{ paymentForm.errors.or_number }}</p>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Reference No. (optional)</label>
                    <input
                        v-model="paymentForm.reference_number"
                        type="text"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
                    />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Notes (optional)</label>
                    <input
                        v-model="paymentForm.notes"
                        type="text"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
                    />
                </div>

                <div class="col-span-full flex items-center gap-2">
                    <button
                        type="submit"
                        :disabled="paymentForm.processing"
                        class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-amber-700 disabled:opacity-50"
                    >
                        {{ paymentForm.processing ? 'Saving...' : 'Save Payment' }}
                    </button>
                    <button
                        type="button"
                        @click="cancelPaymentForm"
                        class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm text-gray-600 shadow-sm hover:bg-gray-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-gray-300 dark:hover:bg-zinc-800"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>

        <!-- ── Payment history ── -->
        <div class="rounded-xl border border-gray-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="border-b border-gray-200 px-5 py-4 dark:border-zinc-700">
                <h2 class="font-medium text-gray-700 dark:text-gray-300">Payment History</h2>
                <p class="mt-0.5 text-xs text-gray-400">{{ lot.payments.length }} payment(s) recorded</p>
            </div>

            <div v-if="lot.payments.length === 0" class="px-5 py-10 text-center text-sm text-gray-400">
                No payments recorded yet.
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50 dark:border-zinc-800 dark:bg-zinc-800/50">
                            <th class="px-5 py-3 text-left text-xs font-medium uppercase text-gray-500">Date</th>
                            <th class="px-5 py-3 text-left text-xs font-medium uppercase text-gray-500">OR No.</th>
                            <th class="px-5 py-3 text-left text-xs font-medium uppercase text-gray-500">Method</th>
                            <th class="px-5 py-3 text-left text-xs font-medium uppercase text-gray-500">Notes</th>
                            <th class="px-5 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                            <th class="px-5 py-3 text-right text-xs font-medium uppercase text-gray-500">Amount</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
                        <tr v-for="payment in lot.payments" :key="payment.id"
                            :class="{ 'opacity-60': payment.status === 'voided' }">
                            <td class="px-5 py-3 text-gray-700 dark:text-gray-300">{{ formatDate(payment.paid_at) }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-400">{{ payment.or_number || '—' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-400">{{ methodLabel(payment.method) }}</td>
                            <td class="px-5 py-3 text-gray-500 dark:text-gray-400">{{ payment.notes || '—' }}</td>
                            <td class="px-5 py-3">
                                <span :class="(PAYMENT_STATUS[payment.status] ?? PAYMENT_STATUS.posted).classes"
                                    class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium">
                                    <span :class="(PAYMENT_STATUS[payment.status] ?? PAYMENT_STATUS.posted).dot"
                                        class="h-1.5 w-1.5 rounded-full"></span>
                                    {{ (PAYMENT_STATUS[payment.status] ?? PAYMENT_STATUS.posted).label }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right font-medium text-emerald-600 dark:text-emerald-400"
                                :class="{ 'line-through': payment.status === 'voided' }">
                                {{ formatPeso(payment.amount) }}
                            </td>
                            <td class="px-5 py-3 text-right">
                                <Link :href="paymentRoute.show({ payment: payment.id }).url"
                                    class="text-xs font-medium text-amber-600 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-300">
                                    View
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</template>
