<template>
    <div class="min-h-screen bg-slate-950 text-white">
        <Navbar />

        <!-- Hero -->
        <section class="bg-gradient-to-b from-slate-900 to-slate-950 py-20 text-center">
            <div class="mx-auto max-w-3xl px-6">
                <span class="inline-block rounded-full bg-sky-500/10 px-4 py-1 text-xs font-semibold uppercase tracking-widest text-sky-400">{{ t('about.badge') }}</span>
                <h1 class="mt-4 text-4xl font-bold leading-tight">{{ t('about.headline') }}</h1>
                <p class="mt-5 text-lg text-slate-400 leading-relaxed">{{ t('about.body') }}</p>
            </div>
        </section>

        <!-- Mission & Vision -->
        <section class="mx-auto max-w-7xl px-6 py-16">
            <div class="grid gap-8 md:grid-cols-2">
                <div class="rounded-2xl border border-white/10 bg-slate-900 p-8">
                    <div class="mb-4 grid h-12 w-12 place-items-center rounded-xl bg-sky-500/10 text-sky-400">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-semibold">{{ t('about.mission.title') }}</h2>
                    <p class="mt-3 text-slate-400 leading-relaxed">{{ t('about.mission.body') }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-slate-900 p-8">
                    <div class="mb-4 grid h-12 w-12 place-items-center rounded-xl bg-cyan-500/10 text-cyan-400">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-semibold">{{ t('about.vision.title') }}</h2>
                    <p class="mt-3 text-slate-400 leading-relaxed">{{ t('about.vision.body') }}</p>
                </div>
            </div>
        </section>

        <!-- Stats -->
        <section class="border-y border-white/5 bg-slate-900">
            <div class="mx-auto grid max-w-5xl grid-cols-2 gap-px md:grid-cols-4">
                <div v-for="stat in stats" :key="stat.label" class="py-12 text-center">
                    <p class="text-3xl font-bold text-white">{{ stat.value }}</p>
                    <p class="mt-1 text-sm text-slate-400">{{ stat.label }}</p>
                </div>
            </div>
        </section>

        <!-- Values -->
        <section class="mx-auto max-w-7xl px-6 py-16">
            <h2 class="text-center text-2xl font-semibold">{{ t('about.standFor') }}</h2>
            <p class="mx-auto mt-3 max-w-xl text-center text-slate-400">{{ t('about.valuesSubtitle') }}</p>
            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div v-for="val in values" :key="val.title" class="rounded-2xl border border-white/10 bg-slate-900 p-6">
                    <span class="text-2xl">{{ val.icon }}</span>
                    <h3 class="mt-3 font-semibold text-white">{{ val.title }}</h3>
                    <p class="mt-2 text-sm text-slate-400 leading-relaxed">{{ val.desc }}</p>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="bg-gradient-to-r from-sky-600 to-cyan-500 py-14 text-center">
            <h2 class="text-2xl font-bold text-white">{{ t('about.cta.headline') }}</h2>
            <p class="mt-2 text-sky-100">{{ t('about.cta.body') }}</p>
            <RouterLink to="/register" class="mt-6 inline-block rounded-2xl bg-white px-8 py-3 text-sm font-semibold text-sky-700 transition hover:bg-sky-50">
                {{ t('about.cta.button') }}
            </RouterLink>
        </section>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import Navbar from '../components/Navbar.vue';

const { t, tm } = useI18n();

const stats = computed(() => tm('about.stats') as Array<{ value: string; label: string }>);
const values = computed(() => tm('about.values') as Array<{ icon: string; title: string; desc: string }>);
</script>
