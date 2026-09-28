<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import * as reservationRoute from '@/routes/reservations';
import { dateInputValue } from '@/lib/reservations';

// ── Types ──
type Option = { id: string; name: string };

type ReservationData = {
    id: string;
    client_id: string;
    agent_id: string | null;
    project_id: string | null;
    lot_number: string;
    block_number: string | null;
    subdivision: string;
    phase: string | null;
    lot_area: number | null;
    reservation_fee: number | null;
    reservation_date: string | null;
    expiry_date: string | null;
    notes: string | null;
};

const props = defineProps<{
    clients: Option[];
    agents: Option[];
    projects: Option[];
    reservation?: ReservationData | null;
    selectedClientId?: string | null;
}>();

const isEdit = computed(() => !!props.reservation);

// ── Form ──
const form = useForm({
    client_id: props.reservation?.client_id ?? props.selectedClientId ?? '',
    agent_id: props.reservation?.agent_id ?? '',
    project_id: props.reservation?.project_id ?? '',
    lot_number: props.reservation?.lot_number ?? '',
    block_number: props.reservation?.block_number ?? '',
    subdivision: props.reservation?.subdivision ?? '',
    phase: props.reservation?.phase ?? '',
    lot_area: props.reservation?.lot_area?.toString() ?? '',
    reservation_fee: props.reservation?.reservation_fee?.toString() ?? '',
    reservation_date: dateInputValue(props.reservation?.reservation_date),
    expiry_date: dateInputValue(props.reservation?.expiry_date),
    notes: props.reservation?.notes ?? '',
});

// Convenience: picking a project fills the subdivision if it's still empty
watch(
    () => form.project_id,
    (id) => {
        if (!id || form.subdivision) return;
        const project = props.projects.find((p) => p.id === id);
        if (project) form.subdivision = project.name;
    },
);

const submit = () => {
    if (isEdit.value && props.reservation) {
        form.put(reservationRoute.update({ reservation: props.reservation.id }).url);
    } else {
        form.post(reservationRoute.store().url);
    }
};

const cancelHref = computed(() =>
    isEdit.value && props.reservation
        ? reservationRoute.show({ reservation: props.reservation.id }).url
        : reservationRoute.index().url,
);

// ── Shared classes ──
const inputClass =
    'w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white';
const labelClass = 'mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400';
const cardClass = 'rounded-xl border border-gray-200 bg-white dark:border-zinc-700 dark:bg-zinc-900';
const errorClass = 'mt-1 text-xs text-red-500';
</script>

<template>
    <form @submit.prevent="submit" class="flex flex-col gap-6">
        <!-- ── Client & Agent ── -->
        <div :class="cardClass">
            <div class="border-b border-gray-200 px-5 py-4 dark:border-zinc-700">
                <h2 class="font-medium text-gray-700 dark:text-gray-300">Client &amp; Agent</h2>
                <p class="mt-0.5 text-xs text-gray-400">Who is reserving, and who is handling the reservation.</p>
            </div>
            <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label :class="labelClass">Client</label>
                    <select v-model="form.client_id" required :class="inputClass">
                        <option value="">— Select client —</option>
                        <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                    <p v-if="form.errors.client_id" :class="errorClass">{{ form.errors.client_id }}</p>
                </div>
                <div>
                    <label :class="labelClass">Agent (optional)</label>
                    <select v-model="form.agent_id" :class="inputClass">
                        <option value="">— No agent —</option>
                        <option v-for="a in agents" :key="a.id" :value="a.id">{{ a.name }}</option>
                    </select>
                    <p v-if="form.errors.agent_id" :class="errorClass">{{ form.errors.agent_id }}</p>
                </div>
                <div>
                    <label :class="labelClass">Project (optional)</label>
                    <select v-model="form.project_id" :class="inputClass">
                        <option value="">— No project —</option>
                        <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>
                    <p v-if="form.errors.project_id" :class="errorClass">{{ form.errors.project_id }}</p>
                </div>
            </div>
        </div>

        <!-- ── Lot details ── -->
        <div :class="cardClass">
            <div class="border-b border-gray-200 px-5 py-4 dark:border-zinc-700">
                <h2 class="font-medium text-gray-700 dark:text-gray-300">Lot Details</h2>
                <p class="mt-0.5 text-xs text-gray-400">The lot being reserved. The official lot record is created once the sale is confirmed.</p>
            </div>
            <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label :class="labelClass">Subdivision</label>
                    <input v-model="form.subdivision" type="text" required :class="inputClass" />
                    <p v-if="form.errors.subdivision" :class="errorClass">{{ form.errors.subdivision }}</p>
                </div>
                <div>
                    <label :class="labelClass">Phase (optional)</label>
                    <input v-model="form.phase" type="text" :class="inputClass" />
                    <p v-if="form.errors.phase" :class="errorClass">{{ form.errors.phase }}</p>
                </div>
                <div>
                    <label :class="labelClass">Lot Area, sqm (optional)</label>
                    <input v-model="form.lot_area" type="number" step="0.01" min="0" :class="inputClass" />
                    <p v-if="form.errors.lot_area" :class="errorClass">{{ form.errors.lot_area }}</p>
                </div>
                <div>
                    <label :class="labelClass">Block No. (optional)</label>
                    <input v-model="form.block_number" type="text" :class="inputClass" />
                    <p v-if="form.errors.block_number" :class="errorClass">{{ form.errors.block_number }}</p>
                </div>
                <div>
                    <label :class="labelClass">Lot No.</label>
                    <input v-model="form.lot_number" type="text" required :class="inputClass" />
                    <p v-if="form.errors.lot_number" :class="errorClass">{{ form.errors.lot_number }}</p>
                </div>
            </div>
        </div>

        <!-- ── Reservation details ── -->
        <div :class="cardClass">
            <div class="border-b border-gray-200 px-5 py-4 dark:border-zinc-700">
                <h2 class="font-medium text-gray-700 dark:text-gray-300">Reservation Details</h2>
                <p class="mt-0.5 text-xs text-gray-400">All fields here are optional. Reservation date defaults to today.</p>
            </div>
            <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label :class="labelClass">Reservation Fee (optional)</label>
                    <input v-model="form.reservation_fee" type="number" step="0.01" min="0" :class="inputClass" />
                    <p v-if="form.errors.reservation_fee" :class="errorClass">{{ form.errors.reservation_fee }}</p>
                </div>
                <div>
                    <label :class="labelClass">Reservation Date</label>
                    <input v-model="form.reservation_date" type="date" :class="inputClass" />
                    <p v-if="form.errors.reservation_date" :class="errorClass">{{ form.errors.reservation_date }}</p>
                </div>
                <div>
                    <label :class="labelClass">Expiry Date (optional)</label>
                    <input v-model="form.expiry_date" type="date" :class="inputClass" />
                    <p v-if="form.errors.expiry_date" :class="errorClass">{{ form.errors.expiry_date }}</p>
                </div>
                <div class="sm:col-span-2 lg:col-span-3">
                    <label :class="labelClass">Notes (optional)</label>
                    <input v-model="form.notes" type="text" :class="inputClass" />
                    <p v-if="form.errors.notes" :class="errorClass">{{ form.errors.notes }}</p>
                </div>
            </div>
        </div>

        <!-- ── Actions ── -->
        <div class="flex items-center gap-2">
            <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-amber-700 disabled:opacity-50"
            >
                {{ form.processing ? 'Saving...' : isEdit ? 'Save Changes' : 'Create Reservation' }}
            </button>
            <Link
                :href="cancelHref"
                class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm text-gray-600 shadow-sm hover:bg-gray-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-gray-300 dark:hover:bg-zinc-800"
            >
                Cancel
            </Link>
        </div>
    </form>
</template>
