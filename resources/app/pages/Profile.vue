<template>
    <div class="min-h-screen bg-gray-50 text-gray-900">
        <Navbar />

        <main class="mx-auto max-w-2xl px-6 py-10">
            <h1 class="mb-8 text-2xl font-semibold">Profile Settings</h1>

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

            <!-- Avatar section -->
            <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-6">
                <h2 class="mb-4 text-sm font-medium text-gray-700">Profile Photo</h2>
                <div class="flex items-center gap-5">
                    <!-- Current / preview avatar -->
                    <div class="relative h-20 w-20 shrink-0">
                        <img
                            v-if="avatarPreview || user?.profile?.avatar"
                            :src="avatarPreview || user?.profile?.avatar"
                            alt="Avatar"
                            class="h-20 w-20 rounded-full object-cover border border-gray-200"
                        />
                        <span
                            v-else
                            class="grid h-20 w-20 place-items-center rounded-full bg-blue-600 text-3xl font-bold text-white"
                        >
                            {{ user?.name?.charAt(0)?.toUpperCase() ?? '?' }}
                        </span>
                        <!-- Loading overlay -->
                        <div
                            v-if="avatarUploading"
                            class="absolute inset-0 flex items-center justify-center rounded-full bg-black/40"
                        >
                            <svg class="h-6 w-6 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                            </svg>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label
                            class="cursor-pointer rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                        >
                            {{ user?.profile?.avatar ? 'Change photo' : 'Upload photo' }}
                            <input
                                ref="avatarInput"
                                type="file"
                                accept="image/*"
                                class="hidden"
                                @change="onAvatarSelected"
                            />
                        </label>
                        <p class="text-xs text-gray-400">JPG, PNG or GIF · Max 5 MB</p>
                        <p v-if="avatarError" class="text-xs text-red-400">{{ avatarError }}</p>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-5 rounded-2xl border border-gray-200 bg-white p-6">
                <!-- Name -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Full name</label>
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="Your name"
                        class="w-full rounded-xl border bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 outline-none transition focus:ring-2"
                        :class="errors.name ? 'border-red-500 focus:ring-red-500/30' : 'border-gray-200 focus:ring-orange-400/30 focus:border-orange-400/60'"
                    />
                    <p v-if="errors.name" class="mt-1 text-xs text-red-400">{{ errors.name }}</p>
                </div>

                <!-- Email -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Email address</label>
                    <input
                        v-model="form.email"
                        type="email"
                        placeholder="you@example.com"
                        class="w-full rounded-xl border bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 outline-none transition focus:ring-2"
                        :class="errors.email ? 'border-red-500 focus:ring-red-500/30' : 'border-gray-200 focus:ring-orange-400/30 focus:border-orange-400/60'"
                    />
                    <p v-if="errors.email" class="mt-1 text-xs text-red-400">{{ errors.email }}</p>
                </div>

                <button
                    type="submit"
                    :disabled="loading"
                    class="w-full rounded-xl bg-[#1a1a4e] py-3 text-sm font-semibold text-white transition hover:bg-[#0f2460] disabled:opacity-60"
                >
                    {{ loading ? 'Saving…' : 'Save changes' }}
                </button>
            </form>

            <div class="mt-4 rounded-2xl border border-gray-200 bg-white p-5 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-700">Password</p>
                    <p class="mt-0.5 text-xs text-gray-400">Change your account password on a dedicated page.</p>
                </div>
                <RouterLink
                    to="/change-password"
                    class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                >
                    Change password
                </RouterLink>
            </div>
        </main>
        <AppFooter />
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';
import { RouterLink } from 'vue-router';
import { useAuth } from '../composables/useAuth';
import Navbar from '../components/Navbar.vue';
import AppFooter from '../components/Footer.vue';

const { user, setUser } = useAuth();

const form = reactive({
    name: '',
    email: '',
});

const errors = reactive<Record<string, string>>({});
const loading = ref(false);
const successMessage = ref('');
const generalError = ref('');

// Avatar state
const avatarInput = ref<HTMLInputElement | null>(null);
const avatarPreview = ref<string | null>(null);
const avatarUploading = ref(false);
const avatarError = ref('');
const pendingAvatarPath = ref<string | null>(null);

onMounted(() => {
    if (user.value) {
        form.name = user.value.name;
        form.email = user.value.email;
    }
});

const onAvatarSelected = async (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (!file) return;

    const MAX_SIZE = 5 * 1024 * 1024;
    if (file.size > MAX_SIZE) {
        avatarError.value = 'File is too large. Maximum size is 5 MB.';
        return;
    }

    if (!file.type.startsWith('image/')) {
        avatarError.value = 'Only image files are allowed.';
        return;
    }

    avatarError.value = '';
    avatarPreview.value = URL.createObjectURL(file);
    avatarUploading.value = true;

    try {
        // 1. Get presigned upload URL from backend
        const { data: presign } = await axios.post('/api/profile/presign-avatar', {
            file_name: file.name,
            content_type: file.type,
            file_size: file.size,
        });

        // 2. Upload file directly to S3 using the presigned PUT URL
        await axios.put(presign.upload_url, file, {
            headers: presign.headers,
            withCredentials: false,
        });

        // Store path to include when saving profile
        pendingAvatarPath.value = presign.path;
    } catch (err: any) {
        const msg = err?.response?.data?.message ?? err?.message ?? 'Unknown error';
        avatarError.value = `Failed to upload photo: ${msg}. Please try again.`;
        avatarPreview.value = null;
        pendingAvatarPath.value = null;
    } finally {
        avatarUploading.value = false;
        // Reset file input so the same file can be re-selected if needed
        if (avatarInput.value) avatarInput.value.value = '';
    }
};

const submit = async () => {
    loading.value = true;
    successMessage.value = '';
    generalError.value = '';
    Object.keys(errors).forEach((k) => delete errors[k]);

    try {
        const payload: Record<string, any> = { ...form };
        if (pendingAvatarPath.value) {
            payload.avatar = pendingAvatarPath.value;
        }

        const { data } = await axios.put('/api/profile', payload);
        setUser(data.user);
        pendingAvatarPath.value = null;
        avatarPreview.value = null;
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

