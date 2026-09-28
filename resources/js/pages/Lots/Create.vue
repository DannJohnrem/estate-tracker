<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import * as lotRoute from '@/routes/lots';
import LotForm from '@/components/lots/LotForm.vue';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Lots',      href: lotRoute.index() },
            { title: 'Add Lot',   href: '#' },
        ],
    },
});

type ClientOption = { id: string; name: string };
type AgentOption = { id: string; name: string };
type ProjectOption = { id: string; name: string };

type ReservationPrefill = {
    id: string;
    client_id: string;
    agent_id: string | null;
    project_id: string | null;
    lot_number: string;
    block_number: string | null;
    subdivision: string;
    phase: string | null;
    lot_area: number | null;
};

defineProps<{
    clients: ClientOption[];
    agents: AgentOption[];
    projects: ProjectOption[];
    selected_client_id: string | null;
    reservation?: ReservationPrefill | null;
}>();
</script>

<template>
    <Head title="Add Lot" />
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Add Lot</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Register a new lot record</p>
        </div>

        <!-- Shown only when coming from a reservation's "Confirm & Create Lot" button -->
        <div
            v-if="reservation"
            class="rounded-xl border border-emerald-200 bg-emerald-50/40 p-4 text-sm text-gray-700 dark:border-emerald-800 dark:bg-emerald-900/10 dark:text-gray-300"
        >
            Creating a lot from a reservation. Once saved, the reservation will be marked
            <span class="font-medium">Confirmed</span>.
            <p v-if="!reservation.project_id" class="mt-1 text-amber-700 dark:text-amber-400">
                The reservation has no project. Please select one below.
            </p>
        </div>

        <LotForm
            mode="create"
            :clients="clients"
            :agents="agents"
            :projects="projects"
            :selected-client-id="selected_client_id"
            :reservation="reservation"
        />
    </div>
</template>
