<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

// import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';

import {
    index as usersIndex,
    updateStatus,
    updateRoles,
} from '@/routes/admin/users';

import type { Role, User } from '@/types';

defineOptions({ layout: AppLayout });

const props = defineProps<{
    users: User[];
    roles: Role[];
    filters: {
        status: string | null;
        search?: string;
    };
}>();

// ---------------------------------------------------------
// Search
// ---------------------------------------------------------

const search = ref(props.filters.search ?? '');

let searchTimeout: ReturnType<typeof setTimeout>;

watch(search, () => {
    clearTimeout(searchTimeout);

    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 400);
});

// ---------------------------------------------------------
// Status Filter
// ---------------------------------------------------------

const statusTabs = [
    { label: 'Lahat', value: null },
    { label: 'Pending', value: 'pending' },
    { label: 'Approved', value: 'approved' },
    { label: 'Rejected', value: 'rejected' },
];

const applyFilters = () => {
    router.get(
        usersIndex().url,
        {
            search: search.value || undefined,
            status: props.filters.status || undefined,
        },
        {
            preserveState: true,
            replace: true,
            preserveScroll: true,
        },
    );
};

const filterBy = (status: string | null) => {
    router.get(
        usersIndex().url,
        {
            search: search.value || undefined,
            status: status || undefined,
        },
        {
            preserveState: true,
            replace: true,
            preserveScroll: true,
        },
    );
};

const resetFilters = () => {
    search.value = '';

    router.get(
        usersIndex().url,
        {},
        {
            preserveState: true,
            replace: true,
            preserveScroll: true,
        },
    );
};

const hasActiveFilters = () => {
    return search.value || props.filters.status;
};

// ---------------------------------------------------------
// Status
// ---------------------------------------------------------

const statusConfig: Record<
    string,
    {
        label: string;
        classes: string;
        dot: string;
    }
> = {
    pending: {
        label: 'Pending',
        dot: 'bg-yellow-500',
        classes:
            'bg-yellow-50 text-yellow-700 ring-1 ring-yellow-200 dark:bg-yellow-900/30 dark:text-yellow-400 dark:ring-yellow-800',
    },

    approved: {
        label: 'Approved',
        dot: 'bg-emerald-500',
        classes:
            'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:ring-emerald-800',
    },

    rejected: {
        label: 'Rejected',
        dot: 'bg-red-500',
        classes:
            'bg-red-50 text-red-700 ring-1 ring-red-200 dark:bg-red-900/30 dark:text-red-400 dark:ring-red-800',
    },
};

const getStatus = (status: string) => {
    return (
        statusConfig[status] ?? {
            label: status,
            dot: 'bg-gray-400',
            classes:
                'bg-gray-100 text-gray-500 ring-1 ring-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700',
        }
    );
};

// ---------------------------------------------------------
// Approve / Reject
// ---------------------------------------------------------

const approve = (user: User) => {
    router.patch(
        updateStatus({ user: user.id }).url,
        {
            status: 'approved',
        },
        {
            preserveScroll: true,
        },
    );
};

const reject = (user: User) => {
    router.patch(
        updateStatus({ user: user.id }).url,
        {
            status: 'rejected',
        },
        {
            preserveScroll: true,
        },
    );
};

// ---------------------------------------------------------
// Role Assignment Dialog
// ---------------------------------------------------------

const roleDialogOpen = ref(false);
const activeUser = ref<User | null>(null);
const selectedRoleIds = ref<string[]>([]);

const openRoleDialog = (user: User) => {
    activeUser.value = user;

    selectedRoleIds.value = (user.roles ?? []).map(
        (role) => role.id,
    );

    roleDialogOpen.value = true;
};

const toggleRole = (roleId: string) => {
    selectedRoleIds.value = selectedRoleIds.value.includes(roleId)
        ? selectedRoleIds.value.filter((id) => id !== roleId)
        : [...selectedRoleIds.value, roleId];
};

const saveRoles = () => {
    if (!activeUser.value) {
        return;
    }

    router.patch(
        updateRoles({
            user: activeUser.value.id,
        }).url,
        {
            roles: selectedRoleIds.value,
        },
        {
            preserveScroll: true,

            onSuccess: () => {
                roleDialogOpen.value = false;
            },
        },
    );
};
</script>

<template>

    <Head title="User Accounts" />

    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">

        <!-- Page Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">
                    User Accounts
                </h1>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Manage user accounts, approvals, and roles.
                </p>
            </div>
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap items-center gap-3">

            <!-- Search -->
            <div class="relative">
                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" />
                </svg>

                <input v-model="search" type="text" placeholder="Search name or email..."
                    class="w-72 rounded-lg border border-gray-200 bg-white py-2 pl-9 pr-3 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white dark:placeholder:text-gray-500" />
            </div>

            <!-- Status Buttons -->
            <div class="flex flex-wrap gap-2">
                <Button v-for="tab in statusTabs" :key="tab.label" size="sm" :variant="props.filters.status === tab.value
                    ? 'default'
                    : 'outline'
                    " @click="filterBy(tab.value)">
                    {{ tab.label }}
                </Button>
            </div>

            <!-- Clear -->
            <button v-if="hasActiveFilters()" @click="resetFilters"
                class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-500 shadow-sm transition hover:bg-gray-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-gray-400 dark:hover:bg-zinc-800">
                Clear filters
            </button>
        </div>

        <!-- Table -->
        <div
            class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <Table>

                <!-- Header -->
                <TableHeader>
                    <TableRow class="border-b border-gray-100 bg-gray-50 dark:border-zinc-700 dark:bg-zinc-800/60">
                        <TableHead
                            class="px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Name
                        </TableHead>

                        <TableHead
                            class="px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Email
                        </TableHead>

                        <TableHead
                            class="px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Status
                        </TableHead>

                        <TableHead
                            class="px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Roles
                        </TableHead>

                        <TableHead
                            class="px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Source
                        </TableHead>

                        <TableHead
                            class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Actions
                        </TableHead>
                    </TableRow>
                </TableHeader>

                <!-- Body -->
                <TableBody>

                    <!-- Empty -->
                    <TableRow v-if="props.users.length === 0">
                        <TableCell colspan="6" class="h-32 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>

                                <p class="text-sm font-medium text-gray-400 dark:text-gray-500">
                                    No users found
                                </p>

                                <p class="text-xs text-gray-300 dark:text-gray-600">
                                    Try adjusting your search or filter
                                </p>
                            </div>
                        </TableCell>
                    </TableRow>

                    <!-- Rows -->
                    <TableRow v-for="user in props.users" :key="user.id"
                        class="group transition-colors hover:bg-amber-50/40 dark:hover:bg-amber-900/10">

                        <!-- Name -->
                        <TableCell class="px-5 py-4 font-medium text-gray-900 dark:text-white">
                            {{ user.name }}
                        </TableCell>

                        <!-- Email -->
                        <TableCell class="px-5 py-4 text-gray-600 dark:text-gray-300">
                            {{ user.email }}
                        </TableCell>

                        <!-- Status -->
                        <TableCell class="px-5 py-4">
                            <span :class="getStatus(user.status).classes"
                                class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium">
                                <span :class="getStatus(user.status).dot" class="h-1.5 w-1.5 rounded-full" />

                                {{ getStatus(user.status).label }}
                            </span>
                        </TableCell>

                        <!-- Roles -->
                        <TableCell class="px-5 py-4">
                            <div class="flex flex-wrap gap-1.5">
                                <span v-for="role in user.roles" :key="role.id"
                                    class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-2 py-0.5 text-xs font-medium text-gray-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-gray-300">
                                    {{ role.name }}
                                </span>

                                <span v-if="!user.roles?.length" class="text-xs text-gray-400 dark:text-gray-500">
                                    None yet
                                </span>
                            </div>
                        </TableCell>

                        <!-- Source -->
                        <TableCell class="px-5 py-4">
                            <span :class="user.google_id
                                ? 'bg-blue-50 text-blue-700 ring-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:ring-blue-800'
                                : 'bg-gray-100 text-gray-600 ring-gray-200 dark:bg-zinc-800 dark:text-gray-400 dark:ring-zinc-700'
                                " class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1">
                                {{ user.google_id ? 'Google' : 'Email' }}
                            </span>
                        </TableCell>

                        <!-- Actions -->
                        <TableCell class="px-5 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <!-- Approve -->
                                <Button v-if="user.status !== 'approved'" size="sm" variant="default"
                                    @click="approve(user)">
                                    Approve
                                </Button>

                                <!-- Reject -->
                                <Button v-if="user.status !== 'rejected'" size="sm" variant="destructive"
                                    @click="reject(user)">
                                    Reject
                                </Button>

                                <!-- Roles -->
                                <Button size="sm" variant="outline" @click="openRoleDialog(user)">
                                    Roles
                                </Button>
                            </div>
                        </TableCell>

                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <!-- Role Dialog -->
        <Dialog v-model:open="roleDialogOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        Assign roles to {{ activeUser?.name }}
                    </DialogTitle>
                </DialogHeader>

                <div class="space-y-3 py-2">
                    <Label v-for="role in props.roles" :key="role.id" class="flex items-center gap-3">
                        <Checkbox :model-value="selectedRoleIds.includes(role.id)
                            " @update:model-value="
                                () => toggleRole(role.id)
                            " />

                        <span>{{ role.name }}</span>
                    </Label>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="roleDialogOpen = false">
                        Cancel
                    </Button>

                    <Button @click="saveRoles">
                        Save
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
