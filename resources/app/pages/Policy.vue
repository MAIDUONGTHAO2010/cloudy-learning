<template>
    <div class="min-h-screen bg-slate-950 text-white">
        <Navbar />

        <!-- Hero -->
        <section class="bg-gradient-to-b from-slate-900 to-slate-950 py-20 text-center">
            <div class="mx-auto max-w-3xl px-6">
                <span class="inline-block rounded-full bg-sky-500/10 px-4 py-1 text-xs font-semibold uppercase tracking-widest text-sky-400">{{ t('policy.badge') }}</span>
                <h1 class="mt-4 text-4xl font-bold">{{ t('policy.headline') }}</h1>
                <p class="mt-3 text-slate-400">{{ t('policy.updated') }}</p>
            </div>
        </section>

        <!-- Content -->
        <main class="mx-auto max-w-3xl px-6 py-14">
            <div class="space-y-10 text-slate-300 leading-relaxed">

                <section v-for="section in sections" :key="section.title">
                    <h2 class="text-lg font-semibold text-white">{{ section.title }}</h2>
                    <p class="mt-3 text-sm">{{ section.body }}</p>
                </section>

                <section>
                    <h2 class="text-lg font-semibold text-white">{{ t('policy.contactUs') }}</h2>
                    <p class="mt-3 text-sm">
                        {{ t('policy.contactNote') }}
                        <a href="mailto:privacy@cloudylearning.com" class="text-sky-400 hover:underline">privacy@cloudylearning.com</a>.
                    </p>
                </section>
            </div>
        </main>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import Navbar from '../components/Navbar.vue';

const { t, tm } = useI18n();

const sections = computed(() => tm('policy.sections') as Array<{ title: string; body: string }>);
</script>
