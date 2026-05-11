<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login, register } from '@/routes';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);
</script>

<template>
    <Head title="EstateTracker — Land Payment Monitoring">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet" />
    </Head>

    <div class="et-root">
        <!-- Grid background -->
        <div class="et-grid-bg" aria-hidden="true"></div>

        <!-- Nav -->
        <header class="et-nav">
            <div class="et-logo">
                <span class="et-logo-dot"></span>
                EstateTracker
            </div>
            <nav class="et-nav-links">
                <a href="#features" class="et-nav-link">Features</a>
                <a href="#reports" class="et-nav-link">Reports</a>
                <a href="#clients" class="et-nav-link">Clients</a>
            </nav>
            <div class="et-nav-actions">
                <Link v-if="$page.props.auth.user" :href="dashboard()" class="et-btn-fill">
                    Dashboard
                </Link>
                <template v-else>
                    <Link :href="login()" class="et-btn-outline">Log in</Link>
                    <Link v-if="canRegister" :href="register()" class="et-btn-fill">
                        Get started
                    </Link>
                </template>
            </div>
        </header>

        <!-- Hero -->
        <section class="et-hero">
            <p class="et-eyebrow">Land Payment Monitoring</p>
            <h1 class="et-headline">
                Every lot.<br />
                Every payment.<br />
                <em>Always clear.</em>
            </h1>
            <p class="et-subhead">
                Track land and lot payment clients in one place. See who's overdue,
                who's current, and export detailed reports in seconds.
            </p>
            <div class="et-cta-row">
                <Link v-if="$page.props.auth.user" :href="dashboard()" class="et-btn-fill et-btn-lg">
                    Open Dashboard →
                </Link>
                <Link v-else :href="register()" class="et-btn-fill et-btn-lg">
                    Get started →
                </Link>
                <span class="et-cta-note">Laravel 13 · Vue · Inertia</span>
            </div>
        </section>

        <!-- Stats bar -->
        <div class="et-stats-bar">
            <div class="et-stat">
                <div class="et-stat-num">2,<span>418</span></div>
                <div class="et-stat-label">Lots Tracked</div>
            </div>
            <div class="et-stat">
                <div class="et-stat-num"><span>₱</span>84M</div>
                <div class="et-stat-label">Total Portfolio</div>
            </div>
            <div class="et-stat">
                <div class="et-stat-num">98<span>%</span></div>
                <div class="et-stat-label">Collection Rate</div>
            </div>
        </div>

        <!-- Feature cards -->
        <section class="et-features" id="features">
            <p class="et-section-label">What you get</p>
            <div class="et-cards">
                <div class="et-card">
                    <div class="et-card-title">Live Dashboard</div>
                    <div class="et-card-desc">
                        Doughnut and bar charts showing payment status at a glance.
                        Overdue vs current, month over month.
                    </div>
                    <span class="et-status-pill current">● 12 current today</span>
                </div>
                <div class="et-card">
                    <div class="et-card-title">Overdue Alerts</div>
                    <div class="et-card-desc">
                        Filtered client lists showing overdue accounts, days past due,
                        and outstanding balances instantly.
                    </div>
                    <span class="et-status-pill overdue">● 3 overdue</span>
                </div>
                <div class="et-card">
                    <div class="et-card-title">PDF & Excel Export</div>
                    <div class="et-card-desc">
                        Generate client reports as PDF or Excel with one click.
                        Share with management or clients directly.
                    </div>
                    <span class="et-status-pill export">● Export ready</span>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
.et-root {
    font-family: 'DM Sans', sans-serif;
    background: #0D1117;
    color: #E8E4DC;
    min-height: 100vh;
    position: relative;
    overflow-x: hidden;
}

.et-grid-bg {
    position: fixed;
    inset: 0;
    background-image:
        linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
    background-size: 40px 40px;
    pointer-events: none;
    z-index: 0;
}

/* Nav */
.et-nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 48px;
    border-bottom: 0.5px solid rgba(255, 255, 255, 0.08);
    position: relative;
    z-index: 10;
}

.et-logo {
    font-family: 'DM Serif Display', serif;
    font-size: 20px;
    color: #E8E4DC;
    letter-spacing: -0.3px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.et-logo-dot {
    display: inline-block;
    width: 8px;
    height: 8px;
    background: #C8A96E;
    border-radius: 50%;
}

.et-nav-links {
    display: flex;
    gap: 32px;
    align-items: center;
}

.et-nav-link {
    font-size: 13px;
    color: rgba(232, 228, 220, 0.55);
    text-decoration: none;
    transition: color 0.2s;
}

.et-nav-link:hover { color: #E8E4DC; }

.et-nav-actions {
    display: flex;
    gap: 10px;
    align-items: center;
}

/* Buttons */
.et-btn-outline {
    font-size: 13px;
    border: 0.5px solid rgba(200, 169, 110, 0.5);
    color: #C8A96E;
    padding: 7px 18px;
    border-radius: 4px;
    text-decoration: none;
    background: transparent;
    transition: background 0.2s;
    display: inline-block;
}

.et-btn-outline:hover { background: rgba(200, 169, 110, 0.1); }

.et-btn-fill {
    font-size: 13px;
    background: #C8A96E;
    color: #0D1117;
    padding: 8px 20px;
    border-radius: 4px;
    text-decoration: none;
    font-weight: 500;
    display: inline-block;
    transition: opacity 0.2s;
}

.et-btn-fill:hover { opacity: 0.88; }
.et-btn-lg { padding: 10px 28px; font-size: 14px; }

/* Hero */
.et-hero {
    padding: 80px 48px 60px;
    position: relative;
    z-index: 2;
    max-width: 680px;
}

.et-eyebrow {
    font-size: 11px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #C8A96E;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.et-eyebrow::before {
    content: '';
    display: inline-block;
    width: 24px;
    height: 1px;
    background: #C8A96E;
}

.et-headline {
    font-family: 'DM Serif Display', serif;
    font-size: 52px;
    line-height: 1.1;
    letter-spacing: -1px;
    color: #E8E4DC;
    margin: 0 0 20px;
}

.et-headline em {
    font-style: italic;
    color: #C8A96E;
}

.et-subhead {
    font-size: 15px;
    color: rgba(232, 228, 220, 0.55);
    line-height: 1.7;
    max-width: 440px;
    margin-bottom: 36px;
}

.et-cta-row {
    display: flex;
    align-items: center;
    gap: 16px;
}

.et-cta-note {
    font-size: 12px;
    color: rgba(232, 228, 220, 0.3);
}

/* Stats bar */
.et-stats-bar {
    display: flex;
    border-top: 0.5px solid rgba(255, 255, 255, 0.08);
    border-bottom: 0.5px solid rgba(255, 255, 255, 0.08);
    position: relative;
    z-index: 2;
}

.et-stat {
    flex: 1;
    padding: 28px 48px;
    border-right: 0.5px solid rgba(255, 255, 255, 0.08);
}

.et-stat:last-child { border-right: none; }

.et-stat-num {
    font-family: 'DM Serif Display', serif;
    font-size: 32px;
    color: #E8E4DC;
    line-height: 1;
    margin-bottom: 6px;
}

.et-stat-num span { color: #C8A96E; }

.et-stat-label {
    font-size: 11px;
    color: rgba(232, 228, 220, 0.4);
    text-transform: uppercase;
    letter-spacing: 1.5px;
}

/* Feature cards */
.et-features {
    padding: 60px 48px;
    position: relative;
    z-index: 2;
}

.et-section-label {
    font-size: 11px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: rgba(232, 228, 220, 0.3);
    margin-bottom: 32px;
}

.et-cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}

.et-card {
    border: 0.5px solid rgba(255, 255, 255, 0.08);
    border-radius: 6px;
    padding: 28px 24px;
    background: rgba(255, 255, 255, 0.02);
    transition: border-color 0.2s, background 0.2s;
}

.et-card:hover {
    border-color: rgba(200, 169, 110, 0.3);
    background: rgba(200, 169, 110, 0.04);
}

.et-card-title {
    font-size: 14px;
    font-weight: 500;
    color: #E8E4DC;
    margin-bottom: 10px;
}

.et-card-desc {
    font-size: 13px;
    color: rgba(232, 228, 220, 0.45);
    line-height: 1.6;
    margin-bottom: 0;
}

/* Status pills */
.et-status-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 11px;
    padding: 3px 10px;
    border-radius: 20px;
    margin-top: 16px;
}

.et-status-pill.overdue {
    background: rgba(226, 75, 74, 0.12);
    color: #E24B4A;
    border: 0.5px solid rgba(226, 75, 74, 0.25);
}

.et-status-pill.current {
    background: rgba(99, 153, 34, 0.12);
    color: #639922;
    border: 0.5px solid rgba(99, 153, 34, 0.25);
}

.et-status-pill.export {
    background: rgba(200, 169, 110, 0.1);
    color: #C8A96E;
    border: 0.5px solid rgba(200, 169, 110, 0.25);
}
</style>
