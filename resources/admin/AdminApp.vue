<template>
    <div
        class="min-h-screen text-slate-900"
        :class="isGuestLayout ? 'bg-slate-950' : 'bg-[radial-gradient(circle_at_top,#eff6ff,transparent_30%),linear-gradient(180deg,#f8fafc_0%,#eef2ff_100%)]'"
    >
        <router-view v-if="isGuestLayout" />

        <div class="flex min-h-screen">
            <template v-if="!isGuestLayout">
            <aside class="hidden w-72 shrink-0 flex-col bg-slate-950 text-slate-300 md:flex">
                <div class="border-b border-white/10 px-6 py-6">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-linear-to-br from-blue-500 to-cyan-400 font-black text-slate-950">
                            CL
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Cloudy Learning</p>
                            <h1 class="text-lg font-semibold text-white">Admin Console</h1>
                        </div>
                    </div>
                </div>

                <nav class="flex-1 space-y-2 px-4 py-6">
                    <RouterLink
                        v-for="item in navItems"
                        :key="item.to"
                        :to="item.to"
                        class="group flex items-center justify-between rounded-2xl px-4 py-3 transition"
                        active-class="bg-blue-600 text-white shadow-lg shadow-blue-900/30"
                    >
                        <span class="font-medium">{{ item.label }}</span>
                        <span class="text-xs uppercase tracking-[0.3em] text-current/60">{{ item.short }}</span>
                    </RouterLink>
                </nav>

                <div class="mx-4 mb-4 rounded-3xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-500">System Status</p>
                    <p class="mt-3 text-sm text-white">Content pipeline is healthy and ready for new lessons.</p>
                    <div class="mt-4 flex items-center gap-2 text-sm text-emerald-300">
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                        Live synchronization enabled
                    </div>
                </div>
            </aside>

            <div class="flex min-h-screen flex-1 flex-col">
                <Header :nav-items="navItems" :today-label="todayLabel" :user="adminUser" />

                <main class="flex-1 px-5 py-6 sm:px-8 lg:px-10 lg:py-8">
                    <router-view />
                </main>

                <Footer :today-label="todayLabel" />
            </div>
            </template>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import Footer from './components/Footer.vue';
import Header from './components/Header.vue';
import { useAdminUser } from './composables/useAdminUser.js';
import { useRoute } from 'vue-router';

const { t } = useI18n();

const navItems = computed(() => [
    { to: '/dashboard', label: t('admin.nav.dashboard'), short: '01' },
    { to: '/users',     label: t('admin.nav.users'),     short: '02' },
    { to: '/categories',label: t('admin.nav.categories'),short: '03' },
    { to: '/courses',   label: t('admin.nav.courses'),   short: '04' },
]);

const route = useRoute();
const { adminUser } = useAdminUser();

const isGuestLayout = computed(() => Boolean(route.meta.guestLayout));

const todayLabel = new Intl.DateTimeFormat('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
}).format(new Date());
</script>
