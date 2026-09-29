<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { FileText } from 'lucide-vue-next';
import { computed } from 'vue';
import { toast } from 'vue-sonner';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { index, store, update } from '@/routes/documents';

interface DocumentItem {
    id: string;
    name: string;
    description: string | null;
    original_name: string;
}

const props = defineProps<{
    document?: DocumentItem;
}>();

const isEdit = computed(() => !!props.document);

const form = useForm<{
    name: string;
    description: string;
    file: File | null;
}>({
    name: props.document?.name ?? '',
    description: props.document?.description ?? '',
    file: null,
});

const onFileChange = (event: Event) => {
    const input = event.target as HTMLInputElement;
    form.file = input.files?.[0] ?? null;
};

// Toast callbacks shared by create and edit
const visitOptions = {
    forceFormData: true,
    onSuccess: () => {
        toast.success(isEdit.value ? 'Document updated successfully.' : 'Document uploaded successfully.');
    },
    onError: (errors: Record<string, string>) => {
        toast.error(errors.file ?? errors.name ?? errors.description ?? 'Something went wrong. Please try again.');
    },
};

const submit = () => {
    if (isEdit.value && props.document) {
        // PHP cannot read multipart data on PUT, so spoof the method over POST
        form.transform((data) => ({ ...data, _method: 'put' })).post(
            update({ document: props.document.id }).url,
            visitOptions,
        );
        return;
    }

    form.post(store().url, visitOptions);
};
</script>

<template>
    <form @submit.prevent="submit">
        <Card>
            <CardHeader>
                <CardTitle>Document Details</CardTitle>
                <CardDescription>
                    {{ isEdit ? 'Update the details, or upload a new file to replace the current one.' : 'Give the document a name and upload the file.' }}
                </CardDescription>
            </CardHeader>

            <CardContent class="space-y-5">
                <div class="space-y-2">
                    <Label for="name">Name</Label>
                    <Input id="name" v-model="form.name" placeholder="e.g. Contract to Sell - Lot 12 Block 3" />
                    <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                </div>

                <div class="space-y-2">
                    <Label for="description">Description</Label>
                    <Textarea
                        id="description"
                        v-model="form.description"
                        rows="4"
                        placeholder="Short description of this document (optional)"
                    />
                    <p v-if="form.errors.description" class="text-sm text-destructive">{{ form.errors.description }}</p>
                </div>

                <div class="space-y-2">
                    <Label for="file">File</Label>
                    <Input
                        id="file"
                        type="file"
                        accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                        @change="onFileChange"
                    />
                    <p v-if="isEdit && document" class="flex items-center gap-1.5 text-xs text-muted-foreground">
                        <FileText class="h-3.5 w-3.5" />
                        Current file: {{ document.original_name }}. Leave empty to keep it.
                    </p>
                    <p v-else class="text-xs text-muted-foreground">PDF, JPG, JPEG or PNG, up to 20 MB.</p>
                    <p v-if="form.errors.file" class="text-sm text-destructive">{{ form.errors.file }}</p>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <Button as-child variant="outline" type="button">
                        <Link :href="index().url">Cancel</Link>
                    </Button>
                    <Button type="submit" class="bg-amber-500 text-white hover:bg-amber-600" :disabled="form.processing">
                        {{ form.processing ? 'Saving...' : isEdit ? 'Save Changes' : 'Upload Document' }}
                    </Button>
                </div>
            </CardContent>
        </Card>
    </form>
</template>
