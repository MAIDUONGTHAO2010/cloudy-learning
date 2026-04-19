<template>
    <div class="min-h-screen bg-gray-50 text-gray-900">
        <Navbar />

        <!-- Hero -->
        <section class="bg-gradient-to-br from-[#1a1a4e] via-[#0f2460] to-[#0c1d50] py-20 text-center">
            <div class="mx-auto max-w-3xl px-6">
                <span class="inline-block rounded-full bg-orange-500/10 px-4 py-1 text-xs font-semibold uppercase tracking-widest text-orange-400">{{ t('contact.badge') }}</span>
                <h1 class="mt-4 text-4xl font-bold text-white">{{ t('contact.headline') }}</h1>
                <p class="mt-4 text-blue-100/80 text-lg">{{ t('contact.subtitle') }}</p>
            </div>
        </section>

        <main class="mx-auto max-w-7xl px-6 py-16">
            <div class="grid gap-10 lg:grid-cols-2">

                <!-- Contact info -->
                <div class="space-y-6">
                    <h2 class="text-xl font-semibold">{{ t('contact.reachUs') }}</h2>
                    <div v-for="item in contactInfo" :key="item.label" class="flex items-start gap-4 rounded-2xl border border-gray-200 bg-white p-5">
                        <div class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-orange-500/10 text-orange-500">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-widest text-gray-500">{{ item.label }}</p>
                            <p class="mt-1 text-sm text-gray-900">{{ item.value }}</p>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-white p-5">
                        <p class="text-xs font-semibold uppercase tracking-widest text-gray-500">{{ t('contact.businessHours') }}</p>
                        <p class="mt-2 text-sm text-gray-900">{{ t('contact.hoursValue') }}</p>
                        <p class="text-sm text-gray-500">{{ t('contact.hoursNote') }}</p>
                    </div>
                </div>

                <!-- Contact form -->
                <div class="rounded-2xl border border-gray-200 bg-white p-8">
                    <h2 class="mb-6 text-xl font-semibold">{{ t('contact.form.title') }}</h2>

                    <div v-if="sent" class="flex flex-col items-center py-12 text-center">
                        <div class="mb-4 grid h-14 w-14 place-items-center rounded-full bg-emerald-50 text-emerald-600">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <p class="font-semibold text-gray-900">{{ t('contact.form.sentTitle') }}</p>
                        <p class="mt-1 text-sm text-gray-500">{{ t('contact.form.sentBody') }}</p>
                        <button @click="sent = false" class="mt-5 text-sm text-orange-500 hover:underline">{{ t('contact.form.sendAnother') }}</button>
                    </div>

                    <form v-else @submit.prevent="submit" class="space-y-4">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-widest text-gray-500">{{ t('contact.form.name') }}</label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    required
                                    :placeholder="t('contact.form.name')"
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 outline-none transition focus:border-orange-400/60 focus:ring-1 focus:ring-orange-400/30"
                                />
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-widest text-gray-500">{{ t('contact.form.email') }}</label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    required
                                    placeholder="you@example.com"
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 outline-none transition focus:border-orange-400/60 focus:ring-1 focus:ring-orange-400/30"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-widest text-gray-500">{{ t('contact.form.subject') }}</label>
                            <select
                                v-model="form.subject"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-orange-400/60 focus:ring-1 focus:ring-orange-400/30"
                            >
                                <option value="">{{ t('contact.form.selectTopic') }}</option>
                                <option v-for="topic in topics" :key="topic" :value="topic">{{ topic }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-widest text-gray-500">{{ t('contact.form.message') }}</label>
                            <textarea
                                v-model="form.message"
                                required
                                rows="5"
                                :placeholder="t('contact.form.messagePlaceholder')"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 outline-none transition focus:border-orange-400/60 focus:ring-1 focus:ring-orange-400/30 resize-none"
                            />
                        </div>

                        <button
                            type="submit"
                            :disabled="loading"
                            class="w-full rounded-xl bg-orange-500 py-3 text-sm font-semibold text-white transition hover:bg-orange-600 disabled:opacity-50"
                        >
                            {{ loading ? t('contact.form.sending') : t('contact.form.send') }}
                        </button>
                    </form>
                </div>
            </div>
        </main>
        <AppFooter />
    </div>
</template>

<script setup lang="ts">
import axios from 'axios';
import { ref, reactive, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import Navbar from '../components/Navbar.vue';
import AppFooter from '../components/Footer.vue';

const { t, tm } = useI18n();

const sent = ref(false);
const loading = ref(false);

const form = reactive({
    name: '',
    email: '',
    subject: '',
    message: '',
});

const topics = computed(() => tm('contact.topics') as string[]);

const contactInfo = [
    {
        label: 'Email',
        value: 'support@cloudylearning.com',
        icon: 'M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75',
    },
    {
        label: 'Address',
        value: '123 Learning St, Ho Chi Minh City, Vietnam',
        icon: 'M15 10.5a3 3 0 11-6 0 3 3 0 016 0z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z',
    },
    {
        label: 'Phone',
        value: '+84 (28) 1234 5678',
        icon: 'M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z',
    },
];

const submit = async () => {
    loading.value = true;

    try {
        await axios.post('/api/contact', form);

        sent.value = true;
        form.name = '';
        form.email = '';
        form.subject = '';
        form.message = '';
    } catch (error) {
        console.error('Failed to send contact message', error);
    } finally {
        loading.value = false;
    }
};
</script>
