<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';   // ← Link added
import * as clientRoute from '@/routes/clients';

// Types
type ClientForm = {
    first_name: string;
    middle_name: string;
    last_name: string;
    email: string;
    phone_number: string;
    address: string;
    valid_id_type: string;
    valid_id_number: string;
};

const props = defineProps<{
    client?: ClientForm & { id: number };
    mode: 'create' | 'edit';
}>();

// Form
const form = useForm<ClientForm>({
    first_name:      props.client?.first_name      ?? '',
    middle_name:     props.client?.middle_name     ?? '',
    last_name:       props.client?.last_name       ?? '',
    email:           props.client?.email           ?? '',
    phone_number:    props.client?.phone_number    ?? '',
    address:         props.client?.address         ?? '',
    valid_id_type:   props.client?.valid_id_type   ?? '',
    valid_id_number: props.client?.valid_id_number ?? '',
});

// Submit handler
const submit = () => {
    if (props.mode === 'create') {
        form.post(clientRoute.store(), {
            preserveScroll: true,
        });
    } else {
        form.put(clientRoute.update({ client: props.client!.id }), {
            preserveScroll: true,
        });
    }
};

// Valid ID options - ideally this should come from the backend or a config file, but hardcoding for simplicity
const validIdOptions = [
    "Driver's License",
    'Passport',
    'SSS ID',
    'GSIS ID',
    'PhilHealth ID',
    'Pag-IBIG ID',
    'Postal ID',
    "Voter's ID",
    'National ID (PhilSys)',
    'PRC ID',
    'TIN ID',
    'Senior Citizen ID',
    'PWD ID',
    'OFW ID',
];
</script>

<template>
    <form @submit.prevent="submit" class="space-y-8">

        <!-- Section: Personal Information -->
        <div class="rounded-xl border border-gray-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-zinc-700">
                <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Personal Information</h2>
                <p class="mt-0.5 text-xs text-gray-400">Basic details of the client</p>
            </div>
            <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-3">

                <!-- First Name -->
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">
                        First Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.first_name"
                        type="text"
                        placeholder="Juan"
                        autocomplete="given-name"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder:text-zinc-500"
                        :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-400': form.errors.first_name }"
                    />
                    <p v-if="form.errors.first_name" class="mt-1 text-xs text-red-500">
                        {{ form.errors.first_name }}
                    </p>
                </div>

                <!-- Middle Name -->
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">
                        Middle Name <span class="text-gray-400">(optional)</span>
                    </label>
                    <input
                        v-model="form.middle_name"
                        type="text"
                        placeholder="Santos"
                        autocomplete="additional-name"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder:text-zinc-500"
                    />
                </div>

                <!-- Last Name -->
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">
                        Last Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.last_name"
                        type="text"
                        placeholder="Dela Cruz"
                        autocomplete="family-name"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder:text-zinc-500"
                        :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-400': form.errors.last_name }"
                    />
                    <p v-if="form.errors.last_name" class="mt-1 text-xs text-red-500">
                        {{ form.errors.last_name }}
                    </p>
                </div>

            </div>
        </div>

        <!-- Section: Contact Information -->
        <div class="rounded-xl border border-gray-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-zinc-700">
                <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Contact Information</h2>
                <p class="mt-0.5 text-xs text-gray-400">How to reach the client</p>
            </div>
            <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">

                <!-- Email -->
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">
                        Email Address <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.email"
                        type="email"
                        placeholder="juan@example.com"
                        autocomplete="email"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder:text-zinc-500"
                        :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-400': form.errors.email }"
                    />
                    <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">
                        {{ form.errors.email }}
                    </p>
                </div>

                <!-- Phone -->
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">
                        Phone Number <span class="text-gray-400">(optional)</span>
                    </label>
                    <input
                        v-model="form.phone_number"
                        type="tel"
                        placeholder="09171234567"
                        autocomplete="tel"
                        maxlength="20"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder:text-zinc-500"
                        :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-400': form.errors.phone_number }"
                    />
                    <p v-if="form.errors.phone_number" class="mt-1 text-xs text-red-500">
                        {{ form.errors.phone_number }}
                    </p>
                </div>

                <!-- Address -->
                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">
                        Address <span class="text-gray-400">(optional)</span>
                    </label>
                    <textarea
                        v-model="form.address"
                        rows="2"
                        placeholder="123 Mabini St., Brgy. San Antonio, Quezon City"
                        autocomplete="street-address"
                        maxlength="255"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder:text-zinc-500"
                        :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-400': form.errors.address }"
                    />
                    <p v-if="form.errors.address" class="mt-1 text-xs text-red-500">
                        {{ form.errors.address }}
                    </p>
                </div>

            </div>
        </div>

        <!-- Section: ID Verification -->
        <div class="rounded-xl border border-gray-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-zinc-700">
                <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300">ID Verification</h2>
                <p class="mt-0.5 text-xs text-gray-400">Government-issued ID for KYC compliance</p>
            </div>
            <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">

                <!-- ID Type -->
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">
                        ID Type <span class="text-gray-400">(optional)</span>
                    </label>
                    <select
                        v-model="form.valid_id_type"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                    >
                        <option value="">Select ID type</option>
                        <option v-for="id in validIdOptions" :key="id" :value="id">
                            {{ id }}
                        </option>
                    </select>
                    <p v-if="form.errors.valid_id_type" class="mt-1 text-xs text-red-500">
                        {{ form.errors.valid_id_type }}
                    </p>
                </div>

                <!-- ID Number -->
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">
                        ID Number <span class="text-gray-400">(optional)</span>
                    </label>
                    <input
                        v-model="form.valid_id_number"
                        type="text"
                        placeholder="e.g. N01-23-456789"
                        maxlength="100"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder:text-zinc-500"
                        :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-400': form.errors.valid_id_number }"
                    />
                    <p v-if="form.errors.valid_id_number" class="mt-1 text-xs text-red-500">
                        {{ form.errors.valid_id_number }}
                    </p>
                </div>

            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end gap-3">
            <Link
                :href="clientRoute.index()"
                class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-600 shadow-sm transition hover:bg-gray-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-gray-300 dark:hover:bg-zinc-800"
            >
                Cancel
            </Link>
            <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-5 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
            >
                <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                </svg>
                {{ form.processing ? 'Saving...' : mode === 'create' ? 'Create Client' : 'Save Changes' }}
            </button>
        </div>

    </form>
</template>
