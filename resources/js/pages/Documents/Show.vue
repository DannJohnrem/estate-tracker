<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, CalendarDays, Download, ExternalLink, FileText, HardDrive, Maximize, Pencil, User, ZoomIn, ZoomOut } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { edit, file, index } from '@/routes/documents';

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

const props = defineProps<{
    document: DocumentItem;
    can: { edit: boolean; delete: boolean };
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Documents', href: index().url },
            { title: 'View Document', href: '#' },
        ],
    },
});

// ?v= changes whenever the document is updated, so a replaced file isn't served from cache
const fileUrl = computed(() => `${file({ document: props.document.id }).url}?v=${props.document.version}`);

// #toolbar=1 keeps the browser's built-in PDF toolbar (zoom, page nav, print)
const pdfViewerUrl = computed(() => `${fileUrl.value}#toolbar=1&view=FitH`);
const downloadUrl = computed(() => `${file({ document: props.document.id }).url}?download=1`);

/* ------------------------------------------------------------------ */
/* Image zoom                                                          */
/* ------------------------------------------------------------------ */

const MIN_ZOOM = 0.25;
const MAX_ZOOM = 5;
const ZOOM_STEP = 0.25;

const container = ref<HTMLElement | null>(null);
const imageEl = ref<HTMLImageElement | null>(null);

const naturalWidth = ref(0);
const naturalHeight = ref(0);

// Width (px) at which the image fits inside the viewer; zoom = 1 means "fit"
const baseWidth = ref(0);
const zoom = ref(1);

// Real size percentage shown in the toolbar (100% = actual pixels)
const actualPercent = computed(() => {
    if (!naturalWidth.value || !baseWidth.value) return 100;
    return Math.round(((baseWidth.value * zoom.value) / naturalWidth.value) * 100);
});

const canZoomIn = computed(() => zoom.value < MAX_ZOOM);
const canZoomOut = computed(() => zoom.value > MIN_ZOOM);

// Work out the "fit" width from the viewer size and the image's natural size
const computeBaseWidth = () => {
    if (!container.value || !naturalWidth.value || !naturalHeight.value) return;

    const padding = 32; // p-4 on both sides
    const availableWidth = container.value.clientWidth - padding;
    const availableHeight = container.value.clientHeight - padding;

    // Never upscale small images past their real size when fitting
    const scale = Math.min(availableWidth / naturalWidth.value, availableHeight / naturalHeight.value, 1);
    baseWidth.value = naturalWidth.value * scale;
};

const onImageLoad = () => {
    if (!imageEl.value) return;

    naturalWidth.value = imageEl.value.naturalWidth;
    naturalHeight.value = imageEl.value.naturalHeight;
    computeBaseWidth();
};

const clampZoom = (value: number) => Math.min(MAX_ZOOM, Math.max(MIN_ZOOM, Math.round(value * 100) / 100));

const zoomIn = () => (zoom.value = clampZoom(zoom.value + ZOOM_STEP));
const zoomOut = () => (zoom.value = clampZoom(zoom.value - ZOOM_STEP));
const fitToScreen = () => (zoom.value = 1);

// Double-click toggles between "fit" and 100% actual size
const toggleActualSize = () => {
    if (zoom.value !== 1) {
        zoom.value = 1;
        return;
    }
    if (baseWidth.value) {
        zoom.value = clampZoom(naturalWidth.value / baseWidth.value);
    }
};

// Ctrl + mouse wheel zooms; normal scrolling still scrolls the viewer
const onWheel = (event: WheelEvent) => {
    if (!event.ctrlKey) return;
    event.preventDefault();
    if (event.deltaY < 0) zoomIn();
    else zoomOut();
};

/* Drag to pan while zoomed in */
const dragging = ref(false);
let startX = 0;
let startY = 0;
let startScrollLeft = 0;
let startScrollTop = 0;

const onMouseDown = (event: MouseEvent) => {
    if (!container.value || zoom.value <= 1) return;
    dragging.value = true;
    startX = event.clientX;
    startY = event.clientY;
    startScrollLeft = container.value.scrollLeft;
    startScrollTop = container.value.scrollTop;
};

const onMouseMove = (event: MouseEvent) => {
    if (!dragging.value || !container.value) return;
    container.value.scrollLeft = startScrollLeft - (event.clientX - startX);
    container.value.scrollTop = startScrollTop - (event.clientY - startY);
};

const endDrag = () => (dragging.value = false);

onMounted(() => {
    window.addEventListener('resize', computeBaseWidth);

    // The image can finish loading before the @load listener runs (browser cache)
    if (imageEl.value?.complete && imageEl.value.naturalWidth) {
        onImageLoad();
    }
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', computeBaseWidth);
});

const formatSize = (bytes: number): string => {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
};
</script>

<template>

    <Head :title="document.name" />

    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">
        <!-- Header -->
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <!-- Left: title + badges (takes the remaining space; badges wrap inside it) -->
            <div class="min-w-0 flex-1">
                <h1 class="truncate text-2xl font-semibold tracking-tight">{{ document.name }}</h1>
                <p v-if="document.description" class="text-sm text-muted-foreground">{{ document.description }}</p>
                <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                    <!-- Original filename -->
                    <span
                        class="inline-flex max-w-full items-center gap-1.5 rounded-full bg-muted px-3 py-1.5 font-medium text-muted-foreground"
                    >
                        <FileText class="h-3.5 w-3.5 shrink-0 text-amber-500" />
                        <span class="truncate" :title="document.original_name">
                            {{ document.original_name }}
                        </span>
                    </span>

                    <!-- Upload date -->
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full bg-muted px-3 py-1.5 font-medium text-muted-foreground"
                    >
                        <CalendarDays class="h-3.5 w-3.5 shrink-0 text-amber-500" />
                        <span class="whitespace-nowrap">Uploaded {{ document.uploaded_at }}</span>
                    </span>

                    <!-- File size -->
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full bg-muted px-3 py-1.5 font-medium text-muted-foreground"
                    >
                        <HardDrive class="h-3.5 w-3.5 shrink-0 text-amber-500" />
                        <span class="whitespace-nowrap">{{ formatSize(document.file_size) }}</span>
                    </span>

                <!-- Uploaded by -->
                <span
                    v-if="document.uploaded_by"
                    class="inline-flex items-center gap-1.5 rounded-full bg-muted px-3 py-1.5 font-medium text-muted-foreground"
                    title="Uploaded by"
                >
                    <User class="h-3.5 w-3.5 shrink-0 text-amber-500" />
                    <span class="whitespace-nowrap">Uploaded by {{ document.uploaded_by }}</span>
                </span>
                </div>
            </div>

            <!-- Right: action buttons stay on the right on large screens -->
            <div class="flex shrink-0 flex-wrap gap-2 lg:justify-end">
                <Button as-child variant="outline">
                    <Link :href="index().url">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Back
                    </Link>
                </Button>
                <Button v-if="can.edit" as-child variant="outline">
                    <Link :href="edit({ document: document.id }).url">
                        <Pencil class="mr-2 h-4 w-4" />
                        Edit
                    </Link>
                </Button>
                <Button as-child variant="outline">
                    <a :href="fileUrl" target="_blank" rel="noopener">
                        <ExternalLink class="mr-2 h-4 w-4" />
                        Open in new tab
                    </a>
                </Button>
                <Button as-child class="bg-amber-500 text-white hover:bg-amber-600">
                    <a :href="downloadUrl">
                        <Download class="mr-2 h-4 w-4" />
                        Download
                    </a>
                </Button>
            </div>
        </div>

        <!-- Image viewer with zoom -->
        <div v-if="document.is_image" class="flex flex-1 flex-col overflow-hidden rounded-lg border bg-muted">
            <!-- Zoom toolbar -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b bg-background px-3 py-2">
                <p class="hidden text-xs text-muted-foreground sm:block">
                    Ctrl + scroll to zoom · Drag to move · Double-click for 100%
                </p>

                <div class="ml-auto flex items-center gap-1">
                    <Button variant="outline" size="icon"
                        class="h-8 w-8 transition-colors hover:border-amber-300 hover:bg-amber-100 hover:text-amber-700 dark:hover:bg-amber-500/20 dark:hover:text-amber-400"
                        :disabled="!canZoomOut" title="Zoom out" @click="zoomOut">
                        <ZoomOut class="h-4 w-4" />
                        <span class="sr-only">Zoom out</span>
                    </Button>

                    <span class="w-14 text-center text-sm font-medium tabular-nums">{{ actualPercent }}%</span>

                    <Button variant="outline" size="icon"
                        class="h-8 w-8 transition-colors hover:border-amber-300 hover:bg-amber-100 hover:text-amber-700 dark:hover:bg-amber-500/20 dark:hover:text-amber-400"
                        :disabled="!canZoomIn" title="Zoom in" @click="zoomIn">
                        <ZoomIn class="h-4 w-4" />
                        <span class="sr-only">Zoom in</span>
                    </Button>

                    <Button variant="outline" size="sm"
                        class="ml-1 h-8 transition-colors hover:border-amber-300 hover:bg-amber-100 hover:text-amber-700 dark:hover:bg-amber-500/20 dark:hover:text-amber-400"
                        title="Fit to screen" @click="fitToScreen">
                        <Maximize class="mr-1.5 h-4 w-4" />
                        Fit
                    </Button>
                </div>
            </div>

            <!-- Scrollable image area -->
            <div ref="container" class="flex h-[75vh] overflow-auto p-4 select-none"
                :class="zoom > 1 ? (dragging ? 'cursor-grabbing' : 'cursor-grab') : ''" @wheel="onWheel"
                @mousedown="onMouseDown" @mousemove="onMouseMove" @mouseup="endDrag" @mouseleave="endDrag">
                <!-- m-auto centers the image when small and keeps it scrollable when large -->
                <img ref="imageEl" :src="fileUrl" :alt="document.name" draggable="false"
                    class="m-auto max-w-none shrink-0 rounded shadow-md transition-[width] duration-150"
                    :class="baseWidth ? '' : 'opacity-0'"
                    :style="baseWidth ? { width: `${baseWidth * zoom}px`, height: 'auto' } : undefined"
                    @load="onImageLoad" @dblclick="toggleActualSize" />
            </div>
        </div>

        <!-- PDF viewer -->
        <div v-else class="flex-1 overflow-hidden rounded-lg border bg-muted">
            <iframe :src="pdfViewerUrl" :title="document.name" class="h-[80vh] w-full" />
        </div>
    </div>
</template>
