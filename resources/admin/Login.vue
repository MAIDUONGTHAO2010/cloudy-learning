<template>
	<section class="relative flex min-h-screen overflow-hidden bg-slate-950 text-white">
		<div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(59,130,246,0.35),transparent_30%),radial-gradient(circle_at_bottom_right,rgba(16,185,129,0.22),transparent_28%)]"></div>

		<div class="relative grid min-h-screen w-full lg:grid-cols-[1.1fr_0.9fr]">
			<div class="hidden border-r border-white/10 lg:flex lg:flex-col lg:justify-between lg:px-14 lg:py-12">
				<div>
					<div class="flex items-center gap-3">
						<div class="grid h-12 w-12 place-items-center rounded-2xl bg-linear-to-br from-blue-500 to-cyan-400 font-black text-slate-950">
							CL
						</div>
						<div>
							<p class="text-xs uppercase tracking-[0.35em] text-slate-400">Cloudy Learning</p>
							<h1 class="mt-1 text-2xl font-semibold text-white">Admin access</h1>
						</div>
					</div>

					<div class="mt-16 max-w-xl">
						<p class="text-sm font-semibold uppercase tracking-[0.35em] text-blue-300">Operations hub</p>
						<h2 class="mt-5 text-5xl font-semibold leading-tight text-white">
							Control courses, users, and publishing from one focused workspace.
						</h2>
						<p class="mt-6 text-base leading-7 text-slate-300">
							Sign in to review lesson growth, moderate learner activity, and keep the content pipeline moving.
						</p>
					</div>
				</div>

				<div class="grid gap-4 md:grid-cols-3">
					<article
						v-for="highlight in highlights"
						:key="highlight.label"
						class="rounded-3xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm"
					>
						<p class="text-xs uppercase tracking-[0.25em] text-slate-400">{{ highlight.label }}</p>
						<p class="mt-3 text-3xl font-semibold text-white">{{ highlight.value }}</p>
						<p class="mt-2 text-sm text-slate-400">{{ highlight.description }}</p>
					</article>
				</div>
			</div>

			<div class="relative flex items-center justify-center px-5 py-10 sm:px-8 lg:px-12">
				<div class="w-full max-w-xl rounded-[2rem] border border-white/10 bg-white/95 p-8 text-slate-900 shadow-[0_40px_120px_-50px_rgba(15,23,42,0.8)] backdrop-blur xl:p-10">
					<div class="flex items-center justify-between gap-4">
						<div>
							<p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Welcome back</p>
							<h2 class="mt-3 text-3xl font-semibold text-slate-950">Sign in to admin</h2>
						</div>
						<RouterLink
							to="/"
							class="hidden rounded-full border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 transition hover:border-slate-300 hover:text-slate-900 sm:inline-flex"
						>
							Back to dashboard
						</RouterLink>
					</div>

					<p class="mt-4 text-sm leading-6 text-slate-500">
						Use your administrator credentials to access reporting, learner management, and content controls.
					</p>

					<!-- <div
						v-if="errorMessage"
						class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-600"
					>
						{{ errorMessage }}
					</div> -->

					<form class="mt-8 space-y-5" @submit.prevent="handleSubmit">
						<label class="block">
							<span class="mb-2 block text-sm font-medium text-slate-700">Email address</span>
							<input
								v-model="form.email"
								type="email"
								autocomplete="email"
								placeholder="admin@cloudylearning.com"
								class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10"
							/>
							<p v-if="fieldErrors.email.length" class="mt-2 text-sm text-rose-600">{{ fieldErrors.email[0] }}</p>
						</label>

						<label class="block">
							<span class="mb-2 block text-sm font-medium text-slate-700">Password</span>
							<input
								v-model="form.password"
								type="password"
								autocomplete="current-password"
								placeholder="Enter your password"
								class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10"
							/>
							<p v-if="fieldErrors.password.length" class="mt-2 text-sm text-rose-600">{{ fieldErrors.password[0] }}</p>
						</label>

						<div class="flex items-center justify-between gap-4">
							<label class="flex items-center gap-3 text-sm text-slate-600">
								<input v-model="form.remember" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
								Remember this device
							</label>
							<a href="#" class="text-sm font-medium text-blue-600 transition hover:text-blue-500">Forgot password?</a>
						</div>

						<button
							type="submit"
							:disabled="isSubmitting"
							class="inline-flex w-full items-center justify-center rounded-2xl bg-slate-950 px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-70"
						>
							{{ isSubmitting ? 'Signing in...' : 'Sign in' }}
						</button>
					</form>

					<div class="mt-8 rounded-3xl bg-slate-50 p-5">
						<p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Demo access</p>
						<div class="mt-4 flex flex-wrap gap-3">
							<button
								v-for="preset in presets"
								:key="preset.email"
								type="button"
								class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 transition hover:border-slate-300 hover:text-slate-900"
								@click="applyPreset(preset)"
							>
								{{ preset.label }}
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
</template>

<script setup lang="ts">
import axios from 'axios';
import { reactive, ref } from 'vue';
import { useAdminUser } from './composables/useAdminUser.js';

const { setAdminUser } = useAdminUser();

const form = reactive({
	email: '',
	password: '',
	remember: true,
});

const isSubmitting = ref(false);
const errorMessage = ref('');
const fieldErrors = reactive<{ email: string[]; password: string[] }>({
	email: [],
	password: [],
});

const highlights = [
	{ label: 'Live learners', value: '856', description: 'Currently active across premium programs.' },
	{ label: 'New lessons', value: '42', description: 'Queued for review in the next publishing cycle.' },
	{ label: 'Completion rate', value: '92%', description: 'Average completion across the current catalog.' },
];

const presets = [
	{ label: 'Content admin', email: 'content@cloudylearning.com', password: 'Aa@123456789' },
	{ label: 'Support lead', email: 'support@cloudylearning.com', password: 'password' },
];

const applyPreset = (preset: { email: string; password: string }) => {
	form.email = preset.email;
	form.password = preset.password;
	form.remember = true;
	errorMessage.value = '';
	fieldErrors.email = [];
	fieldErrors.password = [];
};

const handleSubmit = async () => {
	isSubmitting.value = true;
	errorMessage.value = '';
	fieldErrors.email = [];
	fieldErrors.password = [];

	try {
		const response = await axios.post('/admin/login', {
			email: form.email,
			password: form.password,
			remember: form.remember,
		});

		if (response.data.user?.name && response.data.user?.email) {
			setAdminUser({
				name: response.data.user.name,
				email: response.data.user.email,
			});
		}

		window.location.assign(response.data.redirect ?? '/admin/dashboard');
	} catch (error) {
		if (axios.isAxiosError(error)) {
			const responseErrors = error.response?.data?.errors;
			fieldErrors.email = Array.isArray(responseErrors?.email) ? responseErrors.email : [];
			fieldErrors.password = Array.isArray(responseErrors?.password) ? responseErrors.password : [];
			errorMessage.value = error.response?.data?.message ?? 'Unable to sign in with the provided credentials.';
		} else {
			errorMessage.value = 'Unable to sign in with the provided credentials.';
		}
	} finally {
		isSubmitting.value = false;
	}
};
</script>
