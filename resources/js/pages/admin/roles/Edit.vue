<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { update } from '@/routes/admin/roles';
import type { Permission, Role } from '@/types';

defineOptions({ layout: AppLayout });

const props = defineProps<{
    role: Role;
    permissions: Record<string, Permission[]>;
}>();

const assignedIds = (props.role.permissions ?? []).map((p) => p.id);
</script>

<template>

    <Head :title="`Edit ${role.name}`" />

    <div class="mx-auto max-w-2xl space-y-6 p-6">
        <div>
            <h1 class="text-2xl font-semibold">Edit Role</h1>
        </div>

        <Form v-bind="update.form({ role: role.id })" method="put" class="space-y-6" v-slot="{ errors, processing }">
            <div class="grid gap-2">
                <Label for="name">Pangalan ng Role</Label>
                <Input id="name" name="name" :default-value="role.name" required />
                <InputError :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="description">Deskripsyon (optional)</Label>
                <Input id="description" name="description" :default-value="role.description ?? ''" />
                <InputError :message="errors.description" />
            </div>

            <div class="space-y-4">
                <Label>Permissions</Label>
                <div v-for="(perms, group) in permissions" :key="group" class="rounded-md border p-4">
                    <h3 class="mb-2 text-sm font-semibold capitalize">{{ group }}</h3>
                    <div class="grid grid-cols-2 gap-2">
                        <Label v-for="perm in perms" :key="perm.id" class="flex items-center gap-2 text-sm font-normal">
                            <Checkbox name="permissions[]" :value="perm.id"
                                :default-checked="assignedIds.includes(perm.id)" />
                            {{ perm.name }}
                        </Label>
                    </div>
                </div>
            </div>

            <Button type="submit" :disabled="processing">Save Changes</Button>
        </Form>
    </div>
</template>
