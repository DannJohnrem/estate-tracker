<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AgentForm from '@/components/Agents/AgentForm.vue';
import { dashboard } from '@/routes';
import * as agentRoute from '@/routes/agents';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Agents', href: agentRoute.index() },
            { title: 'Create', href: agentRoute.create() },
        ],
    },
});
</script>

<template>
    <Head title="Add Agent" />
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Add Agent</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Register a new agent record</p>
        </div>
        <AgentForm mode="create" />
    </div>
</template>
