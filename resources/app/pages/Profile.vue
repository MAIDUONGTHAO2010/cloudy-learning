<template>
    <div class="min-h-screen bg-slate-950 text-white">
        <Navbar />

        <main class="mx-auto max-w-2xl px-6 py-10">
            <h1 class="mb-8 text-2xl font-semibold">Profile Settings</h1>

            <!-- Success message -->
            <div
                v-if="successMessage"
                class="mb-6 rounded-xl border border-emerald-500/30 bg-emerald-900/20 px-4 py-3 text-sm text-emerald-400"
            >
                {{ successMessage }}
            </div>

            <!-- General error -->
            <div
                v-if="generalError"
                class="mb-6 rounded-xl border border-red-500/30 bg-red-900/20 px-4 py-3 text-sm text-red-400"
            >
                {{ generalError }}
            </div>

            <form @submit.prevent="submit" class="space-y-5 rounded-2xl border border-white/10 bg-slate-900 p-6">
                <!-- Name -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-300">Full name</label>
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="Your name"
                        class="w-full rounded-xl border bg-slate-800 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition focus:ring-2"
                        :class="errors.name ? 'border-red-500 focus:ring-red-500/30' : 'border-white/10 focus:ring-blue-500/30 focus:border-blue-500/50'"
                    />
                    <p v-if="errors.name" class="mt-1 text-xs text-red-400">{{ errors.name }}</p>
                </div>

                <!-- Email -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-300">Email address</label>
                    <input
                        v-model="form.email"
                        type="email"
                        placeholder="you@example.com"
                        class="w-full rounded-xl border bg-slate-800 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition focus:ring-2"
                        :class="errors.email ? 'border-red-500 focus:ring-red-500/30' : 'border-white/10 focus:ring-blue-500/30 focus:border-blue-500/50'"
                    />
                    <p v-if="errors.email" class="mt-1 text-xs text-red-400">{{ errors.email }}</p>
                </div>

                <hr class="border-white/10" />

                <!-- New password -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-300">
                        New password <span class="text-slate-500 font-normal">(leave blank to keep current)</span>
                    </label>
                    <input
                        v-model="form.password"
                        type="password"
                        placeholder="••••••••"
                        class="w-full rounded-xl border bg-slate-800 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition focus:ring-2"
                        :class="errors.password ? 'border-red-500 focus:ring-red-500/30' : 'border-white/10 focus:ring-blue-500/30 focus:border-blue-500/50'"
                    />
                    <p v-if="errors.password" class="mt-1 text-xs text-red-400">{{ errors.password }}</p>
                </div>

                <!-- Confirm password -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-300">Confirm new password</label>
                    <input
                        v-model="form.password_confirmation"
                        type="password"
                        placeholder="••••••••"
                        class="w-full rounded-xl border bg-slate-800 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition focus:ring-2"
                        :class="errors.password_confirmation ? 'border-red-500 focus:ring-red-500/30' : 'border-white/10 focus:ring-blue-500/30 focus:border-blue-500/50'"
                    />
                    <p v-if="errors.password_confirmation" class="mt-1 text-xs text-red-400">{{ errors.password_confirmation }}</p>
                </div>

                <button
                    type="submit"
                    :disabled="loading"
                    class="w-full rounded-xl bg-blue-600 py-3 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:opacity-60"
                >
                    {{ loading ? 'Saving…' : 'Save changes' }}
                </button>
            </form>
        </main>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';
import { useAuth } from '../composables/useAuth';
import Navbar from '../components/Navbar.vue';

const { user, setUser } = useAuth();

const form = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const errors = reactive<Record<string, string>>({});
const loading = ref(false);
const successMessage = ref('');
const generalError = ref('');

onMounted(() => {
    if (user.value) {
        form.name = user.value.name;
        form.email = user.value.email;
    }
});

const submit = async () => {
    loading.value = true;
    successMessage.value = '';
    generalError.value = '';
    Object.keys(errors).forEach((k) => delete errors[k]);

    try {
        const { data } = await axios.put('/api/profile', form);
        setUser(data.user);
        form.password = '';
        form.password_confirmation = '';
        successMessage.value = data.message;
    } catch (err: any) {
        const data = err?.response?.data;
        if (data?.errors) {
            Object.assign(
                errors,
                Object.fromEntries(
                    Object.entries(data.errors as Record<string, string[]>).map(([k, v]) => [k, v[0]]),
                ),
            );
        } else {
            generalError.value = data?.message ?? 'Something went wrong. Please try again.';
        }
    } finally {
        loading.value = false;
    }
};
</script>
