<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ReservationForm from '@/components/reservations/ReservationForm.vue';

type Option = { id: string; name: string };

defineProps<{
    clients: Option[];
    agents: Option[];
    projects: Option[];
    selected_client_id: string | null;
    breadcrumbs: { title: string; href: string }[];
}>();
</script>

<template>
    <Head title="New Reservation" />

    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">New Reservation</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Hold a lot for a client before the contract is signed.</p>
        </div>

        <ReservationForm
            :clients="clients"
            :agents="agents"
            :projects="projects"
            :selected-client-id="selected_client_id"
        />
    </div>
</template>
