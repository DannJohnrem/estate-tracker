<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import DocumentForm from '@/components/documents/DocumentForm.vue';
import { create, index } from '@/routes/documents';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Documents', href: index().url },
            { title: 'Upload', href: create().url },
        ],
    },
});
</script>

<template>
    <Head title="Upload Document" />

    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Upload Document</h1>
            <p class="text-sm text-muted-foreground">Add a new PDF document.</p>
        </div>

        <div class="max-w-2xl">
            <DocumentForm />
        </div>
    </div>
</template>
