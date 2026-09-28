<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ReservationForm from '@/components/reservations/ReservationForm.vue';
import { lotLabel } from '@/lib/reservations';

type Option = { id: string; name: string };

const props = defineProps<{
    reservation: {
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
    clients: Option[];
    agents: Option[];
    projects: Option[];
    breadcrumbs: { title: string; href: string }[];
}>();
</script>

<template>
    <Head :title="`Edit ${lotLabel(reservation)}`" />

    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Edit Reservation</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ lotLabel(reservation) }} · {{ reservation.subdivision }}</p>
        </div>

        <ReservationForm
            :reservation="reservation"
            :clients="clients"
            :agents="agents"
            :projects="projects"
        />
    </div>
</template>
