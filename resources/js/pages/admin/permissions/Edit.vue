<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ShieldCheck } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
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

        <Form
            v-bind="permissionRoute.update.form({ permission: permission.id })"
            method="put"
            class="mx-auto flex w-full max-w-3xl flex-col gap-6"
            v-slot="{ errors, processing }"
        >
            <div class="rounded-xl border p-6">
                <div class="mb-6 flex items-start gap-2">
                    <ShieldCheck class="mt-0.5 h-4 w-4 text-amber-600" />
                    <div>
                        <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Permission Details</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Basic details of the permission</p>
                    </div>
                </div>

                <div class="grid gap-6">
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="group">Module / Group <span class="text-red-500">*</span></Label>
                            <Input id="group" name="group" list="group-suggestions" :default-value="permission.group" required />
                            <datalist id="group-suggestions">
                                <option v-for="g in groups" :key="g" :value="g" />
                            </datalist>
                            <InputError :message="errors.group" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="name">Permission Name <span class="text-red-500">*</span></Label>
                            <Input id="name" name="name" :default-value="permission.name" required />
                            <InputError :message="errors.name" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="description">Description <span class="text-gray-400">(optional)</span></Label>
                        <Textarea id="description" name="description" :default-value="permission.description ?? ''" rows="3" />
                        <InputError :message="errors.description" />
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <Link :href="permissionRoute.index().url">
                    <Button type="button" variant="outline">Cancel</Button>
                </Link>
                <Button type="submit" :disabled="processing" class="bg-amber-600 hover:bg-amber-700">
                    Save Changes
                </Button>
            </div>
        </Form>
    </div>
</template>
