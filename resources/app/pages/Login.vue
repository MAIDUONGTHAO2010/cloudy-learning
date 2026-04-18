<template>
  <section class="relative flex min-h-screen overflow-hidden bg-slate-950 text-white">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(59,130,246,0.35),transparent_30%),radial-gradient(circle_at_bottom_right,rgba(16,185,129,0.22),transparent_28%)]"></div>

    <div class="relative grid min-h-screen w-full lg:grid-cols-[1.1fr_0.9fr]">
      <!-- Left panel -->
      <div class="hidden border-r border-white/10 lg:flex lg:flex-col lg:justify-between lg:px-14 lg:py-12">
        <div>
          <RouterLink to="/" class="flex items-center gap-3">
            <div class="grid h-12 w-12 place-items-center rounded-2xl bg-gradient-to-br from-blue-500 to-cyan-400 font-black text-slate-950">
              CL
            </div>
            <div>
              <p class="text-xs uppercase tracking-[0.35em] text-slate-400">Cloudy Learning</p>
              <h1 class="mt-1 text-2xl font-semibold text-white">Sign in to learn</h1>
            </div>
          </RouterLink>

          <div class="mt-16 max-w-xl">
            <p class="text-sm font-semibold uppercase tracking-[0.35em] text-blue-300">Your learning journey</p>
            <h2 class="mt-5 text-5xl font-semibold leading-tight text-white">
              Unlock thousands of courses taught by real experts.
            </h2>
            <p class="mt-6 text-base leading-7 text-slate-300">
              Sign in to access your enrolled courses, track progress, and continue where you left off.
            </p>
          </div>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
          <article
            v-for="stat in stats"
            :key="stat.label"
            class="rounded-3xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm"
          >
            <p class="text-xs uppercase tracking-[0.25em] text-slate-400">{{ stat.label }}</p>
            <p class="mt-3 text-3xl font-semibold text-white">{{ stat.value }}</p>
            <p class="mt-2 text-sm text-slate-400">{{ stat.description }}</p>
          </article>
        </div>
      </div>

      <!-- Right panel: form -->
      <div class="relative flex items-center justify-center px-5 py-10 sm:px-8 lg:px-12">
        <div class="w-full max-w-xl rounded-[2rem] border border-white/10 bg-white/95 p-8 text-slate-900 shadow-[0_40px_120px_-50px_rgba(15,23,42,0.8)] backdrop-blur xl:p-10">

          <!-- Mobile logo -->
          <RouterLink to="/" class="mb-6 flex items-center gap-2 lg:hidden">
            <div class="grid h-9 w-9 place-items-center rounded-xl bg-gradient-to-br from-blue-500 to-cyan-400 font-black text-white text-sm">
              CL
            </div>
            <span class="text-sm font-semibold text-slate-600">Cloudy Learning</span>
          </RouterLink>

          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Welcome back</p>
            <h2 class="mt-3 text-3xl font-semibold text-slate-950">Sign in</h2>
          </div>

          <form @submit.prevent="submit" class="mt-8 space-y-5">
            <div>
              <label class="mb-1.5 block text-sm font-medium text-slate-700">
                Email address
              </label>
              <input
                v-model="form.email"
                type="email"
                autocomplete="email"
                required
                placeholder="you@example.com"
                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                :class="{ 'border-rose-400 focus:border-rose-400 focus:ring-rose-100': errors.email }"
              />
              <p v-if="errors.email" class="mt-1.5 text-xs text-rose-500">{{ errors.email }}</p>
            </div>

            <div>
              <div class="mb-1.5 flex items-center justify-between">
                <label class="text-sm font-medium text-slate-700">Password</label>
              </div>
              <input
                v-model="form.password"
                type="password"
                autocomplete="current-password"
                required
                placeholder="Your password"
                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                :class="{ 'border-rose-400 focus:border-rose-400 focus:ring-rose-100': errors.password }"
              />
              <p v-if="errors.password" class="mt-1.5 text-xs text-rose-500">{{ errors.password }}</p>
            </div>

            <div class="flex items-center gap-2">
              <input
                v-model="form.remember"
                id="remember"
                type="checkbox"
                class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-400"
              />
              <label for="remember" class="text-sm text-slate-600">Remember me</label>
            </div>

            <div v-if="generalError" class="rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-600">
              {{ generalError }}
            </div>

            <button
              type="submit"
              :disabled="loading"
              class="w-full rounded-2xl bg-slate-900 py-3.5 text-sm font-semibold text-white transition hover:bg-slate-700 disabled:opacity-50"
            >
              {{ loading ? 'Signing in…' : 'Sign in' }}
            </button>
          </form>

          <p class="mt-6 text-center text-sm text-slate-500">
            Don't have an account?
            <RouterLink to="/register" class="font-semibold text-blue-600 hover:text-blue-700">
              Create one
            </RouterLink>
          </p>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import { useAuth } from '../composables/useAuth';

const router = useRouter();
const { setUser } = useAuth();

const form = reactive({ email: '', password: '', remember: false });
const errors = reactive<Record<string, string>>({});
const generalError = ref('');
const loading = ref(false);

const stats = [
    { label: 'Courses', value: '500+', description: 'Across all skill levels' },
    { label: 'Learners', value: '12k+', description: 'Active students worldwide' },
    { label: 'Instructors', value: '80+', description: 'Industry professionals' },
];

const submit = async () => {
    loading.value = true;
    generalError.value = '';
    Object.keys(errors).forEach((k) => delete errors[k]);

    try {
        const { data } = await axios.post('/auth/login', form);
        setUser(data.user);
        router.push('/dashboard');
    } catch (err: any) {
        const data = err?.response?.data;
        if (data?.errors) {
            Object.assign(errors, Object.fromEntries(
                Object.entries(data.errors as Record<string, string[]>).map(([k, v]) => [k, v[0]])
            ));
        } else {
            generalError.value = data?.message ?? 'Something went wrong. Please try again.';
        }
    } finally {
        loading.value = false;
    }
};
</script>
