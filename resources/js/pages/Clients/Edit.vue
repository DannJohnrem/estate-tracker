<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ClientForm from '@/components/Clients/ClientForm.vue';
import { dashboard } from '@/routes';
import * as clientRoute from '@/routes/clients';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Clients',   href: clientRoute.index() },
            { title: 'Edit',      href: '#' },
        ],
    },
});

const props = defineProps<{
    client: {
        id: number;
        first_name: string;
        middle_name: string;
        last_name: string;
        email: string;
        phone_number: string;
        address: string;
        valid_id_type: string;
        valid_id_number: string;
    };
}>();
</script>

<template>
    <Head title="Edit Client" />
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Edit Client</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Update details for {{ props.client.first_name }} {{ props.client.last_name }}
            </p>
        </div>
        <ClientForm mode="edit" :client="props.client" />
    </div>
</template>

<style scoped>

</style>
