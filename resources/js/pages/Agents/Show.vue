<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import * as agentRoute from '@/routes/agents';
import * as lotRoute from '@/routes/lots';

type Breadcrumb = { title: string; href: string };

type Lot = {
    id: string;
    lot_number: string;
    block_number: string | null;
    subdivision: string;
    status: 'active' | 'delinquent' | 'fully_paid' | 'cancelled';
};

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
    lots: Lot[];
    created_at: string;
};

const props = defineProps<{
    agent: Agent;
    breadcrumbs: Breadcrumb[];
}>();

const fullName = [props.agent.first_name, props.agent.middle_name, props.agent.last_name]
    .filter(Boolean).join(' ');

const LOT_STATUS: Record<string, { label: string; classes: string; dot: string }> = {
    active: { label: 'Active', classes: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:ring-emerald-800', dot: 'bg-emerald-500' },
    delinquent: { label: 'Delinquent', classes: 'bg-red-50 text-red-700 ring-1 ring-red-200 dark:bg-red-900/30 dark:text-red-400 dark:ring-red-800', dot: 'bg-red-500' },
    fully_paid: { label: 'Fully Paid', classes: 'bg-blue-50 text-blue-700 ring-1 ring-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:ring-blue-800', dot: 'bg-blue-500' },
    cancelled: { label: 'Cancelled', classes: 'bg-gray-100 text-gray-500 ring-1 ring-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700', dot: 'bg-gray-400' },
};

const getLotStatus = (s: string) => LOT_STATUS[s] ?? { label: s, classes: 'bg-gray-100 text-gray-500', dot: 'bg-gray-400' };

const deleteAgent = () => {
    if (!confirm(`Remove "${fullName}" from the system? This cannot be undone.`)) return;
    router.delete(agentRoute.destroy({ agent: props.agent.id }).url);
};
</script>

<template>
    <Head :title="fullName" />

    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">{{ fullName }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ agent.commission_rate }}% commission · {{ agent.status === 'active' ? 'Active' : 'Inactive' }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <Link :href="agentRoute.edit({ agent: agent.id }).url"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-600 shadow-sm transition hover:bg-gray-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-gray-300 dark:hover:bg-zinc-800">
                    Edit
                </Link>
                <button @click="deleteAgent"
                    class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 shadow-sm transition hover:bg-red-50 dark:border-red-800 dark:bg-zinc-900 dark:text-red-400 dark:hover:bg-red-900/20">
                    Delete
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <div class="rounded-xl border border-gray-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-zinc-700">
                    <h2 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Contact Information</h2>
                </div>
                <div class="space-y-4 p-5">
                    <div>
                        <p class="text-xs text-gray-400">Email</p>
                        <p class="mt-0.5 text-sm text-gray-700 dark:text-gray-300">{{ agent.email }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Phone</p>
                        <p class="mt-0.5 text-sm text-gray-700 dark:text-gray-300">{{ agent.phone_number ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">License Number</p>
                        <p class="mt-0.5 text-sm text-gray-700 dark:text-gray-300">{{ agent.license_number ?? '—' }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-zinc-700">
                    <h2 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Lots Handled</h2>
                </div>
                <div class="space-y-4 p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-xs text-gray-400">Total Lots</p>
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-amber-100 text-xs font-semibold text-amber-700 dark:bg-amber-900/40 dark:text-amber-400">
                            {{ agent.lots.length }}
                        </span>
                    </div>
                    <div v-for="lot in agent.lots" :key="lot.id" class="flex items-center justify-between">
                        <Link :href="lotRoute.show({ lot: lot.id }).url" class="text-xs text-gray-600 hover:text-amber-700 dark:text-gray-400">
                            Blk {{ lot.block_number }} Lot {{ lot.lot_number }}
                        </Link>
                        <span :class="getLotStatus(lot.status).classes" class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium">
                            <span :class="getLotStatus(lot.status).dot" class="h-1.5 w-1.5 rounded-full"></span>
                            {{ getLotStatus(lot.status).label }}
                        </span>
                    </div>
                    <div v-if="agent.lots.length === 0" class="text-xs text-gray-400">No lots assigned yet.</div>
                </div>
            </div>
        </div>
    </div>
</template>
