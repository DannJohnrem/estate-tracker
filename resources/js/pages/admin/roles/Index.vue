<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

import { Button } from '@/components/ui/button';

import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

import { create, edit } from '@/routes/admin/roles';

import type { Role } from '@/types';

defineOptions({ layout: AppLayout });

const props = defineProps<{
    roles: (Role & {
        users_count: number;
    })[];
}>();
</script>

<template>

    <Head title="Roles" />

    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">

        <!-- Page Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">
                    Roles
                </h1>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Manage roles and the permissions assigned to each one.
                </p>
            </div>

            <!-- Add Role -->
            <Button as-child>
                <Link :href="create().url">
                    + New Role
                </Link>
            </Button>
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
                            Description
                        </TableHead>

                        <TableHead
                            class="px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Permissions
                        </TableHead>

                        <TableHead
                            class="px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Users
                        </TableHead>

                        <TableHead
                            class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Actions
                        </TableHead>
                    </TableRow>
                </TableHeader>

                <!-- Body -->
                <TableBody>

                    <!-- Empty State -->
                    <TableRow v-if="props.roles.length === 0">
                        <TableCell colspan="5" class="h-32 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622C17.176 19.29 21 14.591 21 9c0-1.042-.133-2.052-.382-3.016z" />
                                </svg>

                                <p class="text-sm font-medium text-gray-400 dark:text-gray-500">
                                    No roles found
                                </p>

                                <p class="text-xs text-gray-300 dark:text-gray-600">
                                    Create a role to get started.
                                </p>
                            </div>
                        </TableCell>
                    </TableRow>

                    <!-- Rows -->
                    <TableRow v-for="role in props.roles" :key="role.id"
                        class="group transition-colors hover:bg-amber-50/40 dark:hover:bg-amber-900/10">

                        <!-- Name -->
                        <TableCell class="px-5 py-4 font-medium text-gray-900 dark:text-white">
                            {{ role.name }}
                        </TableCell>

                        <!-- Description -->
                        <TableCell class="max-w-[300px] truncate px-5 py-4 text-sm text-gray-600 dark:text-gray-300"
                            :title="role.description || undefined">
                            {{ role.description || '—' }}
                        </TableCell>

                        <!-- Permissions -->
                        <TableCell class="px-5 py-4">
                            <span
                                class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-medium text-gray-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-gray-300">
                                {{ role.permissions?.length ?? 0 }}
                                permissions
                            </span>
                        </TableCell>

                        <!-- Users -->
                        <TableCell class="px-5 py-4">
                            <span
                                class="inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-amber-100 px-1.5 text-xs font-semibold text-amber-700 dark:bg-amber-900/40 dark:text-amber-400">
                                {{ role.users_count }}
                            </span>
                        </TableCell>

                        <!-- Actions -->
                        <TableCell class="px-5 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <Button size="sm" variant="outline" as-child>
                                    <Link :href="edit({
                                        role: role.id,
                                    }).url
                                        ">
                                        Edit
                                    </Link>
                                </Button>
                            </div>
                        </TableCell>

                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </div>
</template>
