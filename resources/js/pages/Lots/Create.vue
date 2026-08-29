<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import * as lotRoute from '@/routes/lots';
import LotForm from '@/components/Lots/LotForm.vue';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Lots',      href: lotRoute.index() },
            { title: 'Add Lot',   href: '#' },
        ],
    },
});

type ClientOption = { id: number; name: string };

const props = defineProps<{
    clients: ClientOption[];
    selected_client_id: number | null;
}>();
</script>

<template>
    <Head title="Add Lot" />
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Add Lot</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Register a new lot record</p>
        </div>
        <LotForm
            mode="create"
            :clients="clients"
            :selected-client-id="selected_client_id"
        />
    </div>
</template>
