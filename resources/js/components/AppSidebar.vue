<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    LayoutGrid,
    Users,
    MapPin,
    Wallet,
    Building2,
    UserRoundCog,
    FileText,
    BarChart3,
    CalendarClock,
    TrendingUp,
    AlertTriangle,
    ClipboardList,
    Percent,
    UserCog,
    ShieldCheck,
    KeyRound,
} from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import * as clientRoute from '@/routes/clients';
import * as lotRoute from '@/routes/lots';
import * as adminUserRoute from '@/routes/admin/users';
import * as adminRoleRoute from '@/routes/admin/roles';
import * as adminPermissionRoute from '@/routes/admin/permissions';
import type { NavGroup } from '@/types';

// 🔧 Settings ay INALIS na dito — nasa UserMenuContent.vue (avatar dropdown) na lang,
// para iwas redundant. Roles & Permissions ay makikita naman sa loob ng Settings page mismo.

const page = usePage();

// 🔧 Flat list ng permission slugs ng naka-login na user, kinukuha mula sa roles.permissions
const userPermissions = computed<string[]>(() => {
    const roles = page.props.auth?.user?.roles ?? [];
    return roles.flatMap((role) => role.permissions ?? []).map((p) => p.slug);
});

const hasPermission = (slug: string) => userPermissions.value.includes(slug);

const navGroups = computed<NavGroup[]>(() => {
    const groups: NavGroup[] = [
        {
            label: 'Platform',
            items: [
                {
                    title: 'Dashboard',
                    href: dashboard(),
                    icon: LayoutGrid,
                },
                {
                    title: 'Clients',
                    href: clientRoute.index(),
                    icon: Users,
                },
                {
                    title: 'Lots',
                    href: lotRoute.index(),
                    icon: MapPin,
                },
            ],
        },
        {
            label: 'Sales & Operations',
            items: [
                // 🔧 STATIC MUNA — wala pang routes/controllers, i-uncomment/i-wire kapag na-build na yung module
                {
                    title: 'Projects', // Subdivisions/Phases — grouping ng mga lots
                    href: '#',
                    icon: Building2,
                },
                {
                    title: 'Payments', // Collections / payment history ledger (hiwalay sa per-lot recordPayment)
                    href: '#',
                    icon: Wallet,
                },
                {
                    title: 'Reservations', // Lot reservation bago maging buong sale
                    href: '#',
                    icon: CalendarClock,
                },
                {
                    title: 'Agents', // Sales agents / brokers + commission tracking
                    href: '#',
                    icon: UserRoundCog,
                },
            ],
        },
        {
            label: 'Records',
            items: [
                {
                    title: 'Documents', // Contracts to sell, deeds, titles
                    href: '#',
                    icon: FileText,
                },
                {
                    // 🔧 STATIC MUNA — Reports collapsible group, exportable to Excel later (xlsx)
                    title: 'Reports', // Analytics / collection reports / aging
                    href: '#',
                    icon: BarChart3,
                    items: [
                        {
                            title: 'Collections Report', // Daily/monthly na na-collect na payments
                            href: '#',
                            icon: Wallet,
                        },
                        {
                            title: 'Aging of Receivables', // Overdue breakdown (30/60/90+ days)
                            href: '#',
                            icon: AlertTriangle,
                        },
                        {
                            title: 'Sales Report', // Per-project/per-agent sales summary
                            href: '#',
                            icon: TrendingUp,
                        },
                        {
                            title: 'Client Statement of Account', // Per-client ledger
                            href: '#',
                            icon: ClipboardList,
                        },
                        {
                            title: 'Lot Inventory Report', // Available/reserved/sold/delinquent per project
                            href: '#',
                            icon: MapPin,
                        },
                        {
                            title: 'Commission Report', // Agent/broker commission summary
                            href: '#',
                            icon: Percent,
                        },
                    ],
                },
            ],
        },
    ];

    // 🔧 Administration group — makikita lang kung may kahit anong admin permission ang naka-login na user
    if (hasPermission('users.view') || hasPermission('roles.view')) {
        groups.push({
            label: 'Administration',
            items: [
                {
                    title: 'User Accounts',
                    href: adminUserRoute.index(),
                    icon: UserCog,
                },
                {
                    title: 'Roles',
                    href: adminRoleRoute.index(),
                    icon: ShieldCheck,
                },
                {
                    title: 'Permissions',
                    href: adminPermissionRoute.index(),
                    icon: KeyRound,
                },
            ],
        });
    }

    return groups;
});
</script>
<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>
        <SidebarContent>
            <NavMain :groups="navGroups" />
        </SidebarContent>
        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
