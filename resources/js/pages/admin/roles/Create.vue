<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ShieldCheck } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard } from '@/routes';
import * as roleRoute from '@/routes/admin/roles';
import type { Permission } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Roles', href: roleRoute.index() },
            { title: 'Create', href: roleRoute.create() },
        ],
    },
});

defineProps<{
    permissions: Record<string, Permission[]>;
}>();
</script>

<template>
    <Head title="New Role" />

    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">New Role</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Create a role and select the permissions it should include</p>
        </div>

        <Form v-bind="roleRoute.store.form()" class="flex flex-col gap-6" v-slot="{ errors, processing }">
            <!-- Role Details -->
            <div class="rounded-xl border p-6">
                <div class="mb-6">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Role Details</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Basic details of the role</p>
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="name">Role Name <span class="text-red-500">*</span></Label>
                        <Input id="name" name="name" placeholder="e.g. Collections Officer" required />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="description">Description <span class="text-gray-400">(optional)</span></Label>
                        <Input id="description" name="description" placeholder="Short description" />
                        <InputError :message="errors.description" />
                    </div>
                </div>
            </div>

            <!-- Permissions -->
            <div class="rounded-xl border p-6">
                <div class="mb-6 flex items-start gap-2">
                    <ShieldCheck class="mt-0.5 h-4 w-4 text-amber-600" />
                    <div>
                        <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Permissions</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Select what this role is allowed to do</p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="(perms, group) in permissions"
                        :key="group"
                        class="rounded-xl border p-4 transition-colors hover:border-amber-300"
                    >
                        <h3 class="mb-3 text-sm font-semibold capitalize text-gray-800 dark:text-gray-100">
                            {{ group }}
                        </h3>
                        <div class="space-y-1">
                            <Label
                                v-for="perm in perms"
                                :key="perm.id"
                                class="flex cursor-pointer items-center gap-2 rounded-md px-2 py-1.5 text-sm font-normal transition-colors hover:bg-amber-50"
                            >
                                <Checkbox
                                    name="permissions[]"
                                    :value="perm.id"
                                    class="data-[state=checked]:border-amber-600 data-[state=checked]:bg-amber-600"
                                />
                                {{ perm.name }}
                            </Label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3">
                <Link :href="roleRoute.index().url">
                    <Button type="button" variant="outline">Cancel</Button>
                </Link>
                <Button type="submit" :disabled="processing" class="bg-amber-600 hover:bg-amber-700">
                    Create Role
                </Button>
            </div>
        </Form>
    </div>
</template>
