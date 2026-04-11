<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import { ref, onMounted } from 'vue';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

// Placeholder stats data
const stats = {
    totalClients: 124,
    overDuePayments: 12,
    currentPayments: 34,
    paidPayments: 78,
    totalCollectibleAmount: 50000,
    totalCollectedAmount: 45000,
    overDueBalance: 10000,
    currentBalance: 20000,
};

const topOverdue = [
    { name: 'Ana Lim', email: 'ana@email.com', lot: '15', block: '4', subdivision: 'Sunshine Homes', balance: 780000, monthsOverdue: 16 },
    { name: 'Juan dela Cruz', email: 'juan@email.com', lot: '12', block: '3', subdivision: 'Villa Ramos', balance: 730000, monthsOverdue: 14 },
    { name: 'Roberto Cruz', email: 'roberto@email.com', lot: '7', block: '2', subdivision: 'Green Valley', balance: 520000, monthsOverdue: 11 },
    { name: 'Linda Torres', email: 'linda@email.com', lot: '2', block: '6', subdivision: 'Villa Ramos', balance: 430000, monthsOverdue: 9 },
    { name: 'Mario Bautista', email: 'mario@email.com', lot: '9', block: '1', subdivision: 'Sunshine Homes', balance: 310000, monthsOverdue: 7 },
];

const statusChartRef = ref<HTMLCanvasElement | null>(null);
const monthlyChartRef = ref<HTMLCanvasElement | null>(null);

const formatPeso = (amount: number) =>
    '₱ ' + amount.toLocaleString('en-PH', { minimumFractionDigits: 0 });

const formatPesoShort = (amount: number) =>
    '₱' + (amount / 1000000).toFixed(1) + 'M';

onMounted(() => {
    // Doughnut Chart — Status Breakdown
    if (statusChartRef.value) {
        new Chart(statusChartRef.value, {
            type: 'doughnut',
            data: {
                labels: ['Overdue', 'Current', 'Paid'],
                datasets: [{
                    data: [stats.overdue, stats.current, stats.paid],
                    backgroundColor: ['#ef4444', '#f59e0b', '#22c55e'],
                    borderWidth: 0,
                    hoverOffset: 6,
                }],
            },
            options: {
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 16, font: { size: 12 } },
                    },
                },
                cutout: '68%',
            },
        });
    }

    // Bar Chart — Monthly Collections
    if (monthlyChartRef.value) {
        new Chart(monthlyChartRef.value, {
            type: 'bar',
            data: {
                labels: ['Nov 2024', 'Dec 2024', 'Jan 2025', 'Feb 2025', 'Mar 2025', 'Apr 2025'],
                datasets: [{
                    label: 'Collections',
                    data: [1200000, 980000, 1450000, 1100000, 1320000, 870000],
                    backgroundColor: '#6366f1',
                    borderRadius: 6,
                    borderSkipped: false,
                }],
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        ticks: { callback: (v) => formatPesoShort(v as number) },
                        grid: { color: 'rgba(0,0,0,0.05)' },
                    },
                    x: { grid: { display: false } },
                },
            },
        });
    }
});
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Dashboard</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">EstateTracker — Land Payment Monitoring</p>
            </div>
            <button class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                View Full Report
            </button>
        </div>

        <!-- Stat Cards -->
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">

            <!-- Total Clients -->
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-3 flex items-center justify-between">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Total Clients</p>
                    <div class="rounded-lg bg-blue-50 p-2 dark:bg-blue-900/30">
                        <svg class="size-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ stats.totalClients }}</p>
                <p class="mt-1 text-xs text-gray-400">All registered clients</p>
            </div>

            <!-- Overdue -->
            <div class="rounded-xl border border-red-200 bg-white p-5 dark:border-red-800 dark:bg-zinc-900">
                <div class="mb-3 flex items-center justify-between">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Overdue</p>
                    <div class="rounded-lg bg-red-50 p-2 dark:bg-red-900/30">
                        <svg class="size-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ stats.overDuePayments }}</p>
                <p class="mt-1 text-xs text-gray-400">Past due date</p>
            </div>

            <!-- Current -->
            <div class="rounded-xl border border-yellow-200 bg-white p-5 dark:border-yellow-800 dark:bg-zinc-900">
                <div class="mb-3 flex items-center justify-between">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Current</p>
                    <div class="rounded-lg bg-yellow-50 p-2 dark:bg-yellow-900/30">
                        <svg class="size-4 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ stats.currentPayments }}</p>
                <p class="mt-1 text-xs text-gray-400">On-time payments</p>
            </div>

            <!-- Fully Paid -->
            <div class="rounded-xl border border-green-200 bg-white p-5 dark:border-green-800 dark:bg-zinc-900">
                <div class="mb-3 flex items-center justify-between">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Fully Paid</p>
                    <div class="rounded-lg bg-green-50 p-2 dark:bg-green-900/30">
                        <svg class="size-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ stats.paidPayments }}</p>
                <p class="mt-1 text-xs text-gray-400">Completed payments</p>
            </div>

        </div>

        <!-- Collection Summary -->
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="mb-2 text-xs font-medium uppercase tracking-wide text-gray-500">Total Collectible</p>
                <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ formatPeso(stats.totalCollectibleAmount) }}</p>
                <p class="mt-1 text-xs text-gray-400">Combined lot prices</p>
            </div>

            <div class="rounded-xl border border-green-200 bg-white p-5 dark:border-green-800 dark:bg-zinc-900">
                <p class="mb-2 text-xs font-medium uppercase tracking-wide text-gray-500">Total Collected</p>
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ formatPeso(stats.totalCollectedAmount) }}</p>
                <p class="mt-1 text-xs text-gray-400">Total amount paid</p>
            </div>

            <div class="rounded-xl border border-red-200 bg-white p-5 dark:border-red-800 dark:bg-zinc-900">
                <p class="mb-2 text-xs font-medium uppercase tracking-wide text-gray-500">Overdue Balance</p>
                <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ formatPeso(stats.overDueBalance) }}</p>
                <p class="mt-1 text-xs text-gray-400">Unpaid overdue amount</p>
            </div>

        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

            <!-- Doughnut Chart -->
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="font-medium text-gray-700 dark:text-gray-300">Client Status Breakdown</h2>
                <p class="mb-4 text-xs text-gray-400">Distribution of all clients by payment status</p>
                <canvas ref="statusChartRef" height="220"></canvas>
            </div>

            <!-- Bar Chart -->
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="font-medium text-gray-700 dark:text-gray-300">Monthly Collections</h2>
                <p class="mb-4 text-xs text-gray-400">Total collected per month (last 6 months)</p>
                <canvas ref="monthlyChartRef" height="220"></canvas>
            </div>

        </div>

        <!-- Top Overdue Table -->
        <div class="rounded-xl border border-gray-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-zinc-700">
                <div>
                    <h2 class="font-medium text-gray-700 dark:text-gray-300">Top Overdue Clients</h2>
                    <p class="mt-0.5 text-xs text-gray-400">Clients with longest overdue payments</p>
                </div>
                <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-700 dark:bg-red-900/30 dark:text-red-400">
                    {{ stats.overDuePayments }} overdue
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50 dark:border-zinc-800 dark:bg-zinc-800/50">
                            <th class="px-5 py-3 text-left text-xs font-medium uppercase text-gray-500">Client</th>
                            <th class="px-5 py-3 text-left text-xs font-medium uppercase text-gray-500">Lot / Block</th>
                            <th class="px-5 py-3 text-left text-xs font-medium uppercase text-gray-500">Subdivision</th>
                            <th class="px-5 py-3 text-right text-xs font-medium uppercase text-gray-500">Balance</th>
                            <th class="px-5 py-3 text-right text-xs font-medium uppercase text-gray-500">Months Overdue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
                        <tr
                            v-for="client in topOverdue"
                            :key="client.email"
                            class="transition-colors hover:bg-red-50 dark:hover:bg-red-900/10"
                        >
                            <td class="px-5 py-3">
                                <p class="font-medium text-gray-800 dark:text-gray-200">{{ client.name }}</p>
                                <p class="text-xs text-gray-400">{{ client.email }}</p>
                            </td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-400">
                                Lot {{ client.lot }}, Blk {{ client.block }}
                            </td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-400">{{ client.subdivision }}</td>
                            <td class="px-5 py-3 text-right font-medium text-red-600 dark:text-red-400">
                                {{ formatPeso(client.balance) }}
                            </td>
                            <td class="px-5 py-3 text-right">
                                <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-700 dark:bg-red-900/30 dark:text-red-400">
                                    {{ client.monthsOverdue }} mos.
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</template>
