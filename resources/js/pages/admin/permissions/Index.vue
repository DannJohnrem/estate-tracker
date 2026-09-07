<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Badge } from '@/components/ui/badge';
import type { Permission } from '@/types';

defineOptions({ layout: AppLayout });

defineProps<{
    permissions: Record<string, Permission[]>;
}>();
</script>

<template>

    <Head title="Permissions" />

    <div class="space-y-6 p-6">
        <div>
            <h1 class="text-2xl font-semibold">Permissions</h1>
            <p class="text-sm text-muted-foreground">
                A list of all available permissions, grouped by module. These are
                system-defined — assign them to Roles as needed.
            </p>
        </div>

        <div v-for="(perms, group) in permissions" :key="group" class="rounded-md border p-4">
            <h3 class="mb-3 text-sm font-semibold capitalize">{{ group }}</h3>
            <div class="flex flex-wrap gap-2">
                <Badge v-for="perm in perms" :key="perm.id" variant="outline">
                    {{ perm.name }}
                </Badge>
            </div>
        </div>
    </div>
</template>
