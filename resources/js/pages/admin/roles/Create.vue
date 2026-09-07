<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { store } from '@/routes/admin/roles';
import type { Permission } from '@/types';

defineOptions({ layout: AppLayout });

defineProps<{
    permissions: Record<string, Permission[]>;
}>();
</script>

<template>

    <Head title="Bagong Role" />

    <div class="mx-auto max-w-2xl space-y-6 p-6">
        <div>
            <h1 class="text-2xl font-semibold">Bagong Role</h1>
            <p class="text-sm text-muted-foreground">
                Create a role and select the permissions it should include.
            </p>
        </div>

        <Form v-bind="store.form()" class="space-y-6" v-slot="{ errors, processing }">
            <div class="grid gap-2">
                <Label for="name">Pangalan ng Role</Label>
                <Input id="name" name="name" placeholder="hal. Collections Officer" required />
                <InputError :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="description">Deskripsyon (optional)</Label>
                <Input id="description" name="description" placeholder="Maikling paglalarawan" />
                <InputError :message="errors.description" />
            </div>

            <div class="space-y-4">
                <Label>Permissions</Label>
                <div v-for="(perms, group) in permissions" :key="group" class="rounded-md border p-4">
                    <h3 class="mb-2 text-sm font-semibold capitalize">{{ group }}</h3>
                    <div class="grid grid-cols-2 gap-2">
                        <Label v-for="perm in perms" :key="perm.id" class="flex items-center gap-2 text-sm font-normal">
                            <Checkbox name="permissions[]" :value="perm.id" />
                            {{ perm.name }}
                        </Label>
                    </div>
                </div>
            </div>

            <Button type="submit" :disabled="processing">Create Role</Button>
        </Form>
    </div>
</template>
