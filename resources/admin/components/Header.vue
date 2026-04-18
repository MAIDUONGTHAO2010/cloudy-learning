<template>
	<header class="border-b border-slate-200/80 bg-white/80 backdrop-blur">
		<div class="flex flex-col gap-5 px-5 py-5 sm:px-8 lg:px-10">
			<div class="flex items-center justify-between gap-4">
				<div>
					<p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">{{ t('admin.header.operations') }}</p>
					<h2 class="mt-1 text-2xl font-semibold text-slate-900">{{ t('admin.header.title') }}</h2>
				</div>

				<div class="flex items-center gap-3">
					<!-- <div v-if="user" class="hidden rounded-2xl border border-slate-200 bg-white px-4 py-2 text-right shadow-sm lg:block">
						<p class="text-xs uppercase tracking-[0.25em] text-slate-400">Signed in as</p>
						<p class="text-sm font-medium text-slate-700">{{ user.name }}</p>
						<p class="text-xs text-slate-500">{{ user.email }}</p>
					</div> -->
					<div class="hidden rounded-2xl border border-slate-200 bg-white px-4 py-2 text-right shadow-sm sm:block">
						<p class="text-xs uppercase tracking-[0.25em] text-slate-400">{{ t('admin.header.today') }}</p>
						<p class="text-sm font-medium text-slate-700">{{ todayLabel }}</p>
					</div>

					<LanguageSwitcher />
					<div class="relative" ref="bellRef">
						<button
							type="button"
							@click="toggleBell"
							class="relative flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:bg-slate-50 hover:text-slate-900"
							aria-label="Notifications"
						>
							<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
							</svg>
							<span v-if="unreadCount > 0" class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white leading-none">
								{{ unreadCount > 9 ? '9+' : unreadCount }}
							</span>
						</button>

						<!-- Dropdown -->
						<div
							v-if="bellOpen"
							class="absolute right-0 top-14 z-30 w-80 rounded-2xl border border-slate-200 bg-white shadow-xl"
						>
							<div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
								<span class="text-sm font-semibold text-slate-800">{{ t('admin.header.notifications') }}</span>
								<button v-if="unreadCount > 0" @click="markAllRead" class="text-xs text-blue-600 hover:underline">{{ t('admin.header.markAllRead') }}</button>
							</div>
							<ul class="max-h-80 overflow-y-auto divide-y divide-slate-100">
								<li v-if="notifications.length === 0" class="px-4 py-6 text-center text-sm text-slate-400">
									{{ t('admin.header.noNotifications') }}
								</li>
								<li
									v-for="n in notifications"
									:key="n.id"
									@click="markRead(n)"
									class="cursor-pointer px-4 py-3 transition hover:bg-slate-50"
									:class="{ 'opacity-50': n.is_read }"
								>
									<div class="flex items-start gap-3">
										<span class="mt-1.5 h-2 w-2 shrink-0 rounded-full" :class="n.is_read ? 'bg-slate-300' : 'bg-blue-500'" />
										<div class="min-w-0">
											<p class="text-sm font-medium text-slate-800 truncate">{{ n.title }}</p>
											<p class="mt-0.5 text-xs text-slate-500 leading-relaxed line-clamp-2">{{ n.body }}</p>
											<p class="mt-1 text-[11px] text-slate-400">{{ formatDate(n.created_at) }}</p>
										</div>
									</div>
								</li>
							</ul>
						</div>
					</div>

					<div class="relative" data-admin-user-menu>
						<button
							type="button"
							class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-900 font-semibold text-white transition hover:bg-slate-800"
							@click.stop="toggleMenu"
						>
							{{ userInitials }}
						</button>

						<div
							v-if="isMenuOpen"
							class="absolute right-0 top-14 z-20 w-56 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl"
						>
							<div v-if="user" class="rounded-xl px-3 py-2">
								<p class="text-sm font-medium text-slate-900">{{ user.name }}</p>
								<p class="text-xs text-slate-500">{{ user.email }}</p>
							</div>
							<button
								type="button"
								class="flex w-full items-center rounded-xl px-3 py-2 text-left text-sm font-medium text-rose-600 transition hover:bg-rose-50 disabled:cursor-not-allowed disabled:opacity-60"
								:disabled="isLoggingOut"
								@click="handleLogout"
							>
								{{ isLoggingOut ? t('adminAuth.loggingOut') : t('adminAuth.logout') }}
							</button>
						</div>
					</div>
				</div>
			</div>

			<nav class="flex gap-2 overflow-x-auto md:hidden">
				<RouterLink
					v-for="item in navItems"
					:key="`mobile-${item.to}`"
					:to="item.to"
					class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 transition"
					active-class="border-blue-600 bg-blue-600 text-white"
				>
					{{ item.label }}
				</RouterLink>
			</nav>
		</div>
	</header>
</template>

<script setup lang="ts">
import axios from 'axios';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useAdminUser } from '../composables/useAdminUser.js';
import LanguageSwitcher from './LanguageSwitcher.vue';

const { t } = useI18n();

type NavItem = {
	to: string;
	label: string;
	short: string;
};

type AdminUser = {
	name: string;
	email: string;
};

const props = defineProps<{
	navItems: NavItem[];
	todayLabel: string;
    user: AdminUser | null;
}>();

const { clearAdminUser } = useAdminUser();
const isMenuOpen = ref(false);
const isLoggingOut = ref(false);

// ── Notifications ────────────────────────────────────────
const bellOpen      = ref(false);
const bellRef       = ref<HTMLElement | null>(null);
const notifications = ref<any[]>([]);
const unreadCount   = ref(0);

const fetchNotifications = async () => {
	const [listRes, countRes] = await Promise.all([
		axios.get('/admin/api/notifications'),
		axios.get('/admin/api/notifications/unread-count'),
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
		await axios.put(`/admin/api/notifications/${n.id}/read`);
		n.is_read = true;
		unreadCount.value = Math.max(0, unreadCount.value - 1);
	}
};

const markAllRead = async () => {
	await axios.put('/admin/api/notifications/read-all');
	notifications.value.forEach((n) => (n.is_read = true));
	unreadCount.value = 0;
};

const formatDate = (iso: string) => {
	return new Date(iso).toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};
// ─────────────────────────────────────────────────────────

const handleDocumentClick = (event: MouseEvent) => {
	const target = event.target;

	if (!(target instanceof HTMLElement)) return;

	if (bellRef.value && bellRef.value.contains(target)) return;
	bellOpen.value = false;

	if (target.closest('[data-admin-user-menu]')) return;
	isMenuOpen.value = false;
};

const toggleMenu = () => {
	isMenuOpen.value = !isMenuOpen.value;
};

const handleLogout = async () => {
	isLoggingOut.value = true;

	try {
		const response = await axios.post('/admin/logout');
		clearAdminUser();
		isMenuOpen.value = false;
		window.location.assign(response.data.redirect ?? '/admin/login');
	} finally {
		isLoggingOut.value = false;
	}
};

onMounted(() => {
	document.addEventListener('click', handleDocumentClick);
	// Only fetch when the admin user is authenticated
	if (props.user) fetchNotifications();
});

onBeforeUnmount(() => {
	document.removeEventListener('click', handleDocumentClick);
});

const userInitials = computed(() => {
	if (!props.user?.name) {
		return 'AD';
	}

	return props.user.name
		.split(' ')
		.filter(Boolean)
		.slice(0, 2)
		.map((part) => part[0]?.toUpperCase() ?? '')
		.join('');
});
</script>
