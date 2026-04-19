<template>
    <div class="min-h-screen bg-white text-gray-900">
        <Navbar />

        <!-- Hero -->
        <section class="bg-gradient-to-br from-[#1a1a4e] via-[#0f2460] to-[#0c1d50] py-20 text-center">
            <div class="mx-auto max-w-3xl px-6">
                <span class="inline-block rounded-full bg-orange-500/10 px-4 py-1 text-xs font-semibold uppercase tracking-widest text-orange-400">{{ t('policy.badge') }}</span>
                <h1 class="mt-4 text-4xl font-bold text-white">{{ t('policy.headline') }}</h1>
                <p class="mt-3 text-blue-100/70">{{ t('policy.updated') }}</p>
            </div>
        </section>

        <!-- Content -->
        <main class="mx-auto max-w-3xl px-6 py-14">
            <div class="space-y-10 text-gray-600 leading-relaxed">

                <section v-for="section in sections" :key="section.title">
                    <h2 class="text-lg font-semibold text-gray-900">{{ section.title }}</h2>
                    <p class="mt-3 text-sm">{{ section.body }}</p>
                </section>

                <section>
                    <h2 class="text-lg font-semibold text-gray-900">{{ t('policy.contactUs') }}</h2>
                    <p class="mt-3 text-sm">
                        {{ t('policy.contactNote') }}
                        <a href="mailto:privacy@cloudylearning.com" class="text-orange-500 hover:underline">privacy@cloudylearning.com</a>.
                    </p>
                </section>
            </div>
        </main>
        <AppFooter />
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import Navbar from '../components/Navbar.vue';
import AppFooter from '../components/Footer.vue';

const { t, tm } = useI18n();

const sections = computed(() => tm('policy.sections') as Array<{ title: string; body: string }>);
</script>
