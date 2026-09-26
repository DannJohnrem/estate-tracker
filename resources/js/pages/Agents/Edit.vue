<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AgentForm from '@/components/Agents/AgentForm.vue';
import { dashboard } from '@/routes';
import * as agentRoute from '@/routes/agents';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Agents',    href: agentRoute.index() },
            { title: 'Edit',      href: '#' },
        ],
    },
});

const props = defineProps<{
    agent: {
        id: number;
        first_name: string;
        middle_name: string | null;
        last_name: string;
        email: string;
        phone_number: string | null;
        license_number: string | null;
        commission_rate: number;
        status: 'active' | 'inactive';
    };
}>();
</script>

<template>
    <Head title="Edit Agent" />
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Edit Agent</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Update details for {{ props.agent.first_name }} {{ props.agent.last_name }}
            </p>
        </div>
        <AgentForm mode="edit" :agent="props.agent" />
    </div>
</template>
