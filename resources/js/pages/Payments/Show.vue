<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import * as paymentRoute from '@/routes/payments';
import * as lotRoute from '@/routes/lots';
import * as clientRoute from '@/routes/clients';
import { PAYMENT_STATUS, methodLabel } from '@/lib/payments';

// ── Types ──
type Client = {
    id: number;
    first_name: string;
    middle_name: string | null;
    last_name: string;
    email?: string | null;
    phone_number?: string | null;
};

type Payment = {
    id: string;
    or_number: string | null;
    amount: string | number;
    months_covered: number;
    paid_at: string;
    method: string | null;
    reference_number: string | null;
    notes: string | null;
    status: 'posted' | 'voided';
    voided_at: string | null;
    void_reason: string | null;
    created_at: string;
    // Loaded relations serialize under these keys (they replace the raw ids)
    recorded_by: { id: number; name: string } | null;
    voided_by: { id: number; name: string } | null;
    lot: {
        id: number;
        client_id: number;
        lot_number: string;
        block_number: string | null;
        subdivision: string;
        phase: string | null;
        client: Client;
    } | null;
};

const props = defineProps<{
    payment: Payment;
    can: { delete: boolean };
    breadcrumbs: { title: string; href: string }[];
}>();

// ── Helpers ──
const formatPeso = (amount: number | string) =>
    '₱ ' + Number(amount).toLocaleString('en-PH', { minimumFractionDigits: 2 });

const formatDate = (date: string | null) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' });
};

const formatDateTime = (date: string | null) => {
    if (!date) return '—';
    return new Date(date).toLocaleString('en-PH', {
        year: 'numeric', month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit',
    });
};

const clientFullName = (client: Client) =>
    [client.first_name, client.middle_name, client.last_name].filter(Boolean).join(' ');

const status = computed(
    () => PAYMENT_STATUS[props.payment.status] ?? { label: props.payment.status, classes: 'bg-gray-100 text-gray-500', dot: 'bg-gray-400' },
);

const isVoided = computed(() => props.payment.status === 'voided');

// ── Void ──
const showVoidForm = ref(false);

const voidForm = useForm({ void_reason: '' });

// DELETE /payments/{payment} voids the payment (the row is kept)
const submitVoid = () => {
    voidForm.delete(paymentRoute.destroy({ payment: props.payment.id }).url, {
        preserveScroll: true,
        onSuccess: () => {
            voidForm.reset();
            showVoidForm.value = false;
        },
    });
};

const cancelVoid = () => {
    voidForm.reset();
    voidForm.clearErrors();
    showVoidForm.value = false;
};
</script>

<template>

    <Head :title="payment.or_number ? `OR ${payment.or_number}` : 'Payment'" />

    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">

        <!-- ── Header ── -->
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">
                        {{ payment.or_number ? `OR ${payment.or_number}` : 'Payment' }}
                    </h1>
                    <span :class="status.classes"
                        class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium">
                        <span :class="status.dot" class="h-1.5 w-1.5 rounded-full"></span>
                        {{ status.label }}
                    </span>
                </div>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Paid on {{ formatDate(payment.paid_at) }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <Link :href="paymentRoute.index().url"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-gray-300 dark:hover:bg-zinc-800">
                    Back to Payments
                </Link>
                <button v-if="can.delete && !isVoided" @click="showVoidForm = !showVoidForm"
                    class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 shadow-sm transition hover:bg-red-50 dark:border-red-800 dark:bg-zinc-900 dark:text-red-400 dark:hover:bg-red-900/10">
                    Void Payment
                </button>
            </div>
        </div>

        <!-- ── Voided banner ── -->
        <div v-if="isVoided"
            class="rounded-xl border border-red-200 bg-red-50/60 p-5 dark:border-red-800 dark:bg-red-900/10">
            <p class="text-sm font-semibold text-red-700 dark:text-red-400">This payment was voided</p>
            <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                Reason: {{ payment.void_reason || '—' }}
            </p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                {{ formatDateTime(payment.voided_at) }}
                <span v-if="payment.voided_by"> · by {{ payment.voided_by.name }}</span>
            </p>
        </div>

        <!-- ── Void form (toggle) ── -->
        <div v-if="showVoidForm"
            class="rounded-xl border border-red-200 bg-red-50/40 p-5 dark:border-red-800 dark:bg-red-900/10">
            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">Void this payment?</p>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                The record stays for audit purposes.
                <template v-if="payment.months_covered > 0">
                    The lot goes back {{ payment.months_covered }} month{{ payment.months_covered > 1 ? 's' : '' }}
                    (months paid and next due date).
                </template>
            </p>
            <form @submit.prevent="submitVoid" class="mt-4 flex flex-col gap-3 sm:max-w-xl">
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Reason</label>
                    <input v-model="voidForm.void_reason" type="text" required maxlength="255"
                        placeholder="e.g. Wrong amount encoded"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white" />
                    <p v-if="voidForm.errors.void_reason" class="mt-1 text-xs text-red-500">
                        {{ voidForm.errors.void_reason }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" :disabled="voidForm.processing"
                        class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-red-700 disabled:opacity-50">
                        {{ voidForm.processing ? 'Voiding...' : 'Confirm Void' }}
                    </button>
                    <button type="button" @click="cancelVoid"
                        class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm text-gray-600 shadow-sm hover:bg-gray-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-gray-300 dark:hover:bg-zinc-800">
                        Cancel
                    </button>
                </div>
            </form>
        </div>

        <!-- ── Payment details ── -->
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="mb-4 text-sm font-medium text-gray-700 dark:text-gray-300">Payment Details</p>
            <dl class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm lg:grid-cols-4">
                <div>
                    <dt class="text-xs text-gray-400 dark:text-gray-500">Amount</dt>
                    <dd class="mt-1 text-lg font-bold text-emerald-600 dark:text-emerald-400"
                        :class="{ 'line-through opacity-60': isVoided }">
                        {{ formatPeso(payment.amount) }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400 dark:text-gray-500">Date Paid</dt>
                    <dd class="mt-1 font-medium text-gray-800 dark:text-gray-100">{{ formatDate(payment.paid_at) }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400 dark:text-gray-500">Method</dt>
                    <dd class="mt-1 font-medium text-gray-800 dark:text-gray-100">{{ methodLabel(payment.method) }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400 dark:text-gray-500">Months Covered</dt>
                    <dd class="mt-1 font-medium text-gray-800 dark:text-gray-100">{{ payment.months_covered }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400 dark:text-gray-500">OR No.</dt>
                    <dd class="mt-1 font-medium text-gray-800 dark:text-gray-100">{{ payment.or_number || '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400 dark:text-gray-500">Reference No.</dt>
                    <dd class="mt-1 font-medium text-gray-800 dark:text-gray-100">{{ payment.reference_number || '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400 dark:text-gray-500">Recorded By</dt>
                    <dd class="mt-1 font-medium text-gray-800 dark:text-gray-100">{{ payment.recorded_by?.name || '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400 dark:text-gray-500">Recorded On</dt>
                    <dd class="mt-1 font-medium text-gray-800 dark:text-gray-100">{{ formatDateTime(payment.created_at) }}</dd>
                </div>
                <div class="col-span-full">
                    <dt class="text-xs text-gray-400 dark:text-gray-500">Notes</dt>
                    <dd class="mt-1 text-gray-700 dark:text-gray-300">{{ payment.notes || '—' }}</dd>
                </div>
            </dl>
        </div>

        <!-- ── Lot & client ── -->
        <div v-if="payment.lot"
            class="rounded-xl border border-gray-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="mb-4 text-sm font-medium text-gray-700 dark:text-gray-300">Lot & Client</p>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Lot</p>
                    <Link :href="lotRoute.show({ lot: payment.lot.id }).url"
                        class="mt-1 block text-base font-semibold text-gray-800 hover:text-amber-700 dark:text-gray-100 dark:hover:text-amber-400">
                        Blk {{ payment.lot.block_number }} Lot {{ payment.lot.lot_number }}
                    </Link>
                    <p class="text-xs text-gray-400">
                        {{ payment.lot.subdivision }}<span v-if="payment.lot.phase"> · {{ payment.lot.phase }}</span>
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Client</p>
                    <Link :href="clientRoute.show({ client: payment.lot.client_id }).url"
                        class="mt-1 block text-base font-semibold text-gray-800 hover:text-amber-700 dark:text-gray-100 dark:hover:text-amber-400">
                        {{ clientFullName(payment.lot.client) }}
                    </Link>
                    <p class="text-xs text-gray-400">
                        {{ payment.lot.client.email }}
                        <span v-if="payment.lot.client.phone_number"> · {{ payment.lot.client.phone_number }}</span>
                    </p>
                </div>
            </div>
        </div>

    </div>
</template>
