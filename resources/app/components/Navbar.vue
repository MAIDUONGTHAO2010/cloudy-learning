<template>
    <header class="sticky top-0 z-50 border-b border-white/10 bg-slate-950/90 backdrop-blur-sm">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
            <!-- Logo -->
            <RouterLink to="/" class="flex items-center gap-3">
                <div class="grid h-9 w-9 place-items-center rounded-xl bg-gradient-to-br from-blue-500 to-cyan-400 font-black text-slate-950 text-sm">
                    CL
                </div>
                <span class="font-semibold text-white">Cloudy Learning</span>
            </RouterLink>

            <!-- Nav links -->
            <nav class="hidden items-center gap-6 md:flex">
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
                    v-if="user"
                    to="/dashboard"
                    class="text-sm text-slate-400 transition hover:text-white"
                    active-class="text-white"
                >
                    {{ t('nav.myLearning') }}
                </RouterLink>
            </nav>

            <!-- Auth actions -->
            <div class="flex items-center gap-3">
                <LanguageSwitcher />
                <template v-if="!resolved">
                    <!-- Resolving auth state -->
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
                            class="absolute right-0 top-12 z-50 w-80 rounded-2xl border border-white/10 bg-slate-900 shadow-2xl"
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
                        class="flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-white transition hover:bg-white/10"
                    >
                        <span class="grid h-6 w-6 place-items-center rounded-full bg-blue-600 text-xs font-bold">
                            {{ user?.name?.charAt(0)?.toUpperCase() ?? '?' }}
                        </span>
                        <span class="hidden sm:inline">{{ user?.name }}</span>
                    </RouterLink>
                    <button
                        @click="handleLogout"
                        class="rounded-xl border border-white/10 px-4 py-2 text-sm text-slate-400 transition hover:text-white"
                    >
                        {{ t('nav.signOut') }}
                    </button>
                </template>
                <template v-else>
                    <RouterLink
                        to="/login"
                        class="text-sm text-slate-400 transition hover:text-white"
                    >
                        {{ t('nav.signIn') }}
                    </RouterLink>
                    <RouterLink
                        to="/register"
                        class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700"
                    >
                        {{ t('nav.getStarted') }}
                    </RouterLink>
                </template>
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
const handleLogout = async () => {
    await logout();
    router.push('/');
};
</script>
