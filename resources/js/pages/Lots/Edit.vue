<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import * as lotRoute from '@/routes/lots';
import LotForm from '@/components/Lots/LotForm.vue';

type ClientOption = { id: number; name: string };

type Lot = {
    id: number;
    client_id: number;
    lot_number: string;
    block_number: string;
    subdivision: string;
    phase: string;
    lot_area: string;
    total_contract_price: string;
    down_payment: string;
    monthly_amortization: string;
    term_months: string;
    months_paid: string;
    start_date: string;
    next_due_date: string;
    status: string;
};

const props = defineProps<{
    lot: Lot;
    clients: ClientOption[];
    breadcrumbs: { title: string; href: string }[];
}>();
</script>

<template>
    <Head title="Edit Lot" />
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Edit Lot</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Block {{ lot.block_number }} · Lot {{ lot.lot_number }} — {{ lot.subdivision }}
            </p>
        </div>
        <LotForm
            mode="edit"
            :lot="lot"
            :clients="clients"
        />
    </div>
</template>
