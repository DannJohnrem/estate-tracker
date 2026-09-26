<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PermissionForm from '@/components/Admin/Permission/PermissionForm.vue';
import { dashboard } from '@/routes';
import * as permissionRoute from '@/routes/admin/permissions';
import type { Permission } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Permissions', href: permissionRoute.index() },
            { title: 'Edit' },
        ],
    },
});

const props = defineProps<{
    permission: Permission;
    groups: string[];
    actions: string[];
}>();
</script>

<template>
    <Head :title="`Edit ${permission.name}`" />
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Edit Permission</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Update <span class="font-medium text-gray-700 dark:text-gray-200">{{ permission.name }}</span>'s details
            </p>
        </div>
        <PermissionForm
            mode="edit"
            :permission="{ id: permission.id, group: permission.group, slug: permission.slug, description: permission.description }"
            :groups="groups"
            :actions="actions"
        />
    </div>
</template>
