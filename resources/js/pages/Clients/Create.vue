<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ClientForm from '@/components/Clients/ClientForm.vue';
import { dashboard } from '@/routes';
import * as clientRoute from '@/routes/clients';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Clients', href: clientRoute.index() },
            { title: 'Create', href: clientRoute.create()},
        ],
    },
});
</script>

<template>
    <Head title="Create Client" />
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Add Client</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Register a new client record</p>
        </div>
        <ClientForm mode="create" />
    </div>
</template>

<style scoped>

</style>
