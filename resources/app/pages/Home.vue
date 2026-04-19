<template>
    <div class="min-h-screen bg-gray-50 text-gray-900">
        <Navbar />

        <main class="pb-16">
            <!-- Hero Section -->
            <section class="relative overflow-hidden bg-gradient-to-br from-[#1a1a4e] via-[#0f2460] to-[#0c1d50]">
                <div class="pointer-events-none absolute inset-0 overflow-hidden">
                    <div class="absolute -right-32 -top-32 h-80 w-80 rounded-full bg-orange-500/10 blur-3xl"></div>
                    <div class="absolute bottom-0 left-1/4 h-64 w-64 rounded-full bg-orange-400/8 blur-3xl"></div>
                </div>
                <div class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
                    <div class="mx-auto max-w-3xl text-center">
                        <div class="inline-flex items-center gap-2 rounded-full border border-orange-400/30 bg-orange-400/10 px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.28em] text-orange-200">
                            <span class="h-2 w-2 rounded-full bg-orange-400"></span>
                            {{ slides[activeSlide].eyebrow }}
                        </div>

                        <h1 class="mt-6 text-4xl font-black leading-tight text-white sm:text-5xl lg:text-6xl">
                            Smart Study <span class="text-orange-400">Where Knowledge</span> Meets the Web
                        </h1>
                        <p class="mt-5 text-lg leading-8 text-blue-100/80">
                            {{ slides[activeSlide].subtitle }}
                        </p>

                        <!-- Search Bar -->
                        <form class="mx-auto mt-8 flex max-w-xl overflow-hidden rounded-full border border-white/10 bg-white/10 shadow-lg backdrop-blur" @submit.prevent="goSearch">
                            <input
                                v-model="searchQuery"
                                type="text"
                                :placeholder="t('courses.searchPlaceholder')"
                                class="flex-1 bg-transparent px-6 py-4 text-sm text-white placeholder-white/50 focus:outline-none"
                                @keydown.enter.prevent="goSearch"
                            />
                            <button
                                type="button"
                                @click="goSearch"
                                class="m-1.5 rounded-full bg-orange-500 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-600"
                            >
                                {{ t('home.hero.cta') }}
                            </button>
                        </form>

                        <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                            <span
                                v-for="item in heroHighlights"
                                :key="item.label"
                                class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/8 px-4 py-2 text-sm text-blue-100"
                            >
                                <span>{{ item.icon }}</span>
                                {{ item.label }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Stats Bar -->
                <div class="relative border-t border-white/10 bg-white/5 backdrop-blur">
                    <div class="mx-auto grid max-w-7xl grid-cols-2 divide-x divide-white/10 px-4 sm:px-6 lg:grid-cols-4 lg:px-8">
                        <div
                            v-for="stat in heroStats"
                            :key="stat.label"
                            class="flex flex-col items-center py-6 text-center"
                        >
                            <p class="text-3xl font-black text-orange-400">{{ stat.value }}</p>
                            <p class="mt-1 text-sm text-blue-100/70">{{ stat.label }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Start Your Journey Section -->
            <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <div class="grid gap-10 lg:grid-cols-2 lg:items-center">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-orange-500">Get Started</p>
                        <h2 class="mt-3 text-3xl font-black text-gray-900 md:text-4xl">{{ t('home.ctaBanner.title') }}</h2>
                        <p class="mt-4 text-base leading-7 text-gray-500">
                            We offer a brand new approach to the most basic learning paradigms. Choose from a wide range of learning options and gain new skills with expert instructors.
                        </p>
                        <div class="mt-8 flex flex-wrap gap-4">
                            <RouterLink
                                to="/courses"
                                class="rounded-full bg-orange-500 px-7 py-3 text-sm font-semibold text-white shadow-lg shadow-orange-500/30 transition hover:-translate-y-0.5 hover:bg-orange-600"
                            >
                                {{ t('home.ctaBanner.browse') }}
                            </RouterLink>
                            <RouterLink
                                v-if="!user"
                                to="/register"
                                class="rounded-full border border-orange-500 px-7 py-3 text-sm font-semibold text-orange-500 transition hover:bg-orange-50"
                            >
                                {{ t('home.hero.ctaSecondary') }}
                            </RouterLink>
                            <RouterLink
                                v-else
                                to="/dashboard"
                                class="rounded-full border border-orange-500 px-7 py-3 text-sm font-semibold text-orange-500 transition hover:bg-orange-50"
                            >
                                {{ t('home.hero.myLearning') }} →
                            </RouterLink>
                        </div>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-3 lg:grid-cols-1 xl:grid-cols-3">
                        <div
                            v-for="feature in featureCards"
                            :key="feature.title"
                            class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-md"
                        >
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-orange-100 text-2xl">
                                {{ feature.icon }}
                            </div>
                            <h3 class="mt-4 text-base font-bold text-gray-900">{{ feature.title }}</h3>
                            <p class="mt-2 text-sm leading-6 text-gray-500">{{ feature.description }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Popular Courses Section -->
            <section class="bg-white px-4 py-16 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-7xl">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="mb-2 inline-flex rounded-full border border-orange-200 bg-orange-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-orange-500">Popular</p>
                            <h2 class="text-2xl font-black text-gray-900 md:text-3xl">{{ t('home.popularCourses') }}</h2>
                            <p class="mt-1 max-w-2xl text-sm leading-6 text-gray-500">{{ t('home.popularSubtitle') }}</p>
                        </div>
                        <RouterLink to="/courses" class="inline-flex items-center gap-2 rounded-full border border-orange-500 px-5 py-2 text-sm font-semibold text-orange-500 transition hover:bg-orange-50">
                            {{ t('common.viewAll') }}
                        </RouterLink>
                    </div>

                    <div v-if="loadingPopular" class="mt-8 grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
                        <div v-for="n in 4" :key="n" class="h-72 animate-pulse rounded-2xl border border-gray-100 bg-gray-100" />
                    </div>
                    <div v-else-if="popular.length" class="mt-8 grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
                        <RouterLink
                            v-for="c in popular"
                            :key="c.id"
                            :to="'/courses/' + c.slug"
                            class="group flex h-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-md"
                        >
                            <div class="relative aspect-[16/10] overflow-hidden bg-gray-100">
                                <img v-if="c.thumbnail" :src="c.thumbnail" :alt="c.title" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
                                <div v-else class="flex h-full w-full items-center justify-center bg-gradient-to-br from-orange-100 to-orange-50">
                                    <span class="text-5xl font-bold text-orange-300/50">{{ c.title.charAt(0) }}</span>
                                </div>
                                <div class="absolute inset-x-3 top-3 flex items-center justify-between gap-2">
                                    <span v-if="c.category" class="rounded-full bg-white/90 px-2.5 py-1 text-[11px] font-semibold text-orange-600 shadow-sm backdrop-blur">{{ c.category.name }}</span>
                                    <span v-if="c.reviews_avg_rating" class="rounded-full bg-amber-400 px-2.5 py-1 text-[11px] font-semibold text-white">★ {{ Number(c.reviews_avg_rating).toFixed(1) }}</span>
                                </div>
                            </div>
                            <div class="flex flex-1 flex-col p-5">
                                <h3 class="line-clamp-2 text-base font-bold leading-snug text-gray-900">{{ c.title }}</h3>
                                <p class="mt-1 text-xs text-gray-400">{{ t('common.by') }} {{ c.instructor?.name || '—' }}</p>
                                <div class="mt-auto flex items-center justify-between border-t border-gray-100 pt-4">
                                    <span class="rounded-full bg-orange-50 px-2.5 py-1 text-xs font-medium text-orange-600">{{ c.lessons_count }} {{ t('courses.lessons') }}</span>
                                    <span class="text-xs font-semibold text-orange-500">{{ t('common.learnMore') }} →</span>
                                </div>
                            </div>
                        </RouterLink>
                    </div>
                    <div v-else-if="errorPopular" class="mt-8 rounded-2xl border border-red-200 bg-red-50 p-8 text-center text-sm text-red-500">{{ errorPopular }}</div>
                    <div v-else class="mt-8 rounded-2xl border border-dashed border-orange-200 bg-orange-50 p-8 text-center text-sm text-gray-400">{{ t('common.noData') }}</div>
                </div>
            </section>

            <!-- Why Choose Us / Features Section -->
            <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <div class="mb-10 text-center">
                    <p class="text-sm font-semibold uppercase tracking-[0.3em] text-orange-500">Why Us</p>
                    <h2 class="mt-3 text-3xl font-black text-gray-900 md:text-4xl">{{ t('home.panels.featuresTitle') }}</h2>
                </div>
                <div class="grid gap-6 lg:grid-cols-3">
                    <div v-for="feature in featureCards" :key="feature.title + '-section'" class="rounded-2xl border border-gray-100 bg-white p-8 text-center shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-orange-100 text-3xl">{{ feature.icon }}</div>
                        <h3 class="mt-5 text-lg font-bold text-gray-900">{{ feature.title }}</h3>
                        <p class="mt-2 text-sm leading-6 text-gray-500">{{ feature.description }}</p>
                    </div>
                </div>
            </section>

            <!-- Instructors Section -->
            <section id="instructors" class="bg-white px-4 py-16 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-7xl">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="mb-2 inline-flex rounded-full border border-orange-200 bg-orange-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-orange-500">Mentors</p>
                            <h2 class="text-2xl font-black text-gray-900 md:text-3xl">{{ t('home.topInstructors') }}</h2>
                            <p class="mt-1 max-w-2xl text-sm leading-6 text-gray-500">{{ t('home.instructorsSubtitle') }}</p>
                        </div>
                    </div>

                    <div v-if="loadingInstructors" class="mt-8 grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                        <div v-for="n in 3" :key="n" class="h-64 animate-pulse rounded-2xl bg-gray-100" />
                    </div>

                    <div v-else-if="instructors.length" class="mt-8 grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                        <div
                            v-for="inst in instructors"
                            :key="inst.id"
                            class="group flex flex-col items-center rounded-2xl border border-gray-100 bg-white p-8 text-center shadow-sm transition hover:-translate-y-1 hover:shadow-md"
                        >
                            <div class="grid h-20 w-20 place-items-center rounded-full bg-gradient-to-br from-orange-400 to-orange-600 text-2xl font-bold text-white ring-4 ring-orange-100 transition group-hover:ring-orange-200">
                                {{ inst.name.charAt(0).toUpperCase() }}
                            </div>

                            <p class="mt-4 text-lg font-bold text-gray-900">{{ inst.name }}</p>
                            <span class="mt-1 inline-block rounded-full bg-orange-100 px-3 py-1 text-xs font-semibold text-orange-600">
                                {{ t('home.instructorBadge') }}
                            </span>

                            <div class="mt-3 flex flex-wrap items-center justify-center gap-2 text-xs">
                                <span class="rounded-full bg-gray-100 px-3 py-1 text-gray-600">
                                    {{ inst.courses_count }} {{ t('courses.lessons') }}
                                </span>
                                <span
                                    v-if="inst.course_reviews_avg_rating"
                                    class="rounded-full bg-amber-50 px-3 py-1 text-amber-600"
                                >
                                    ★ {{ Number(inst.course_reviews_avg_rating).toFixed(1) }}
                                </span>
                                <span v-else class="rounded-full bg-gray-100 px-3 py-1 text-gray-400">
                                    {{ t('home.noRatings') }}
                                </span>
                            </div>

                            <RouterLink
                                :to="`/courses?instructor_id=${inst.id}`"
                                class="mt-6 w-full rounded-full border border-orange-500 px-4 py-2.5 text-sm font-semibold text-orange-500 transition hover:bg-orange-50"
                            >
                                {{ t('home.viewCourses') }}
                            </RouterLink>
                        </div>
                    </div>

                    <div v-else-if="errorInstructors" class="mt-8 rounded-2xl border border-red-200 bg-red-50 p-8 text-center text-sm text-red-500">
                        {{ errorInstructors }}
                    </div>
                    <div v-else class="mt-8 rounded-2xl border border-dashed border-orange-200 bg-orange-50 p-8 text-center text-sm text-gray-400">
                        {{ t('common.noData') }}
                    </div>
                </div>
            </section>

            <!-- Newest Courses Section -->
            <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="mb-2 inline-flex rounded-full border border-orange-200 bg-orange-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-orange-500">New</p>
                        <h2 class="text-2xl font-black text-gray-900 md:text-3xl">{{ t('home.newestCourses') }}</h2>
                        <p class="mt-1 max-w-2xl text-sm leading-6 text-gray-500">{{ t('home.newestSubtitle') }}</p>
                    </div>
                    <RouterLink to="/courses" class="inline-flex items-center gap-2 rounded-full border border-orange-500 px-5 py-2 text-sm font-semibold text-orange-500 transition hover:bg-orange-50">
                        {{ t('common.viewAll') }}
                    </RouterLink>
                </div>

                <div v-if="loadingNewest" class="mt-8 grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
                    <div v-for="n in 4" :key="n" class="h-72 animate-pulse rounded-2xl border border-gray-100 bg-gray-100" />
                </div>
                <div v-else-if="newest.length" class="mt-8 grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
                    <RouterLink
                        v-for="c in newest"
                        :key="c.id"
                        :to="'/courses/' + c.slug"
                        class="group flex h-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-md"
                    >
                        <div class="relative aspect-[16/10] overflow-hidden bg-gray-100">
                            <img v-if="c.thumbnail" :src="c.thumbnail" :alt="c.title" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
                            <div v-else class="flex h-full w-full items-center justify-center bg-gradient-to-br from-orange-100 to-orange-50">
                                <span class="text-5xl font-bold text-orange-300/50">{{ c.title.charAt(0) }}</span>
                            </div>
                            <div class="absolute inset-x-3 top-3 flex items-center justify-between gap-2">
                                <span v-if="c.category" class="rounded-full bg-white/90 px-2.5 py-1 text-[11px] font-semibold text-orange-600 shadow-sm backdrop-blur">{{ c.category.name }}</span>
                                <span v-if="c.reviews_avg_rating" class="rounded-full bg-amber-400 px-2.5 py-1 text-[11px] font-semibold text-white">★ {{ Number(c.reviews_avg_rating).toFixed(1) }}</span>
                            </div>
                        </div>
                        <div class="flex flex-1 flex-col p-5">
                            <h3 class="line-clamp-2 text-base font-bold leading-snug text-gray-900">{{ c.title }}</h3>
                            <p class="mt-1 text-xs text-gray-400">{{ t('common.by') }} {{ c.instructor?.name || '—' }}</p>
                            <div class="mt-auto flex items-center justify-between border-t border-gray-100 pt-4">
                                <span class="rounded-full bg-orange-50 px-2.5 py-1 text-xs font-medium text-orange-600">{{ c.lessons_count }} {{ t('courses.lessons') }}</span>
                                <span class="text-xs font-semibold text-orange-500">{{ t('common.learnMore') }} →</span>
                            </div>
                        </div>
                    </RouterLink>
                </div>
                <div v-else-if="errorNewest" class="mt-8 rounded-2xl border border-red-200 bg-red-50 p-8 text-center text-sm text-red-500">{{ errorNewest }}</div>
                <div v-else class="mt-8 rounded-2xl border border-dashed border-gray-200 bg-gray-50 p-8 text-center text-sm text-gray-400">{{ t('common.noData') }}</div>
            </section>

            <!-- CTA Banner -->
            <section class="bg-gradient-to-br from-[#1a1a4e] via-[#0f2460] to-[#0c1d50] px-4 py-20 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-3xl text-center">
                    <h2 class="text-3xl font-black text-white md:text-4xl">{{ t('home.ctaBanner.title') }}</h2>
                    <p class="mx-auto mt-4 max-w-xl text-blue-100/80">{{ t('home.ctaBanner.body') }}</p>
                    <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                        <template v-if="!user">
                            <RouterLink
                                to="/register"
                                class="rounded-full bg-orange-500 px-8 py-3.5 text-sm font-semibold text-white shadow-lg shadow-orange-500/30 transition hover:-translate-y-0.5 hover:bg-orange-600"
                            >
                                {{ t('home.hero.ctaSecondary') }}
                            </RouterLink>
                            <RouterLink
                                to="/courses"
                                class="rounded-full border border-white/30 px-8 py-3.5 text-sm font-semibold text-white transition hover:bg-white/10"
                            >
                                {{ t('home.ctaBanner.browse') }}
                            </RouterLink>
                        </template>
                        <template v-else>
                            <RouterLink
                                to="/dashboard"
                                class="rounded-full bg-orange-500 px-8 py-3.5 text-sm font-semibold text-white shadow-lg shadow-orange-500/30 transition hover:-translate-y-0.5 hover:bg-orange-600"
                            >
                                {{ t('home.ctaBanner.dashboard') }}
                            </RouterLink>
                            <RouterLink
                                to="/courses"
                                class="rounded-full border border-white/30 px-8 py-3.5 text-sm font-semibold text-white transition hover:bg-white/10"
                            >
                                {{ t('home.ctaBanner.browse') }}
                            </RouterLink>
                        </template>
                    </div>
                </div>
            </section>

            <!-- Footer -->
            <AppFooter />
        </main>
    </div>
</template>

<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { useAuth } from '../composables/useAuth';
import Navbar from '../components/Navbar.vue';
import AppFooter from '../components/Footer.vue';

const { t } = useI18n();
const router = useRouter();

const searchQuery = ref('');
const goSearch = () => {
    router.push({ path: '/courses', query: searchQuery.value.trim() ? { search: searchQuery.value.trim() } : {} });
};
const { user } = useAuth();

interface Course {
    id: number;
    title: string;
    slug: string;
    thumbnail: string | null;
    lessons_count: number;
    reviews_avg_rating: string | null;
    reviews_count?: number;
    instructor: { id: number; name: string } | null;
    category: { id: number; name: string } | null;
}

const slides = computed(() => [
    {
        id: 1,
        eyebrow: t('home.slides.platform.eyebrow'),
        title: t('home.slides.platform.title'),
        subtitle: t('home.slides.platform.subtitle'),
        bg: 'linear-gradient(135deg, #050816 0%, #0b1733 45%, #081120 100%)',
        cta: { label: t('home.hero.cta'), link: '/courses' },
    },
    {
        id: 2,
        eyebrow: t('home.slides.fresh.eyebrow'),
        title: t('home.slides.fresh.title'),
        subtitle: t('home.slides.fresh.subtitle'),
        bg: 'linear-gradient(135deg, #07121f 0%, #12233f 45%, #0a1524 100%)',
        cta: { label: t('home.ctaBanner.browse'), link: '/courses' },
    },
    {
        id: 3,
        eyebrow: t('home.slides.experts.eyebrow'),
        title: t('home.slides.experts.title'),
        subtitle: t('home.slides.experts.subtitle'),
        bg: 'linear-gradient(135deg, #0a1022 0%, #16284f 45%, #0b1323 100%)',
        cta: { label: t('home.topInstructors'), link: '#instructors' },
    },
    {
        id: 4,
        eyebrow: t('home.slides.career.eyebrow'),
        title: t('home.slides.career.title'),
        subtitle: t('home.slides.career.subtitle'),
        bg: 'linear-gradient(135deg, #091510 0%, #103225 45%, #08130e 100%)',
        cta: { label: t('home.hero.ctaSecondary'), link: user.value ? '/dashboard' : '/register' },
    },
]);

const heroHighlights = computed(() => [
    { icon: '⏱️', label: t('home.chips.flexible') },
    { icon: '🧩', label: t('home.chips.projects') },
    { icon: '🎓', label: t('home.chips.support') },
]);

const featureCards = computed(() => [
    { icon: '📘', title: t('home.features.projectsTitle'), description: t('home.features.projectsDesc') },
    { icon: '⚡', title: t('home.features.flexibleTitle'), description: t('home.features.flexibleDesc') },
    { icon: '🌟', title: t('home.features.expertTitle'), description: t('home.features.expertDesc') },
]);

const stats = computed(() => [
    { value: '500+', label: t('home.stats.courses') },
    { value: '12k+', label: t('home.stats.learners') },
    { value: '80+', label: t('home.stats.instructors') },
]);

const heroStats = computed(() => [
    { value: '134', label: t('home.stats.courses') },
    { value: '299', label: 'Academic Programs' },
    { value: '684', label: 'Certified Students' },
    { value: '941', label: t('home.stats.learners') },
]);

const activeSlide = ref(0);
let timer: ReturnType<typeof setInterval> | null = null;

const goTo = (i: number) => {
    activeSlide.value = i;
    resetTimer();
};

const next = () => goTo((activeSlide.value + 1) % slides.value.length);
const prev = () => goTo((activeSlide.value - 1 + slides.value.length) % slides.value.length);

const resetTimer = () => {
    if (timer) clearInterval(timer);
    timer = setInterval(next, 5000);
};

const popular = ref<Course[]>([]);
const newest = ref<Course[]>([]);
const instructors = ref<{ id: number; name: string; email: string; courses_count: number; course_reviews_avg_rating: string | null }[]>([]);

const loadingPopular = ref(true);
const loadingNewest = ref(true);
const loadingInstructors = ref(true);

const errorPopular = ref('');
const errorNewest = ref('');
const errorInstructors = ref('');

const featuredCourse = computed(() => popular.value[0] ?? newest.value[0] ?? null);

onMounted(() => {
    resetTimer();

    axios.get('/api/courses/popular').then(({ data }) => {
        popular.value = Array.isArray(data) ? data : [];
    }).catch((err) => {
        errorPopular.value = err?.response?.data?.message ?? err.message ?? 'Error';
    }).finally(() => {
        loadingPopular.value = false;
    });

    axios.get('/api/courses/newest').then(({ data }) => {
        newest.value = Array.isArray(data) ? data : [];
    }).catch((err) => {
        errorNewest.value = err?.response?.data?.message ?? err.message ?? 'Error';
    }).finally(() => {
        loadingNewest.value = false;
    });

    axios.get('/api/courses/instructors').then(({ data }) => {
        instructors.value = Array.isArray(data) ? data : [];
    }).catch((err) => {
        errorInstructors.value = err?.response?.data?.message ?? err.message ?? 'Error';
    }).finally(() => {
        loadingInstructors.value = false;
    });
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
});
</script>

<style scoped>
.slide-fade-enter-active,
.slide-fade-leave-active {
    transition: opacity 0.45s ease, transform 0.45s ease;
}

.slide-fade-enter-from {
    opacity: 0;
    transform: translateX(24px);
}

.slide-fade-leave-to {
    opacity: 0;
    transform: translateX(-24px);
}
</style>


