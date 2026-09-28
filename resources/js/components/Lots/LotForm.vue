<script setup lang="ts">
import { computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import * as lotRoute from '@/routes/lots';
import * as reservationRoute from '@/routes/reservations';

// Types
type ClientOption = {
    id: string;
    name: string;
};

type AgentOption = {
    id: string;
    name: string;
};

type ProjectOption = {
    id: string;
    name: string;
};

type LotFormData = {
    client_id: string | null;
    agent_id: string | null;
    project_id: string | null;
    lot_number: string;
    block_number: string;
    phase: string;
    lot_area: string;
    total_contract_price: string;
    down_payment: string;
    monthly_amortization: string;
    term_months: string;
    months_paid: string;
    start_date: string;
    next_due_date: string;
    status: string;
    // Not saved on the lot: tells the controller which reservation to mark as confirmed
    reservation_id: string | null;
};

type ReservationPrefill = {
    id: string;
    client_id: string;
    agent_id: string | null;
    project_id: string | null;
    lot_number: string;
    block_number: string | null;
    phase: string | null;
    lot_area: number | null;
};

const props = defineProps<{
    clients: ClientOption[];
    agents: AgentOption[];
    projects: ProjectOption[];
    mode: 'create' | 'edit';
    lot?: Omit<LotFormData, 'reservation_id'> & { id: string; subdivision?: string };
    selectedClientId?: string | null;
    reservation?: ReservationPrefill | null;
}>();

// Laravel sends date-cast fields as full ISO timestamps (e.g. "2026-06-29T00:00:00.000000Z"),
// but <input type="date"> requires plain "yyyy-MM-dd" — trim it down here.
const toDateInputValue = (value: string | null | undefined) => {
    if (!value) return '';
    return value.slice(0, 10);
};

// Form
const form = useForm<LotFormData>({
    client_id:            props.lot?.client_id            ?? props.reservation?.client_id   ?? props.selectedClientId ?? null,
    agent_id:             props.lot?.agent_id             ?? props.reservation?.agent_id    ?? null,
    project_id:           props.lot?.project_id           ?? props.reservation?.project_id  ?? null,
    lot_number:           props.lot?.lot_number           ?? props.reservation?.lot_number  ?? '',
    block_number:         props.lot?.block_number         ?? props.reservation?.block_number ?? '',
    phase:                props.lot?.phase                ?? props.reservation?.phase       ?? '',
    lot_area:             props.lot?.lot_area             ?? props.reservation?.lot_area?.toString() ?? '',
    total_contract_price: props.lot?.total_contract_price ?? '',
    down_payment:         props.lot?.down_payment         ?? '',
    monthly_amortization: props.lot?.monthly_amortization ?? '',
    term_months:          props.lot?.term_months          ?? '',
    months_paid:          props.lot?.months_paid          ?? '0',
    start_date:           toDateInputValue(props.lot?.start_date),
    next_due_date:        toDateInputValue(props.lot?.next_due_date),
    status:               props.lot?.status               ?? 'active',
    reservation_id:       props.reservation?.id           ?? null,
});

// Cancel goes back to the reservation when creating a lot from one
const cancelHref = computed(() =>
    props.reservation
        ? reservationRoute.show({ reservation: props.reservation.id }).url
        : lotRoute.index().url,
);

// Submit handler
const submit = () => {
    if (props.mode === 'create') {
        form.post(lotRoute.store().url, { preserveScroll: true });
    } else {
        form.put(lotRoute.update({ lot: props.lot!.id }).url, { preserveScroll: true });
    }
};
</script>

<template>
<form @submit.prevent="submit" class="space-y-8">

        <!-- ── Section: Property Details ── -->
        <div class="rounded-xl border border-gray-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-zinc-700">
                <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Property Details</h2>
                <p class="mt-0.5 text-xs text-gray-400">Lot and location information</p>
            </div>
            <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">

                <!-- Client -->
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">
                        Client <span class="text-red-500">*</span>
                    </label>
                    <select
                        v-model="form.client_id"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                        :class="{ 'border-red-400': form.errors.client_id }"
                    >
                        <option :value="null">Select client</option>
                        <option v-for="client in clients" :key="client.id" :value="client.id">
                            {{ client.name }}
                        </option>
                    </select>
                    <p v-if="form.errors.client_id" class="mt-1 text-xs text-red-500">
                        {{ form.errors.client_id }}
                    </p>
                </div>

                <!-- Agent -->
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">
                        Agent <span class="text-gray-400">(optional)</span>
                    </label>
                    <select
                        v-model="form.agent_id"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                        :class="{ 'border-red-400': form.errors.agent_id }"
                    >
                        <option :value="null">No agent</option>
                        <option v-for="agent in agents" :key="agent.id" :value="agent.id">
                            {{ agent.name }}
                        </option>
                    </select>
                    <p v-if="form.errors.agent_id" class="mt-1 text-xs text-red-500">
                        {{ form.errors.agent_id }}
                    </p>
                </div>

                <!-- Project / Subdivision -->
                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">
                        Project / Subdivision <span class="text-red-500">*</span>
                    </label>
                    <select
                        v-model="form.project_id"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                        :class="{ 'border-red-400': form.errors.project_id }"
                    >
                        <option :value="null">Select project</option>
                        <option v-for="project in projects" :key="project.id" :value="project.id">
                            {{ project.name }}
                        </option>
                    </select>
                    <p v-if="form.errors.project_id" class="mt-1 text-xs text-red-500">
                        {{ form.errors.project_id }}
                    </p>
                </div>

                <!-- Block Number -->
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">
                        Block Number <span class="text-gray-400">(optional)</span>
                    </label>
                    <input
                        v-model="form.block_number"
                        type="text"
                        placeholder="e.g. Block 3"
                        maxlength="50"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder:text-zinc-500"
                        :class="{ 'border-red-400': form.errors.block_number }"
                    />
                    <p v-if="form.errors.block_number" class="mt-1 text-xs text-red-500">
                        {{ form.errors.block_number }}
                    </p>
                </div>

                <!-- Lot Number -->
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">
                        Lot Number <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.lot_number"
                        type="text"
                        placeholder="e.g. Lot 12"
                        maxlength="50"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder:text-zinc-500"
                        :class="{ 'border-red-400': form.errors.lot_number }"
                    />
                    <p v-if="form.errors.lot_number" class="mt-1 text-xs text-red-500">
                        {{ form.errors.lot_number }}
                    </p>
                </div>

                <!-- Phase -->
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">
                        Phase <span class="text-gray-400">(optional)</span>
                    </label>
                    <input
                        v-model="form.phase"
                        type="text"
                        placeholder="e.g. Phase 1"
                        maxlength="50"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder:text-zinc-500"
                        :class="{ 'border-red-400': form.errors.phase }"
                    />
                    <p v-if="form.errors.phase" class="mt-1 text-xs text-red-500">
                        {{ form.errors.phase }}
                    </p>
                </div>

                <!-- Lot Area -->
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">
                        Lot Area (sqm) <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.lot_area"
                        type="number"
                        placeholder="e.g. 120"
                        min="1"
                        step="0.01"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder:text-zinc-500"
                        :class="{ 'border-red-400': form.errors.lot_area }"
                    />
                    <p v-if="form.errors.lot_area" class="mt-1 text-xs text-red-500">
                        {{ form.errors.lot_area }}
                    </p>
                </div>

                <!-- Status -->
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select
                        v-model="form.status"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                        :class="{ 'border-red-400': form.errors.status }"
                    >
                        <option value="active">Active</option>
                        <option value="delinquent">Delinquent</option>
                        <option value="fully_paid">Fully Paid</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <p v-if="form.errors.status" class="mt-1 text-xs text-red-500">
                        {{ form.errors.status }}
                    </p>
                </div>

            </div>
        </div>

        <!-- ── Section: Payment Terms ── -->
        <div class="rounded-xl border border-gray-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-zinc-700">
                <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Payment Terms</h2>
                <p class="mt-0.5 text-xs text-gray-400">Financial details of the lot contract</p>
            </div>
            <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">

                <!-- Total Contract Price -->
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">
                        Total Contract Price (₱) <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.total_contract_price"
                        type="number"
                        placeholder="e.g. 1200000"
                        min="1"
                        step="0.01"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder:text-zinc-500"
                        :class="{ 'border-red-400': form.errors.total_contract_price }"
                    />
                    <p v-if="form.errors.total_contract_price" class="mt-1 text-xs text-red-500">
                        {{ form.errors.total_contract_price }}
                    </p>
                </div>

                <!-- Down Payment -->
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">
                        Down Payment (₱) <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.down_payment"
                        type="number"
                        placeholder="e.g. 120000"
                        min="0"
                        step="0.01"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder:text-zinc-500"
                        :class="{ 'border-red-400': form.errors.down_payment }"
                    />
                    <p v-if="form.errors.down_payment" class="mt-1 text-xs text-red-500">
                        {{ form.errors.down_payment }}
                    </p>
                </div>

                <!-- Monthly Amortization -->
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">
                        Monthly Amortization (₱) <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.monthly_amortization"
                        type="number"
                        placeholder="e.g. 9000"
                        min="1"
                        step="0.01"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder:text-zinc-500"
                        :class="{ 'border-red-400': form.errors.monthly_amortization }"
                    />
                    <p v-if="form.errors.monthly_amortization" class="mt-1 text-xs text-red-500">
                        {{ form.errors.monthly_amortization }}
                    </p>
                </div>

                <!-- Term Months -->
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">
                        Term (months) <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.term_months"
                        type="number"
                        placeholder="e.g. 120"
                        min="1"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder:text-zinc-500"
                        :class="{ 'border-red-400': form.errors.term_months }"
                    />
                    <p v-if="form.errors.term_months" class="mt-1 text-xs text-red-500">
                        {{ form.errors.term_months }}
                    </p>
                </div>

                <!-- Months Paid -->
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">
                        Months Paid <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.months_paid"
                        type="number"
                        placeholder="e.g. 6"
                        min="0"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder:text-zinc-500"
                        :class="{ 'border-red-400': form.errors.months_paid }"
                    />
                    <p v-if="form.errors.months_paid" class="mt-1 text-xs text-red-500">
                        {{ form.errors.months_paid }}
                    </p>
                </div>

                <!-- Start Date -->
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">
                        Start Date <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.start_date"
                        type="date"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                        :class="{ 'border-red-400': form.errors.start_date }"
                    />
                    <p v-if="form.errors.start_date" class="mt-1 text-xs text-red-500">
                        {{ form.errors.start_date }}
                    </p>
                </div>

                <!-- Next Due Date -->
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">
                        Next Due Date <span class="text-gray-400">(optional)</span>
                    </label>
                    <input
                        v-model="form.next_due_date"
                        type="date"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                        :class="{ 'border-red-400': form.errors.next_due_date }"
                    />
                    <p v-if="form.errors.next_due_date" class="mt-1 text-xs text-red-500">
                        {{ form.errors.next_due_date }}
                    </p>
                </div>

            </div>
        </div>

        <!-- ── Form Actions ── -->
        <div class="flex items-center justify-end gap-3">
            <Link
                :href="cancelHref"
                class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-600 shadow-sm transition hover:bg-gray-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-gray-300 dark:hover:bg-zinc-800"
            >
                Cancel
            </Link>
            <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-5 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
            >
                <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                </svg>
                {{ form.processing
                    ? 'Saving...'
                    : mode === 'edit'
                        ? 'Save Changes'
                        : reservation
                            ? 'Confirm & Save Lot'
                            : 'Add Lot' }}
            </button>
        </div>

    </form>
</template>
