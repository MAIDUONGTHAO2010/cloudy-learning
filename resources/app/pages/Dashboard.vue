<template>
    <div class="min-h-screen bg-slate-950 text-white">
        <Navbar />

        <main class="mx-auto max-w-7xl px-6 py-10">
            <!-- Welcome header -->
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">
                        {{ t('dashboard.welcomeBack') }} <span class="text-blue-400">{{ user?.name }}</span> 👋
                    </h1>
                    <p class="mt-1 text-sm text-slate-400">{{ t('dashboard.subtitle') }}</p>
                </div>
                <RouterLink
                    to="/courses"
                    class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700"
                >
                    {{ t('dashboard.browseAll') }}
                </RouterLink>
            </div>

            <!-- Stats row -->
            <div class="mb-10 grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-white/10 bg-slate-900 p-5">
                    <p class="text-xs uppercase tracking-widest text-slate-500">{{ t('dashboard.availableCourses') }}</p>
                    <p class="mt-2 text-3xl font-bold text-white">{{ total }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-slate-900 p-5">
                    <p class="text-xs uppercase tracking-widest text-slate-500">{{ t('dashboard.yourAccount') }}</p>
                    <p class="mt-2 text-sm text-white truncate">{{ user?.email }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-slate-900 p-5">
                    <p class="text-xs uppercase tracking-widest text-slate-500">{{ t('dashboard.memberSince') }}</p>
                    <p class="mt-2 text-sm text-white">{{ t('common.today') }}</p>
                </div>
            </div>

            <!-- Course grid -->
            <h2 class="mb-4 text-lg font-semibold text-white">{{ t('dashboard.availableCourses') }}</h2>

            <div v-if="loading" class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <div
                    v-for="n in 8"
                    :key="n"
                    class="h-56 animate-pulse rounded-2xl border border-white/5 bg-slate-900"
                />
            </div>

            <div v-else-if="courses.length === 0" class="py-20 text-center text-slate-500">
                {{ t('dashboard.noCourses') }}
            </div>

            <div v-else class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <RouterLink
                    v-for="course in courses"
                    :key="course.id"
                    :to="`/courses/${course.slug}`"
                    class="group flex flex-col rounded-2xl border border-white/10 bg-slate-900 overflow-hidden transition hover:border-blue-500/50 hover:shadow-lg hover:shadow-blue-900/20"
                >
                    <!-- Thumbnail -->
                    <div class="aspect-video w-full bg-slate-800 overflow-hidden">
                        <img
                            v-if="course.thumbnail"
                            :src="course.thumbnail"
                            :alt="course.title"
                            class="h-full w-full object-cover transition group-hover:scale-105"
                        />
                        <div
                            v-else
                            class="flex h-full w-full items-center justify-center bg-gradient-to-br from-blue-900/40 to-slate-800"
                        >
                            <span class="text-4xl font-bold text-blue-300/30">{{ course.title.charAt(0) }}</span>
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="flex flex-1 flex-col p-4">
                        <span
                            v-if="course.category"
                            class="mb-1 text-xs font-medium text-blue-400"
                        >{{ course.category.name }}</span>
                        <h3 class="line-clamp-2 text-sm font-semibold text-white leading-snug">{{ course.title }}</h3>
                        <p class="mt-1 text-xs text-slate-500">by {{ course.instructor?.name }}</p>

                        <div class="mt-auto flex items-center justify-between pt-3 border-t border-white/5">
                            <span class="text-xs text-slate-400">{{ course.lessons_count }} lessons</span>
                            <span v-if="course.reviews_avg_rating" class="text-xs text-amber-400">
                                ★ {{ Number(course.reviews_avg_rating).toFixed(1) }}
                            </span>
                        </div>
                    </div>
                </RouterLink>
            </div>

            <!-- Pagination -->
            <div v-if="lastPage > 1" class="mt-8 flex items-center justify-center gap-4">
                <button
                    :disabled="currentPage <= 1"
                    @click="fetchPage(currentPage - 1)"
                    class="rounded-xl border border-white/10 px-4 py-2 text-sm text-slate-300 transition hover:bg-white/5 disabled:opacity-30"
                >
                    {{ t('dashboard.previous') }}
                </button>
                <span class="text-sm text-slate-400">{{ t('dashboard.pageOf', { current: currentPage, total: lastPage }) }}</span>
                <button
                    :disabled="currentPage >= lastPage"
                    @click="fetchPage(currentPage + 1)"
                    class="rounded-xl border border-white/10 px-4 py-2 text-sm text-slate-300 transition hover:bg-white/5 disabled:opacity-30"
                >
                    {{ t('dashboard.nextPage') }}
                </button>
            </div>
        </main>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import axios from 'axios';
import { useAuth } from '../composables/useAuth';
import Navbar from '../components/Navbar.vue';

const { t } = useI18n();
const { user } = useAuth();

interface Course {
    id: number;
    title: string;
    slug: string;
    thumbnail: string | null;
    lessons_count: number;
    reviews_avg_rating: string | null;
    instructor: { id: number; name: string } | null;
    category: { id: number; name: string } | null;
}

const courses = ref<Course[]>([]);
const loading = ref(true);
const currentPage = ref(1);
const lastPage = ref(1);
const total = ref(0);

const fetchPage = async (page: number) => {
    loading.value = true;
    try {
        const { data } = await axios.get(`/api/courses?page=${page}`);
        courses.value = data.data;
        currentPage.value = data.current_page;
        lastPage.value = data.last_page;
        total.value = data.total;
    } finally {
        loading.value = false;
    }
};

onMounted(() => fetchPage(1));
</script>
