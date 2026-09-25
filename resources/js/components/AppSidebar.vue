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
import * as paymentRoute from '@/routes/payments';
import type { NavGroup, NavItem } from '@/types';

// 🔧 Settings ay nasa UserMenuContent.vue (avatar dropdown).

const page = usePage();

// 🔧 Flat list ng permission slugs ng naka-login na user, kinukuha mula sa roles.permissions
const userPermissions = computed<string[]>(() => {
    const roles = page.props.auth?.user?.roles ?? [];
    return roles.flatMap((role) => role.permissions ?? []).map((p) => p.slug);
});

const hasPermission = (slug: string) => userPermissions.value.includes(slug);

// 🔧 NavItem na may optional na permission slug. Walang permission = kita ng lahat.
type GatedNavItem = Omit<NavItem, 'items'> & {
    permission?: string;
    items?: GatedNavItem[];
};

// 🔧 Sinasala ang items ayon sa permission, pati ang sub-items (hal. Reports).
// Kapag walang natirang sub-item, itatago rin ang parent.
const filterItems = (items: GatedNavItem[]): GatedNavItem[] =>
    items
        .filter((item) => !item.permission || hasPermission(item.permission))
        .map((item) =>
            item.items ? { ...item, items: filterItems(item.items) } : item,
        )
        .filter((item) => !item.items || item.items.length > 0);

const navGroups = computed<NavGroup[]>(() => {
    const allGroups: { label: string; items: GatedNavItem[] }[] = [
        {
            label: 'Platform',
            items: [
                {
                    title: 'Dashboard', // walang permission, kita ng lahat
                    href: dashboard(),
                    icon: LayoutGrid,
                },
                {
                    title: 'Clients',
                    href: clientRoute.index(),
                    icon: Users,
                    permission: 'clients.view',
                },
                {
                    title: 'Lots',
                    href: lotRoute.index(),
                    icon: MapPin,
                    permission: 'lots.view',
                },
            ],
        },
        {
            label: 'Sales & Operations',
            items: [
                // 🔧 STATIC MUNA — wala pang routes/controllers
                {
                    title: 'Projects',
                    href: '#',
                    icon: Building2,
                    permission: 'projects.view',
                },
                {
                    title: 'Payments',
                    href: paymentRoute.index(),
                    icon: Wallet,
                    permission: 'payments.view',
                },
                {
                    title: 'Reservations',
                    href: '#',
                    icon: CalendarClock,
                    permission: 'reservations.view',
                },
                {
                    title: 'Agents',
                    href: '#',
                    icon: UserRoundCog,
                    permission: 'agents.view',
                },
            ],
        },
        {
            label: 'Records',
            items: [
                {
                    title: 'Documents',
                    href: '#',
                    icon: FileText,
                    permission: 'documents.view',
                },
                {
                    // 🔧 STATIC MUNA — Reports collapsible group
                    title: 'Reports',
                    href: '#',
                    icon: BarChart3,
                    permission: 'reports.view', // sakop nito ang lahat ng sub-reports
                    items: [
                        { title: 'Collections', href: '#', icon: Wallet },          // Collections Report
                        { title: 'Overdue Accounts', href: '#', icon: AlertTriangle }, // Aging of Receivables (30/60/90+ days)
                        { title: 'Sales', href: '#', icon: TrendingUp },              // Sales Report
                        { title: 'Client Statements', href: '#', icon: ClipboardList }, // Client Statement of Account
                        { title: 'Lot Inventory', href: '#', icon: MapPin },          // Lot Inventory Report
                        { title: 'Commissions', href: '#', icon: Percent },           // Commission Report
                    ],
                },
            ],
        },
        {
            label: 'Administration',
            items: [
                {
                    title: 'User Accounts',
                    href: adminUserRoute.index(),
                    icon: UserCog,
                    permission: 'administration-view-users',
                },
                {
                    title: 'Roles',
                    href: adminRoleRoute.index(),
                    icon: ShieldCheck,
                    permission: 'administration-view-roles',
                },
                {
                    title: 'Permissions',
                    href: adminPermissionRoute.index(),
                    icon: KeyRound,
                    permission: 'administration-view-permission',
                },
            ],
        },
    ];

    // 🔧 Itago ang mga group na walang natirang visible item
    return allGroups
        .map((group) => ({ ...group, items: filterItems(group.items) }))
        .filter((group) => group.items.length > 0);
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
