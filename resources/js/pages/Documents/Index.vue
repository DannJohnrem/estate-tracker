<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    CalendarDays,
    EllipsisVertical,
    Eye,
    FileText,
    HardDrive,
    Pencil,
    Plus,
    Trash2,
} from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { create, destroy, edit, index, show } from '@/routes/documents';

interface DocumentItem {
    id: string;
    name: string;
    description: string | null;
    original_name: string;
    file_size: number;
    extension: string;
    is_image: boolean;
    uploaded_by: string | null;
    uploaded_at: string;
    version: number | null;
}

interface Pagination {
    data: DocumentItem[];
    links: { url: string | null; label: string; active: boolean }[];
}

defineProps<{
    documents: Pagination;
    can: { create: boolean; edit: boolean; delete: boolean };
}>();

// Breadcrumbs are static here, so they are safe inside defineOptions
defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Documents', href: index().url }],
    },
});

const documentToDelete = ref<DocumentItem | null>(null);
const deleting = ref(false);

const formatSize = (bytes: number): string => {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
};

// First letters of the first two words, used for the uploader avatar
const initials = (name: string | null): string => {
    if (!name) return '?';
    return name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0].toUpperCase())
        .join('');
};

const confirmDelete = () => {
    if (!documentToDelete.value) return;

    deleting.value = true;

    router.delete(destroy({ document: documentToDelete.value.id }).url, {
        preserveScroll: true,
        onSuccess: () => toast.success('Document deleted successfully.'),
        onError: () => toast.error('Failed to delete the document. Please try again.'),
        onFinish: () => {
            deleting.value = false;
            documentToDelete.value = null;
        },
    });
};
</script>

<template>
    <Head title="Documents" />

    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Documents</h1>
                <p class="text-sm text-muted-foreground">Upload and view PDF and image documents such as contracts and titles.</p>
            </div>
            <Button v-if="can.create" as-child class="bg-amber-500 text-white hover:bg-amber-600">
                <Link :href="create().url">
                    <Plus class="mr-2 h-4 w-4" />
                    Upload Document
                </Link>
            </Button>
        </div>

        <!-- Empty state -->
        <div
            v-if="documents.data.length === 0"
            class="flex flex-1 flex-col items-center justify-center rounded-xl border border-dashed p-12 text-center"
        >
            <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-100 text-amber-600 dark:bg-amber-500/15">
                <FileText class="h-8 w-8" />
            </div>
            <p class="font-medium">No documents yet</p>
            <p class="text-sm text-muted-foreground">Upload your first PDF or image to get started.</p>
        </div>

        <!-- Cards grid -->
        <div v-else class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <Card
                v-for="doc in documents.data"
                :key="doc.id"
                class="group gap-0 overflow-hidden p-0 transition-all duration-200 hover:-translate-y-1 hover:border-amber-300 hover:shadow-xl hover:shadow-amber-500/10 dark:hover:border-amber-500/40"
            >
                <!-- Preview area (click to open the viewer) -->
                <div class="relative">
                    <!-- Same preview design for every file type; only the badge text changes -->
                    <Link
                        :href="show({ document: doc.id }).url"
                        class="relative flex h-40 items-end justify-center overflow-hidden bg-gradient-to-br from-amber-50 via-amber-100/70 to-orange-100 px-6 pt-6 dark:from-amber-500/10 dark:via-amber-500/5 dark:to-orange-500/10"
                    >
                        <div
                            class="relative h-full w-28 translate-y-3 rounded-t-md border border-b-0 bg-white p-3 shadow-lg transition-transform duration-200 group-hover:translate-y-1 dark:bg-zinc-900"
                        >
                            <span
                                class="absolute -top-2 -right-2 rounded px-1.5 py-0.5 text-[10px] font-bold tracking-wide text-white uppercase shadow"
                                :class="{
                                    'bg-red-500': doc.extension.toLowerCase() === 'pdf',
                                    'bg-blue-500': ['jpg', 'jpeg'].includes(doc.extension.toLowerCase()),
                                    'bg-emerald-500': doc.extension.toLowerCase() === 'png',
                                    'bg-zinc-500': !['pdf', 'jpg', 'jpeg', 'png'].includes(doc.extension.toLowerCase()),
                                }"
                            >
                                {{ doc.extension }}
                            </span>
                            <div class="mb-3 h-2 w-3/5 rounded bg-amber-400/80" />
                            <div class="space-y-1.5">
                                <div class="h-1.5 w-full rounded bg-zinc-200 dark:bg-zinc-700" />
                                <div class="h-1.5 w-11/12 rounded bg-zinc-200 dark:bg-zinc-700" />
                                <div class="h-1.5 w-full rounded bg-zinc-200 dark:bg-zinc-700" />
                                <div class="h-1.5 w-4/5 rounded bg-zinc-200 dark:bg-zinc-700" />
                                <div class="h-1.5 w-2/3 rounded bg-zinc-200 dark:bg-zinc-700" />
                            </div>
                        </div>
                    </Link>

                    <!-- 3-dots menu (sibling of the link so clicks don't navigate) -->
                    <DropdownMenu v-if="can.edit || can.delete">
                        <DropdownMenuTrigger as-child>
                            <Button
                                variant="secondary"
                                size="icon"
                                class="absolute top-3 right-3 h-8 w-8 cursor-pointer rounded-full border border-transparent bg-background/80 shadow-sm backdrop-blur transition-all duration-150 hover:scale-110 hover:border-amber-300 hover:bg-amber-100 hover:text-amber-700 focus-visible:ring-2 focus-visible:ring-amber-400 active:scale-95 data-[state=open]:scale-105 data-[state=open]:border-amber-500 data-[state=open]:bg-amber-500 data-[state=open]:text-white dark:hover:bg-amber-500/20 dark:hover:text-amber-400"
                            >
                                <EllipsisVertical class="h-4 w-4" />
                                <span class="sr-only">Open menu</span>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="min-w-36">
                            <DropdownMenuItem
                                v-if="can.edit"
                                as-child
                                class="cursor-pointer focus:bg-amber-50 focus:text-amber-700 dark:focus:bg-amber-500/15 dark:focus:text-amber-400"
                            >
                                <Link :href="edit({ document: doc.id }).url">
                                    <Pencil class="mr-2 h-4 w-4" />
                                    Edit
                                </Link>
                            </DropdownMenuItem>
                            <DropdownMenuSeparator v-if="can.edit && can.delete" />
                            <DropdownMenuItem
                                v-if="can.delete"
                                class="cursor-pointer text-destructive focus:bg-red-50 focus:text-red-600 dark:focus:bg-red-500/15 dark:focus:text-red-400"
                                @select="documentToDelete = doc"
                            >
                                <Trash2 class="mr-2 h-4 w-4" />
                                Delete
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>

                <!-- Body -->
                <div class="flex flex-1 flex-col gap-3 p-4">
                    <div class="space-y-1">
                        <h3 class="line-clamp-1 font-semibold leading-tight" :title="doc.name">{{ doc.name }}</h3>
                        <p class="line-clamp-2 min-h-[2.5rem] text-sm text-muted-foreground">
                            {{ doc.description || 'No description' }}
                        </p>
                    </div>

                    <!-- File name -->
                    <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
                        <FileText class="h-3.5 w-3.5 shrink-0 text-amber-500" />
                        <span class="truncate" :title="doc.original_name">{{ doc.original_name }}</span>
                    </div>

                    <!-- Chips -->
                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex items-center gap-1 rounded-full bg-muted px-2.5 py-1 text-xs font-medium text-muted-foreground">
                            <HardDrive class="h-3 w-3" />
                            {{ formatSize(doc.file_size) }}
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-muted px-2.5 py-1 text-xs font-medium text-muted-foreground">
                            <CalendarDays class="h-3 w-3" />
                            {{ doc.uploaded_at }}
                        </span>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-between gap-3 border-t bg-muted/30 px-4 py-3">
                    <div class="flex min-w-0 items-center gap-2">
                        <div
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-amber-500 text-[10px] font-semibold text-white"
                        >
                            {{ initials(doc.uploaded_by) }}
                        </div>
                        <span class="truncate text-xs text-muted-foreground">{{ doc.uploaded_by ?? 'Unknown' }}</span>
                    </div>

                    <Button as-child size="sm" class="shrink-0 bg-amber-500 text-white hover:bg-amber-600">
                        <Link :href="show({ document: doc.id }).url">
                            <Eye class="mr-1.5 h-4 w-4" />
                            View
                        </Link>
                    </Button>
                </div>
            </Card>
        </div>

        <!-- Pagination -->
        <div v-if="documents.links.length > 3" class="flex flex-wrap justify-center gap-1">
            <template v-for="(link, i) in documents.links" :key="i">
                <Button
                    v-if="link.url"
                    as-child
                    size="sm"
                    :variant="link.active ? 'default' : 'outline'"
                    :class="link.active ? 'bg-amber-500 text-white hover:bg-amber-600' : ''"
                >
                    <Link :href="link.url" preserve-scroll><span v-html="link.label" /></Link>
                </Button>
                <Button v-else size="sm" variant="outline" disabled><span v-html="link.label" /></Button>
            </template>
        </div>
    </div>

    <!-- Delete confirmation -->
    <Dialog :open="!!documentToDelete" @update:open="(open: boolean) => !open && (documentToDelete = null)">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Delete document?</DialogTitle>
                <DialogDescription>
                    "{{ documentToDelete?.name }}" and its file will be permanently deleted. This action cannot be undone.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter class="gap-2">
                <Button variant="outline" :disabled="deleting" @click="documentToDelete = null">Cancel</Button>
                <Button variant="destructive" :disabled="deleting" @click="confirmDelete">
                    {{ deleting ? 'Deleting...' : 'Delete' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
