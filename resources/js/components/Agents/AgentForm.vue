<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import * as agentRoute from '@/routes/agents';

type Agent = {
    id: string;
    first_name: string;
    middle_name: string | null;
    last_name: string;
    email: string;
    phone_number: string | null;
    license_number: string | null;
    commission_rate: number;
    status: 'active' | 'inactive';
};

const props = defineProps<{
    mode: 'create' | 'edit';
    agent?: Agent;
}>();

const form = useForm({
    first_name: props.agent?.first_name ?? '',
    middle_name: props.agent?.middle_name ?? '',
    last_name: props.agent?.last_name ?? '',
    email: props.agent?.email ?? '',
    phone_number: props.agent?.phone_number ?? '',
    license_number: props.agent?.license_number ?? '',
    commission_rate: props.agent?.commission_rate ?? 0,
    status: props.agent?.status ?? 'active',
});

const submit = () => {
    if (props.mode === 'create') {
        form.post(agentRoute.store().url);
    } else {
        form.put(agentRoute.update({ agent: props.agent!.id }).url);
    }
};
</script>

<template>
    <form @submit.prevent="submit" class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">First Name</label>
            <input v-model="form.first_name" type="text" required
                class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white" />
            <p v-if="form.errors.first_name" class="mt-1 text-xs text-red-500">{{ form.errors.first_name }}</p>
        </div>

        <div>
            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Middle Name</label>
            <input v-model="form.middle_name" type="text"
                class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white" />
        </div>

        <div>
            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Last Name</label>
            <input v-model="form.last_name" type="text" required
                class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white" />
            <p v-if="form.errors.last_name" class="mt-1 text-xs text-red-500">{{ form.errors.last_name }}</p>
        </div>

        <div>
            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Email</label>
            <input v-model="form.email" type="email" required
                class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white" />
            <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
        </div>

        <div>
            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Phone Number</label>
            <input v-model="form.phone_number" type="text"
                class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white" />
        </div>

        <div>
            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">License Number</label>
            <input v-model="form.license_number" type="text"
                class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white" />
        </div>

        <div>
            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Commission Rate (%)</label>
            <input v-model="form.commission_rate" type="number" step="0.01" min="0" max="100" required
                class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white" />
            <p v-if="form.errors.commission_rate" class="mt-1 text-xs text-red-500">{{ form.errors.commission_rate }}</p>
        </div>

        <div>
            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Status</label>
            <select v-model="form.status"
                class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>

        <div class="col-span-full flex items-center gap-2">
            <button type="submit" :disabled="form.processing"
                class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-amber-700 disabled:opacity-50">
                {{ form.processing ? 'Saving...' : mode === 'create' ? 'Add Agent' : 'Save Changes' }}
            </button>
        </div>
    </form>
</template>
