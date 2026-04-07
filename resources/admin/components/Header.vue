<template>
	<header class="border-b border-slate-200/80 bg-white/80 backdrop-blur">
		<div class="flex flex-col gap-5 px-5 py-5 sm:px-8 lg:px-10">
			<div class="flex items-center justify-between gap-4">
				<div>
					<p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Operations</p>
					<h2 class="mt-1 text-2xl font-semibold text-slate-900">E-learning administration</h2>
				</div>

				<div class="flex items-center gap-3">
					<!-- <div v-if="user" class="hidden rounded-2xl border border-slate-200 bg-white px-4 py-2 text-right shadow-sm lg:block">
						<p class="text-xs uppercase tracking-[0.25em] text-slate-400">Signed in as</p>
						<p class="text-sm font-medium text-slate-700">{{ user.name }}</p>
						<p class="text-xs text-slate-500">{{ user.email }}</p>
					</div> -->
					<div class="hidden rounded-2xl border border-slate-200 bg-white px-4 py-2 text-right shadow-sm sm:block">
						<p class="text-xs uppercase tracking-[0.25em] text-slate-400">Today</p>
						<p class="text-sm font-medium text-slate-700">{{ todayLabel }}</p>
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
								{{ isLoggingOut ? 'Logging out...' : 'Logout' }}
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
import { useRouter } from 'vue-router';
import { useAdminUser } from '../composables/useAdminUser.js';

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

const router = useRouter();
const { clearAdminUser } = useAdminUser();
const isMenuOpen = ref(false);
const isLoggingOut = ref(false);

const handleDocumentClick = (event: MouseEvent) => {
	const target = event.target;

	if (!(target instanceof HTMLElement) || target.closest('[data-admin-user-menu]')) {
		return;
	}

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
