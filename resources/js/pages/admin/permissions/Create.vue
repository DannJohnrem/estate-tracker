<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PermissionForm from '@/components/Admin/Permission/PermissionForm.vue';
import { dashboard } from '@/routes';
import * as permissionRoute from '@/routes/admin/permissions';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Permissions', href: permissionRoute.index() },
            { title: 'Create' },
        ],
    },
});

defineProps<{
    groups: string[];
    actions: string[];
}>();
</script>

<template>
    <Head title="New Permission" />
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">New Permission</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Define a new permission and assign it to a module</p>
        </div>
        <PermissionForm mode="create" :groups="groups" :actions="actions" />
    </div>
</template>
