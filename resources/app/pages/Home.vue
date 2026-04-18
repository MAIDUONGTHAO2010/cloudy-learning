<template>
    <div class="min-h-screen bg-gray-950 text-white">
        <Navbar />

        <!-- ─── Hero Slider ─────────────────────────────────────────── -->
        <section class="relative h-[540px] overflow-hidden">
            <!-- Slides -->
            <transition-group name="slide-fade" tag="div" class="h-full">
                <div
                    v-for="(slide, i) in slides"
                    v-show="i === activeSlide"
                    :key="slide.id"
                    class="absolute inset-0 flex items-center"
                    :style="{ background: slide.bg }"
                >
                    <!-- Decorative gradient overlay -->
                    <div class="absolute inset-0 bg-gradient-to-r from-gray-950/80 via-gray-950/40 to-transparent" />

                    <div class="relative z-10 mx-auto max-w-7xl w-full px-8 md:px-14">
                        <p class="mb-3 text-xs font-semibold uppercase tracking-[0.35em] text-sky-400">
                            {{ slide.eyebrow }}
                        </p>
                        <h1 class="max-w-xl text-4xl font-bold leading-tight text-white md:text-5xl">
                            {{ slide.title }}
                        </h1>
                        <p class="mt-4 max-w-md text-base text-gray-300">
                            {{ slide.subtitle }}
                        </p>
                        <div class="mt-8 flex flex-wrap gap-4">
                            <RouterLink
                                :to="slide.cta.link"
                                class="rounded-2xl bg-sky-500 px-7 py-3 text-sm font-semibold text-white transition hover:bg-sky-600"
                            >
                                {{ slide.cta.label }}
                            </RouterLink>
                            <template v-if="!user">
                                <RouterLink
                                    to="/register"
                                    class="rounded-2xl border border-white/25 px-7 py-3 text-sm font-semibold text-white transition hover:bg-white/10"
                                >
                                    Join for free
                                </RouterLink>
                                <RouterLink
                                    to="/login"
                                    class="rounded-2xl px-7 py-3 text-sm font-semibold text-white/70 transition hover:text-white"
                                >
                                    Sign in
                                </RouterLink>
                            </template>
                            <RouterLink
                                v-else
                                to="/dashboard"
                                class="rounded-2xl border border-white/25 px-7 py-3 text-sm font-semibold text-white transition hover:bg-white/10"
                            >
                                My learning →
                            </RouterLink>
                        </div>
                    </div>
                </div>
            </transition-group>

            <!-- Dots -->
            <div class="absolute bottom-5 left-1/2 z-20 flex -translate-x-1/2 gap-2">
                <button
                    v-for="(_, i) in slides"
                    :key="i"
                    @click="goTo(i)"
                    :aria-label="`Slide ${i + 1}`"
                    class="h-2 rounded-full transition-all duration-300"
                    :class="i === activeSlide ? 'w-8 bg-sky-400' : 'w-2 bg-white/30 hover:bg-white/60'"
                />
            </div>

            <!-- Arrow controls -->
            <button
                @click="prev"
                aria-label="Previous"
                class="absolute left-4 top-1/2 z-20 -translate-y-1/2 rounded-full border border-white/20 bg-gray-950/50 p-2.5 transition hover:bg-gray-950/80"
            >
                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <button
                @click="next"
                aria-label="Next"
                class="absolute right-4 top-1/2 z-20 -translate-y-1/2 rounded-full border border-white/20 bg-gray-950/50 p-2.5 transition hover:bg-gray-950/80"
            >
                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
        </section>

        <!-- ─── Stats bar ───────────────────────────────────────────── -->
        <section class="border-y border-white/8 bg-gray-900">
            <div class="mx-auto grid max-w-5xl grid-cols-3 divide-x divide-white/8">
                <div v-for="stat in stats" :key="stat.label" class="px-6 py-8 text-center">
                    <p class="text-3xl font-bold text-white">{{ stat.value }}</p>
                    <p class="mt-1 text-sm text-gray-400">{{ stat.label }}</p>
                </div>
            </div>
        </section>

        <!-- ─── Popular Courses ─────────────────────────────────────── -->
        <section class="mx-auto max-w-7xl px-6 py-16">
            <SectionHeader title="Popular Courses" subtitle="Top-rated by our learners" link="/courses" />

            <div v-if="loadingPopular" class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <SkeletonCard v-for="n in 4" :key="n" />
            </div>
            <div v-else class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <CourseCard v-for="c in popular" :key="c.id" :course="c" />
            </div>
        </section>

        <!-- ─── Newest Courses ──────────────────────────────────────── -->
        <section class="bg-gray-900">
            <div class="mx-auto max-w-7xl px-6 py-16">
                <SectionHeader title="New Courses" subtitle="Just added to the platform" link="/courses" />

                <div v-if="loadingNewest" class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <SkeletonCard v-for="n in 4" :key="n" />
                </div>
                <div v-else class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <CourseCard v-for="c in newest" :key="c.id" :course="c" />
                </div>
            </div>
        </section>

        <!-- ─── Instructors ─────────────────────────────────────────── -->
        <section id="instructors" class="mx-auto max-w-7xl px-6 py-16">
            <SectionHeader title="Meet Our Instructors" subtitle="Learn from industry professionals" />
            <p class="mt-3 text-center text-gray-400 max-w-2xl mx-auto">
                Here are some of our most popular instructors — real-world professionals who bring
                hands-on expertise to every lesson. Explore their courses and start learning today.
            </p>

            <!-- Skeleton -->
            <div v-if="loadingInstructors" class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div v-for="n in 3" :key="n" class="h-56 animate-pulse rounded-2xl bg-gray-900" />
            </div>

            <!-- Cards -->
            <div v-else class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="inst in instructors"
                    :key="inst.id"
                    class="group flex flex-col items-center rounded-2xl border border-white/8 bg-gray-900 p-6 text-center transition hover:border-sky-500/40 hover:shadow-lg hover:shadow-sky-900/20"
                >
                    <!-- Avatar -->
                    <div class="grid h-20 w-20 place-items-center rounded-full bg-gradient-to-br from-sky-500 to-cyan-400 text-2xl font-bold text-gray-950 ring-4 ring-gray-900 group-hover:ring-sky-500/20 transition">
                        {{ inst.name.charAt(0).toUpperCase() }}
                    </div>

                    <!-- Info -->
                    <p class="mt-4 font-semibold text-white text-lg leading-tight">{{ inst.name }}</p>
                    <span class="mt-1 inline-block rounded-full bg-sky-500/10 px-3 py-0.5 text-xs font-medium text-sky-400">Instructor</span>

                    <!-- Bio -->
                    <p class="mt-3 text-sm text-gray-400 leading-relaxed">
                        Passionate educator with hands-on industry experience, dedicated to helping students build real-world skills through clear, practical courses.
                    </p>

                    <!-- Stats row -->
                    <div class="mt-4 flex items-center justify-center gap-4 text-sm text-gray-400">
                        <span class="flex items-center gap-1">
                            <svg class="h-4 w-4 text-sky-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                            </svg>
                            {{ inst.courses_count }} course{{ inst.courses_count !== 1 ? 's' : '' }}
                        </span>
                        <span v-if="inst.course_reviews_avg_rating" class="flex items-center gap-1 text-amber-400">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.37 2.448a1 1 0 00-.364 1.118l1.287 3.957c.3.921-.755 1.688-1.54 1.118l-3.37-2.448a1 1 0 00-1.175 0l-3.37 2.448c-.784.57-1.838-.197-1.54-1.118l1.287-3.957a1 1 0 00-.364-1.118L2.063 9.384c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69l1.286-3.957z" />
                            </svg>
                            {{ Number(inst.course_reviews_avg_rating).toFixed(1) }}
                        </span>
                        <span v-else class="flex items-center gap-1 text-gray-600">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.37 2.448a1 1 0 00-.364 1.118l1.287 3.957c.3.921-.755 1.688-1.54 1.118l-3.37-2.448a1 1 0 00-1.175 0l-3.37 2.448c-.784.57-1.838-.197-1.54-1.118l1.287-3.957a1 1 0 00-.364-1.118L2.063 9.384c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69l1.286-3.957z" />
                            </svg>
                            No ratings yet
                        </span>
                    </div>

                    <!-- Social / contact -->
                    <div class="mt-4 flex items-center justify-center gap-3">
                        <a
                            :href="`mailto:${inst.email}`"
                            class="grid h-8 w-8 place-items-center rounded-full border border-white/10 text-gray-400 transition hover:border-sky-500/50 hover:text-sky-400"
                            title="Email"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                            </svg>
                        </a>
                    </div>

                    <!-- CTA -->
                    <RouterLink
                        :to="`/courses?instructor_id=${inst.id}`"
                        class="mt-5 w-full rounded-xl border border-sky-500/30 py-2 text-sm text-sky-400 transition hover:bg-sky-500/10"
                    >
                        View courses
                    </RouterLink>
                </div>
            </div>
        </section>

        <!-- ─── CTA banner ──────────────────────────────────────────── -->
        <section class="bg-gradient-to-r from-sky-600 to-cyan-500">
            <div class="mx-auto max-w-3xl px-6 py-16 text-center">
                <h2 class="text-3xl font-bold text-white">Ready to start learning?</h2>
                <p class="mt-3 text-sky-100">Join thousands of students already learning on Cloudy Learning.</p>
                <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                    <template v-if="!user">
                        <RouterLink
                            to="/register"
                            class="rounded-2xl bg-white px-8 py-3 text-sm font-semibold text-sky-700 transition hover:bg-sky-50"
                        >
                            Create free account
                        </RouterLink>
                        <RouterLink
                            to="/login"
                            class="rounded-2xl border border-white/40 px-8 py-3 text-sm font-semibold text-white transition hover:bg-white/10"
                        >
                            Sign in
                        </RouterLink>
                    </template>
                    <template v-else>
                        <RouterLink
                            to="/dashboard"
                            class="rounded-2xl bg-white px-8 py-3 text-sm font-semibold text-sky-700 transition hover:bg-sky-50"
                        >
                            Go to my learning
                        </RouterLink>
                        <RouterLink
                            to="/courses"
                            class="rounded-2xl border border-white/40 px-8 py-3 text-sm font-semibold text-white transition hover:bg-white/10"
                        >
                            Browse courses
                        </RouterLink>
                    </template>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-white/8 bg-gray-950 px-6 py-8 text-center text-sm text-gray-500">
            © {{ new Date().getFullYear() }} Cloudy Learning. All rights reserved.
        </footer>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import { useAuth } from '../composables/useAuth';
import Navbar from '../components/Navbar.vue';

const { user } = useAuth();

// ── Sub-components (inline) ───────────────────────────────────────────────

// SectionHeader
const SectionHeader = {
    props: ['title', 'subtitle', 'link'],
    template: `
        <div class="flex items-end justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white">{{ title }}</h2>
                <p class="mt-1 text-sm text-gray-400">{{ subtitle }}</p>
            </div>
            <a v-if="link" :href="link" class="text-sm text-sky-400 transition hover:text-sky-300 hover:underline">
                View all →
            </a>
        </div>
    `,
};

// SkeletonCard
const SkeletonCard = {
    template: `<div class="h-64 animate-pulse rounded-2xl border border-white/5 bg-gray-900" />`,
};

// CourseCard
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

const CourseCard = {
    props: ['course'],
    template: `
        <RouterLink
            :to="'/courses/' + course.slug"
            class="group flex flex-col rounded-2xl border border-white/8 bg-gray-900 overflow-hidden transition hover:border-sky-500/50 hover:shadow-xl hover:shadow-sky-900/20"
        >
            <div class="aspect-video w-full overflow-hidden bg-gray-800">
                <img v-if="course.thumbnail" :src="course.thumbnail" :alt="course.title"
                    class="h-full w-full object-cover transition group-hover:scale-105" />
                <div v-else class="flex h-full w-full items-center justify-center bg-gradient-to-br from-sky-900/40 to-gray-800">
                    <span class="text-5xl font-bold text-sky-300/20">{{ course.title.charAt(0) }}</span>
                </div>
            </div>
            <div class="flex flex-1 flex-col p-4">
                <span v-if="course.category" class="mb-1 text-xs font-medium text-sky-400">{{ course.category.name }}</span>
                <h3 class="line-clamp-2 text-sm font-semibold text-white leading-snug">{{ course.title }}</h3>
                <p class="mt-1 text-xs text-gray-500">by {{ course.instructor?.name }}</p>
                <div class="mt-auto flex items-center justify-between pt-3 border-t border-white/5">
                    <span class="text-xs text-gray-400">{{ course.lessons_count }} lessons</span>
                    <span v-if="course.reviews_avg_rating" class="text-xs text-amber-400">
                        ★ {{ Number(course.reviews_avg_rating).toFixed(1) }}
                    </span>
                </div>
            </div>
        </RouterLink>
    `,
};

// ── Slider ────────────────────────────────────────────────────────────────

const slides = [
    {
        id: 1,
        eyebrow: 'Online Learning Platform',
        title: 'Learn Without Limits',
        subtitle: 'Access hundreds of courses from industry experts. Start your journey today.',
        bg: 'linear-gradient(135deg, #0a0f1e 0%, #0c1a3a 50%, #0a1628 100%)',
        cta: { label: 'Browse courses', link: '/courses' },
    },
    {
        id: 2,
        eyebrow: 'New courses every week',
        title: 'Stay Ahead of the Curve',
        subtitle: 'Fresh content added weekly across development, design, data, and more.',
        bg: 'linear-gradient(135deg, #0a1020 0%, #0d1f3c 50%, #091520 100%)',
        cta: { label: 'See new courses', link: '/courses' },
    },
    {
        id: 3,
        eyebrow: 'Expert instructors',
        title: 'Learn from the Best in the Field',
        subtitle: 'Our instructors are active professionals bringing real-world experience to every lesson.',
        bg: 'linear-gradient(135deg, #0f0a1e 0%, #1a0d3a 50%, #100a28 100%)',
        cta: { label: 'Meet instructors', link: '#instructors' },
    },
    {
        id: 4,
        eyebrow: 'Your career, your pace',
        title: 'Build Skills That Get You Hired',
        subtitle: 'Practical, project-based courses designed to level up your career at your own pace.',
        bg: 'linear-gradient(135deg, #091a10 0%, #0d3020 50%, #071510 100%)',
        cta: { label: 'Start for free', link: user.value ? '/dashboard' : '/register' },
    },
];

const activeSlide = ref(0);
let timer: ReturnType<typeof setInterval> | null = null;

const goTo = (i: number) => { activeSlide.value = i; resetTimer(); };
const next = () => goTo((activeSlide.value + 1) % slides.length);
const prev = () => goTo((activeSlide.value - 1 + slides.length) % slides.length);
const resetTimer = () => {
    if (timer) clearInterval(timer);
    timer = setInterval(next, 5000);
};

// ── Stats ─────────────────────────────────────────────────────────────────

const stats = [
    { value: '500+', label: 'Courses available' },
    { value: '12k+', label: 'Active learners' },
    { value: '80+',  label: 'Expert instructors' },
];

// ── Data ──────────────────────────────────────────────────────────────────

const popular = ref<Course[]>([]);
const newest = ref<Course[]>([]);
const instructors = ref<{ id: number; name: string; email: string; courses_count: number; course_reviews_avg_rating: string | null }[]>([]);

const loadingPopular = ref(true);
const loadingNewest = ref(true);
const loadingInstructors = ref(true);

onMounted(() => {
    resetTimer();

    axios.get('/api/courses/popular').then(({ data }) => {
        popular.value = data;
    }).finally(() => { loadingPopular.value = false; });

    axios.get('/api/courses/newest').then(({ data }) => {
        newest.value = data;
    }).finally(() => { loadingNewest.value = false; });

    axios.get('/api/courses/instructors').then(({ data }) => {
        instructors.value = data;
    }).finally(() => { loadingInstructors.value = false; });
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
});
</script>

<style scoped>
.slide-fade-enter-active,
.slide-fade-leave-active {
    transition: opacity 0.6s ease, transform 0.6s ease;
    position: absolute;
    inset: 0;
}
.slide-fade-enter-from {
    opacity: 0;
    transform: translateX(40px);
}
.slide-fade-leave-to {
    opacity: 0;
    transform: translateX(-40px);
}
</style>


