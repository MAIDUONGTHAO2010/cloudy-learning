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
              <h1 class="mt-1 text-2xl font-semibold text-white">Start for free</h1>
            </div>
          </RouterLink>

          <div class="mt-16 max-w-xl">
            <p class="text-sm font-semibold uppercase tracking-[0.35em] text-emerald-400">Join today</p>
            <h2 class="mt-5 text-5xl font-semibold leading-tight text-white">
              Learn from experts. Build real skills. Grow your career.
            </h2>
            <p class="mt-6 text-base leading-7 text-slate-300">
              Create a free account and get instant access to hundreds of courses across web development, data science, design, and more.
            </p>
          </div>
        </div>

        <ul class="space-y-4">
          <li v-for="feature in features" :key="feature" class="flex items-center gap-3">
            <div class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-emerald-500/20 text-emerald-400">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
              </svg>
            </div>
            <span class="text-sm text-slate-300">{{ feature }}</span>
          </li>
        </ul>
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
            <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Get started</p>
            <h2 class="mt-3 text-3xl font-semibold text-slate-950">Create an account</h2>
          </div>

          <form @submit.prevent="submit" class="mt-8 space-y-5">
            <div>
              <label class="mb-1.5 block text-sm font-medium text-slate-700">Full name</label>
              <input
                v-model="form.name"
                type="text"
                autocomplete="name"
                required
                placeholder="Nguyen Van A"
                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                :class="{ 'border-rose-400 focus:border-rose-400 focus:ring-rose-100': errors.name }"
              />
              <p v-if="errors.name" class="mt-1.5 text-xs text-rose-500">{{ errors.name }}</p>
            </div>

            <div>
              <label class="mb-1.5 block text-sm font-medium text-slate-700">Email address</label>
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
              <label class="mb-1.5 block text-sm font-medium text-slate-700">Password</label>
              <input
                v-model="form.password"
                type="password"
                autocomplete="new-password"
                required
                placeholder="At least 8 characters"
                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                :class="{ 'border-rose-400 focus:border-rose-400 focus:ring-rose-100': errors.password }"
              />
              <p v-if="errors.password" class="mt-1.5 text-xs text-rose-500">{{ errors.password }}</p>
            </div>

            <div>
              <label class="mb-1.5 block text-sm font-medium text-slate-700">Confirm password</label>
              <input
                v-model="form.password_confirmation"
                type="password"
                autocomplete="new-password"
                required
                placeholder="Repeat your password"
                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
              />
            </div>

            <!-- Role selector -->
            <div>
              <label class="mb-2 block text-sm font-medium text-slate-700">I want to join as</label>
              <div class="grid grid-cols-2 gap-3">
                <label
                  :class="form.role === 1
                    ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200'
                    : 'border-slate-200 hover:border-slate-300'"
                  class="flex cursor-pointer flex-col items-center gap-2 rounded-2xl border p-4 transition"
                >
                  <input type="radio" v-model.number="form.role" :value="1" class="sr-only" />
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" :class="form.role === 1 ? 'text-blue-600' : 'text-slate-400'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 14c3.866 0 7 1.343 7 3v1H5v-1c0-1.657 3.134-3 7-3zm0 0a4 4 0 100-8 4 4 0 000 8z" />
                  </svg>
                  <span class="text-sm font-semibold" :class="form.role === 1 ? 'text-blue-700' : 'text-slate-600'">Student</span>
                  <span class="text-xs text-center" :class="form.role === 1 ? 'text-blue-500' : 'text-slate-400'">Browse &amp; learn courses</span>
                </label>

                <label
                  :class="form.role === 2
                    ? 'border-emerald-500 bg-emerald-50 ring-2 ring-emerald-200'
                    : 'border-slate-200 hover:border-slate-300'"
                  class="flex cursor-pointer flex-col items-center gap-2 rounded-2xl border p-4 transition"
                >
                  <input type="radio" v-model.number="form.role" :value="2" class="sr-only" />
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" :class="form.role === 2 ? 'text-emerald-600' : 'text-slate-400'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                  </svg>
                  <span class="text-sm font-semibold" :class="form.role === 2 ? 'text-emerald-700' : 'text-slate-600'">Instructor</span>
                  <span class="text-xs text-center" :class="form.role === 2 ? 'text-emerald-500' : 'text-slate-400'">Create &amp; teach courses</span>
                </label>
              </div>
              <p v-if="errors.role" class="mt-1.5 text-xs text-rose-500">{{ errors.role }}</p>
            </div>

            <!-- Date of birth + Sex -->
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Date of birth</label>
                <input
                  v-model="form.date_of_birth"
                  type="date"
                  class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                  :class="{ 'border-rose-400': errors.date_of_birth }"
                />
                <p v-if="errors.date_of_birth" class="mt-1 text-xs text-rose-500">{{ errors.date_of_birth }}</p>
              </div>
              <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Gender</label>
                <select
                  v-model="form.sex"
                  class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                >
                  <option value="">Prefer not to say</option>
                  <option value="0">Male</option>
                  <option value="1">Female</option>
                  <option value="2">Other</option>
                </select>
              </div>
            </div>

            <!-- Categories of interest -->
            <div>
              <label class="mb-2 block text-sm font-medium text-slate-700">
                Interests <span class="text-slate-400 font-normal">(choose up to 3)</span>
              </label>
              <div v-if="loadingCategories" class="text-xs text-slate-400">Loading categories…</div>
              <div v-else class="flex flex-wrap gap-2">
                <button
                  v-for="cat in categories"
                  :key="cat.id"
                  type="button"
                  @click="toggleCategory(cat.id)"
                  :class="form.categories.includes(cat.id)
                    ? 'bg-blue-600 text-white border-blue-600'
                    : 'border-slate-200 text-slate-600 hover:border-blue-300'"
                  class="rounded-full border px-3 py-1 text-xs font-medium transition"
                >
                  {{ cat.name }}
                </button>
              </div>
              <p v-if="errors.categories" class="mt-1 text-xs text-rose-500">{{ errors.categories }}</p>
            </div>

            <div v-if="generalError" class="rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-600">
              {{ generalError }}
            </div>

            <button
              type="submit"
              :disabled="loading"
              class="w-full rounded-2xl bg-slate-900 py-3.5 text-sm font-semibold text-white transition hover:bg-slate-700 disabled:opacity-50"
            >
              {{ loading ? 'Creating account…' : 'Create account' }}
            </button>
          </form>

          <p class="mt-6 text-center text-sm text-slate-500">
            Already have an account?
            <RouterLink to="/login" class="font-semibold text-blue-600 hover:text-blue-700">
              Sign in
            </RouterLink>
          </p>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import { useAuth } from '../composables/useAuth';

const router = useRouter();
const { setUser } = useAuth();

const form = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 1 as 1 | 2,
    date_of_birth: '',
    sex: '' as '' | '0' | '1' | '2',
    categories: [] as number[],
});
const errors = reactive<Record<string, string>>({});
const generalError = ref('');
const loading = ref(false);

const categories = ref<{ id: number; name: string }[]>([]);
const loadingCategories = ref(true);

const toggleCategory = (id: number) => {
    const idx = form.categories.indexOf(id);
    if (idx !== -1) {
        form.categories.splice(idx, 1);
    } else if (form.categories.length < 3) {
        form.categories.push(id);
    }
};

const features = [
    'Free access to hundreds of beginner courses',
    'Track your progress across all enrolled courses',
    'Certificates on course completion',
    'Learn at your own pace, on any device',
];

onMounted(() => {
    axios.get<{ id: number; name: string }[]>('/api/categories')
        .then(({ data }) => { categories.value = data; })
        .finally(() => { loadingCategories.value = false; });
});

const submit = async () => {
    loading.value = true;
    generalError.value = '';
    Object.keys(errors).forEach((k) => delete errors[k]);

    try {
        const payload: Record<string, any> = { ...form };
        if (!payload.date_of_birth) delete payload.date_of_birth;
        if (payload.sex === '') delete payload.sex;
        if (!payload.categories.length) delete payload.categories;

        const { data } = await axios.post('/auth/register', payload);
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
