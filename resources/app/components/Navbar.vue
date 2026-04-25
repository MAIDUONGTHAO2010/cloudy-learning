<template>
    <!-- Logout overlay -->
    <Transition name="fade">
        <div
            v-if="loggingOut"
            class="fixed inset-0 z-[9999] flex flex-col items-center justify-center gap-4 bg-slate-950/90 backdrop-blur-sm"
        >
            <template v-if="logoutSuccess">
                <div class="grid h-14 w-14 place-items-center rounded-full bg-emerald-500/20">
                    <svg class="h-7 w-7 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <p class="text-base font-semibold text-white">{{ t('nav.logoutSuccess') }}</p>
            </template>
            <template v-else>
                <svg class="h-10 w-10 animate-spin text-slate-400" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
                </svg>
                <p class="text-sm text-slate-400">{{ t('nav.loggingOut') }}</p>
            </template>
        </div>
    </Transition>

    <!-- Mobile menu backdrop -->
    <Transition name="fade">
        <div
            v-if="mobileOpen"
            class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-sm md:hidden"
            @click="mobileOpen = false"
        />
    </Transition>

    <!-- Mobile menu drawer -->
    <Transition name="slide-down">
        <div
            v-if="mobileOpen"
            class="fixed inset-x-0 top-0 z-50 border-b border-white/10 bg-slate-950 px-6 pb-6 pt-20 md:hidden"
        >
            <nav class="flex flex-col gap-1">
                <RouterLink
                    to="/"
                    class="rounded-xl px-4 py-3 text-sm font-medium text-slate-300 transition hover:bg-white/5 hover:text-white"
                    active-class="bg-white/10 text-white"
                    @click="mobileOpen = false"
                >
                    {{ t('nav.home') }}
                </RouterLink>
                <RouterLink
                    to="/courses"
                    class="rounded-xl px-4 py-3 text-sm font-medium text-slate-300 transition hover:bg-white/5 hover:text-white"
                    active-class="bg-white/10 text-white"
                    @click="mobileOpen = false"
                >
                    {{ t('nav.courses') }}
                </RouterLink>
                <RouterLink
                    to="/about"
                    class="rounded-xl px-4 py-3 text-sm font-medium text-slate-300 transition hover:bg-white/5 hover:text-white"
                    active-class="bg-white/10 text-white"
                    @click="mobileOpen = false"
                >
                    {{ t('nav.about') }}
                </RouterLink>
                <RouterLink
                    to="/contact"
                    class="rounded-xl px-4 py-3 text-sm font-medium text-slate-300 transition hover:bg-white/5 hover:text-white"
                    active-class="bg-white/10 text-white"
                    @click="mobileOpen = false"
                >
                    {{ t('nav.contact') }}
                </RouterLink>
                <RouterLink
                    v-if="user && user.role !== 2"
                    to="/dashboard"
                    class="rounded-xl px-4 py-3 text-sm font-medium text-slate-300 transition hover:bg-white/5 hover:text-white"
                    active-class="bg-white/10 text-white"
                    @click="mobileOpen = false"
                >
                    {{ t('nav.myLearning') }}
                </RouterLink>
                <RouterLink
                    v-if="user && user.role === 2"
                    to="/my-courses"
                    class="rounded-xl px-4 py-3 text-sm font-medium text-slate-300 transition hover:bg-white/5 hover:text-white"
                    active-class="bg-white/10 text-white"
                    @click="mobileOpen = false"
                >
                    My Courses
                </RouterLink>
            </nav>

            <div class="mt-4 border-t border-white/10 pt-4">
                <template v-if="user">
                    <RouterLink
                        to="/profile"
                        class="flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-white transition hover:bg-white/10 sm:flex"
                        @click="mobileOpen = false"
                    >
                        <img
                            v-if="user?.profile?.avatar"
                            :src="user.profile.avatar"
                            alt="Avatar"
                            class="h-7 w-7 rounded-full object-cover"
                        />
                        <span v-else class="grid h-7 w-7 place-items-center rounded-full bg-blue-600 text-xs font-bold">
                            {{ user?.name?.charAt(0)?.toUpperCase() ?? '?' }}
                        </span>
                        {{ user?.name }}
                    </RouterLink>
                    <button
                        @click="mobileOpen = false; handleLogout()"
                        class="mt-1 w-full rounded-xl px-4 py-3 text-left text-sm text-slate-400 transition hover:bg-white/5 hover:text-white"
                    >
                        {{ t('nav.signOut') }}
                    </button>
                </template>
                <template v-else>
                    <RouterLink
                        to="/login"
                        class="block rounded-xl px-4 py-3 text-sm text-slate-300 transition hover:bg-white/5 hover:text-white"
                        @click="mobileOpen = false"
                    >
                        {{ t('nav.signIn') }}
                    </RouterLink>
                    <RouterLink
                        to="/register"
                        class="mt-2 block rounded-xl bg-blue-600 px-4 py-3 text-center text-sm font-semibold text-white transition hover:bg-blue-700"
                        @click="mobileOpen = false"
                    >
                        {{ t('nav.getStarted') }}
                    </RouterLink>
                </template>
            </div>
        </div>
    </Transition>

    <header class="sticky top-0 z-50 border-b border-white/10 bg-slate-950/90 backdrop-blur-sm">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6">
            <!-- Logo -->
            <RouterLink to="/" class="flex items-center gap-3">
                <div class="grid h-9 w-9 place-items-center rounded-xl bg-gradient-to-br from-blue-500 to-cyan-400 font-black text-slate-950 text-sm">
                    CL
                </div>
                <span class="font-semibold text-white">Cloudy Learning</span>
            </RouterLink>

            <!-- Desktop Nav links -->
            <nav class="hidden items-center gap-6 md:flex">
                <RouterLink
                    to="/"
                    class="text-sm text-slate-400 transition hover:text-white"
                    active-class="text-white"
                    :exact="true"
                >
                    {{ t('nav.home') }}
                </RouterLink>
                <RouterLink
                    to="/courses"
                    class="text-sm text-slate-400 transition hover:text-white"
                    active-class="text-white"
                >
                    {{ t('nav.courses') }}
                </RouterLink>
                <RouterLink
                    to="/about"
                    class="text-sm text-slate-400 transition hover:text-white"
                    active-class="text-white"
                >
                    {{ t('nav.about') }}
                </RouterLink>
                <RouterLink
                    to="/contact"
                    class="text-sm text-slate-400 transition hover:text-white"
                    active-class="text-white"
                >
                    {{ t('nav.contact') }}
                </RouterLink>
                <RouterLink
                    v-if="user && user.role !== 2"
                    to="/dashboard"
                    class="text-sm text-slate-400 transition hover:text-white"
                    active-class="text-white"
                >
                    {{ t('nav.myLearning') }}
                </RouterLink>
                <RouterLink
                    v-if="user && user.role === 2"
                    to="/my-courses"
                    class="text-sm text-slate-400 transition hover:text-white"
                    active-class="text-white"
                >
                    My Courses
                </RouterLink>
            </nav>

            <!-- Auth actions -->
            <div class="flex items-center gap-2 sm:gap-3">
                <LanguageSwitcher />
                <template v-if="!resolved">
                    <span class="h-8 w-20 animate-pulse rounded-xl bg-white/5" />
                </template>
                <template v-else-if="user">
                    <!-- Notification bell -->
                    <div class="relative" ref="bellRef">
                        <button
                            @click="toggleBell"
                            class="relative grid h-9 w-9 place-items-center rounded-xl border border-white/10 bg-white/5 text-slate-400 transition hover:bg-white/10 hover:text-white"
                            aria-label="Notifications"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                            </svg>
                            <span v-if="unreadCount > 0" class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white leading-none">
                                {{ unreadCount > 9 ? '9+' : unreadCount }}
                            </span>
                        </button>

                        <!-- Dropdown -->
                        <div
                            v-if="bellOpen"
                            class="absolute right-0 top-12 z-50 w-80 max-w-[calc(100vw-2rem)] rounded-2xl border border-white/10 bg-slate-900 shadow-2xl"
                        >
                            <div class="flex items-center justify-between border-b border-white/10 px-4 py-3">
                                <span class="text-sm font-semibold text-white">{{ t('nav.notifications') }}</span>
                                <button v-if="unreadCount > 0" @click="markAllRead" class="text-xs text-sky-400 hover:underline">{{ t('nav.markAllRead') }}</button>
                            </div>
                            <ul class="max-h-80 overflow-y-auto divide-y divide-white/5">
                                <li v-if="notifications.length === 0" class="px-4 py-6 text-center text-sm text-slate-500">
                                    {{ t('nav.noNotifications') }}
                                </li>
                                <li
                                    v-for="n in notifications"
                                    :key="n.id"
                                    @click="markRead(n)"
                                    class="cursor-pointer px-4 py-3 transition hover:bg-white/5"
                                    :class="{ 'opacity-50': n.is_read }"
                                >
                                    <div class="flex items-start gap-3">
                                        <span class="mt-0.5 h-2 w-2 shrink-0 rounded-full" :class="n.is_read ? 'bg-slate-600' : 'bg-sky-400'" />
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-white truncate">{{ n.title }}</p>
                                            <p class="mt-0.5 text-xs text-slate-400 leading-relaxed line-clamp-2">{{ n.body }}</p>
                                            <p class="mt-1 text-[11px] text-slate-600">{{ formatDate(n.created_at) }}</p>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <RouterLink
                        to="/profile"
                        class="hidden items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-white transition hover:bg-white/10 sm:flex"
                    >
                        <img
                            v-if="user?.profile?.avatar"
                            :src="user.profile.avatar"
                            alt="Avatar"
                            class="h-6 w-6 rounded-full object-cover"
                        />
                        <span v-else class="grid h-6 w-6 place-items-center rounded-full bg-blue-600 text-xs font-bold">
                            {{ user?.name?.charAt(0)?.toUpperCase() ?? '?' }}
                        </span>
                        <span class="hidden sm:inline">{{ user?.name }}</span>
                    </RouterLink>
                    <button
                        @click="handleLogout"
                        class="hidden rounded-xl border border-white/10 px-4 py-2 text-sm text-slate-400 transition hover:text-white md:block"
                    >
                        {{ t('nav.signOut') }}
                    </button>
                </template>
                <template v-else>
                    <RouterLink
                        to="/login"
                        class="hidden text-sm text-slate-400 transition hover:text-white md:block"
                    >
                        {{ t('nav.signIn') }}
                    </RouterLink>
                    <RouterLink
                        to="/register"
                        class="hidden rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700 md:block"
                    >
                        {{ t('nav.getStarted') }}
                    </RouterLink>
                </template>

                <!-- Hamburger button (mobile only) -->
                <button
                    @click="mobileOpen = !mobileOpen"
                    class="grid h-9 w-9 place-items-center rounded-xl border border-white/10 bg-white/5 text-slate-400 transition hover:bg-white/10 hover:text-white md:hidden"
                    :aria-label="mobileOpen ? 'Close menu' : 'Open menu'"
                    :aria-expanded="mobileOpen"
                >
                    <svg v-if="!mobileOpen" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </header>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { useI18n } from 'vue-i18n';
import axios from 'axios';
import { useAuth } from '../composables/useAuth';
import { useRouter } from 'vue-router';
import LanguageSwitcher from './LanguageSwitcher.vue';

const { t } = useI18n();
const { user, resolved, logout } = useAuth();
const router = useRouter();

// ── Mobile menu ─────────────────────────────────────────
const mobileOpen = ref(false);

// ── Bell / Notifications ────────────────────────────────
const bellOpen      = ref(false);
const bellRef       = ref<HTMLElement | null>(null);
const notifications = ref<any[]>([]);
const unreadCount   = ref(0);

const fetchNotifications = async () => {
    const [listRes, countRes] = await Promise.all([
        axios.get('/api/notifications'),
        axios.get('/api/notifications/unread-count'),
    ]);
    notifications.value = listRes.data;
    unreadCount.value   = countRes.data.count;
};

const toggleBell = () => {
    bellOpen.value = !bellOpen.value;
    if (bellOpen.value) fetchNotifications();
};

const markRead = async (n: any) => {
    if (!n.is_read) {
        await axios.put(`/api/notifications/${n.id}/read`);
        n.is_read = true;
        unreadCount.value = Math.max(0, unreadCount.value - 1);
    }
};

const markAllRead = async () => {
    await axios.put('/api/notifications/read-all');
    notifications.value.forEach((n) => (n.is_read = true));
    unreadCount.value = 0;
};

const formatDate = (iso: string) => {
    return new Date(iso).toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const onClickOutside = (e: MouseEvent) => {
    if (bellRef.value && !bellRef.value.contains(e.target as Node)) {
        bellOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', onClickOutside);
    if (user.value) fetchNotifications();
});
onBeforeUnmount(() => document.removeEventListener('click', onClickOutside));

// ── Auth ────────────────────────────────────────────────
const loggingOut = ref(false);
const logoutSuccess = ref(false);

const handleLogout = async () => {
    loggingOut.value = true;
    logoutSuccess.value = false;
    await logout();
    logoutSuccess.value = true;
    setTimeout(() => {
        loggingOut.value = false;
        logoutSuccess.value = false;
        router.push('/');
    }, 1200);
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.slide-down-enter-active,
.slide-down-leave-active {
    transition: transform 0.25s ease, opacity 0.25s ease;
}
.slide-down-enter-from,
.slide-down-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}
</style>
