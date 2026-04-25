<template>
    <div class="min-h-screen bg-gray-50 text-gray-900">
        <Navbar />

        <main class="mx-auto max-w-2xl px-6 py-10">
            <div class="mb-8 flex items-center gap-3">
                <RouterLink to="/profile" class="text-sm text-gray-500 transition hover:text-gray-900">
                    ← Profile
                </RouterLink>
                <span class="text-gray-300">/</span>
                <h1 class="text-2xl font-semibold">Change Password</h1>
            </div>

            <!-- Success message -->
            <div
                v-if="successMessage"
                class="mb-6 rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
            >
                {{ successMessage }}
            </div>

            <!-- General error -->
            <div
                v-if="generalError"
                class="mb-6 rounded-xl border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700"
            >
                {{ generalError }}
            </div>

            <form @submit.prevent="submit" class="space-y-5 rounded-2xl border border-gray-200 bg-white p-6">
                <!-- Current password -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Current password</label>
                    <input
                        v-model="form.current_password"
                        type="password"
                        placeholder="········"
                        class="w-full rounded-xl border bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 outline-none transition focus:ring-2"
                        :class="errors.current_password ? 'border-red-500 focus:ring-red-500/30' : 'border-gray-200 focus:ring-orange-400/30 focus:border-orange-400/60'"
                    />
                    <p v-if="errors.current_password" class="mt-1 text-xs text-red-400">{{ errors.current_password }}</p>
                </div>

                <!-- New password -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">New password</label>
                    <input
                        v-model="form.password"
                        type="password"
                        placeholder="········"
                        class="w-full rounded-xl border bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 outline-none transition focus:ring-2"
                        :class="errors.password ? 'border-red-500 focus:ring-red-500/30' : 'border-gray-200 focus:ring-orange-400/30 focus:border-orange-400/60'"
                    />
                    <p v-if="errors.password" class="mt-1 text-xs text-red-400">{{ errors.password }}</p>
                </div>

                <!-- Confirm new password -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Confirm new password</label>
                    <input
                        v-model="form.password_confirmation"
                        type="password"
                        placeholder="········"
                        class="w-full rounded-xl border bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 outline-none transition focus:ring-2"
                        :class="errors.password_confirmation ? 'border-red-500 focus:ring-red-500/30' : 'border-gray-200 focus:ring-orange-400/30 focus:border-orange-400/60'"
                    />
                    <p v-if="errors.password_confirmation" class="mt-1 text-xs text-red-400">{{ errors.password_confirmation }}</p>
                </div>

                <button
                    type="submit"
                    :disabled="loading"
                    class="w-full rounded-xl bg-[#1a1a4e] py-3 text-sm font-semibold text-white transition hover:bg-[#0f2460] disabled:opacity-60"
                >
                    {{ loading ? 'Saving…' : 'Update password' }}
                </button>
            </form>
        </main>
        <AppFooter />
    </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';
import axios from 'axios';
import { RouterLink } from 'vue-router';
import Navbar from '../components/Navbar.vue';
import AppFooter from '../components/Footer.vue';

const form = reactive({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const errors = reactive<Record<string, string>>({});
const loading = ref(false);
const successMessage = ref('');
const generalError = ref('');

const submit = async () => {
    loading.value = true;
    successMessage.value = '';
    generalError.value = '';
    Object.keys(errors).forEach((k) => delete errors[k]);

    try {
        const { data } = await axios.put('/api/change-password', { ...form });
        form.current_password = '';
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
