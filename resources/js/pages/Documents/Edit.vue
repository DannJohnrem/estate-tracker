<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import DocumentForm from '@/components/documents/DocumentForm.vue';
import { index } from '@/routes/documents';

defineProps<{
    document: {
        id: string;
        name: string;
        description: string | null;
        original_name: string;
    };
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Documents', href: index().url },
            { title: 'Edit', href: '#' },
        ],
    },
});
</script>

<template>
    <Head title="Edit Document" />

    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Edit Document</h1>
            <p class="text-sm text-muted-foreground">Update the name, description, or replace the PDF.</p>
        </div>

        <div class="max-w-2xl">
            <DocumentForm :document="document" />
        </div>
    </div>
</template>
