<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import { ShieldPlus, ShieldCheck } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import * as permissionRoute from '@/routes/admin/permissions';

type PermissionData = {
    id: string;
    group: string;
    slug: string;
    description: string | null;
};

const props = defineProps<{
    mode: 'create' | 'edit';
    permission?: PermissionData;
    groups: string[];
    actions: string[];
}>();

const form = useForm({
    group: props.permission?.group ?? '',
    action: props.permission?.slug.split('.').pop() ?? '',
    description: props.permission?.description ?? '',
});

// ── Custom group autocomplete ──
const groupOpen = ref(false);

const filteredGroups = computed(() => {
    const q = form.group.trim().toLowerCase();
    if (!q) return props.groups;
    return props.groups.filter((g) => g.toLowerCase().includes(q));
});

const selectGroup = (g: string) => {
    form.group = g;
    groupOpen.value = false;
};

const closeGroupDropdown = () => {
    setTimeout(() => (groupOpen.value = false), 150);
};

const submit = () => {
    if (props.mode === 'create') {
        form.post(permissionRoute.store().url, {
            preserveScroll: true,
        });
    } else {
        form.put(permissionRoute.update({ permission: props.permission!.id }).url, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <form @submit.prevent="submit" class="mx-auto flex w-full max-w-3xl flex-col gap-6">
        <div class="rounded-xl border p-6">
            <div class="mb-6 flex items-start gap-2">
                <component :is="mode === 'create' ? ShieldPlus : ShieldCheck" class="mt-0.5 h-4 w-4 text-amber-600" />
                <div>
                    <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Permission Details</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Basic details of the permission</p>
                </div>
            </div>

            <div class="grid gap-6">
                <div class="grid gap-6 sm:grid-cols-2">
                    <!-- Module / Group — custom autocomplete using the real Input component -->
                    <div class="grid gap-2">
                        <Label for="group">Module / Group <span class="text-red-500">*</span></Label>
                        <div class="relative">
                            <Input
                                id="group"
                                v-model="form.group"
                                type="text"
                                autocomplete="off"
                                placeholder="Select or type a new module"
                                required
                                :class="{ 'border-red-400 focus-visible:border-red-400 focus-visible:ring-red-400': form.errors.group }"
                                @focus="groupOpen = true"
                                @blur="closeGroupDropdown"
                            />

                            <div
                                v-if="groupOpen && filteredGroups.length > 0"
                                class="absolute z-50 mt-1 max-h-56 w-full overflow-auto rounded-md border border-gray-200 bg-white py-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-900"
                            >
                                <button
                                    v-for="g in filteredGroups"
                                    :key="g"
                                    type="button"
                                    class="flex w-full items-center px-3 py-2 text-left text-sm text-gray-700 hover:bg-amber-50 dark:text-gray-200 dark:hover:bg-amber-900/20"
                                    @mousedown.prevent="selectGroup(g)"
                                >
                                    {{ g }}
                                </button>
                            </div>
                        </div>
                        <InputError :message="form.errors.group" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="action">Action <span class="text-red-500">*</span></Label>
                        <Select v-model="form.action">
                            <SelectTrigger id="action" class="w-full" :class="{ 'border-red-400': form.errors.action }">
                                <SelectValue placeholder="Select action" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="a in actions" :key="a" :value="a">
                                    {{ a.charAt(0).toUpperCase() + a.slice(1) }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.action" />
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="description">Description <span class="text-gray-400">(optional)</span></Label>
                    <Textarea
                        id="description"
                        v-model="form.description"
                        placeholder="What does this permission allow?"
                        rows="3"
                        :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-400': form.errors.description }"
                    />
                    <InputError :message="form.errors.description" />
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <Link :href="permissionRoute.index().url">
                <Button type="button" variant="outline">Cancel</Button>
            </Link>
            <Button type="submit" :disabled="form.processing" class="bg-amber-600 hover:bg-amber-700">
                {{ form.processing ? 'Saving...' : mode === 'create' ? 'Create Permission' : 'Save Changes' }}
            </Button>
        </div>
    </form>
</template>
