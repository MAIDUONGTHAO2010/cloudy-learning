<template>
    <div class="min-h-screen bg-slate-950 text-white">
        <Navbar />

        <div v-if="loading" class="mx-auto max-w-4xl px-6 py-20 text-center text-slate-500">
            {{ t('courses.loadingCourse') }}
        </div>

        <div v-else-if="!course" class="mx-auto max-w-4xl px-6 py-20 text-center">
            <p class="text-slate-400">{{ t('courses.notFound') }}</p>
            <RouterLink to="/courses" class="mt-4 inline-block text-blue-400 hover:underline">{{ t('courses.backToCourses') }}</RouterLink>
        </div>

        <main v-else class="mx-auto max-w-4xl px-6 py-10">
            <!-- Breadcrumb -->
            <nav class="mb-6 flex items-center gap-2 text-sm text-slate-500">
                <RouterLink to="/courses" class="hover:text-white transition">{{ t('nav.courses') }}</RouterLink>
                <span>/</span>
                <span class="text-slate-300">{{ course.title }}</span>
            </nav>

            <!-- Course header -->
            <div class="mb-8 rounded-2xl border border-white/10 bg-slate-900 overflow-hidden">
                <div v-if="course.thumbnail" class="aspect-video w-full overflow-hidden">
                    <img :src="course.thumbnail" :alt="course.title" class="h-full w-full object-cover" />
                </div>
                <div
                    v-else
                    class="flex aspect-video w-full items-center justify-center bg-gradient-to-br from-blue-900/30 to-slate-800"
                >
                    <span class="text-8xl font-bold text-blue-300/20">{{ course.title.charAt(0) }}</span>
                </div>

                <div class="p-6">
                    <div class="flex flex-wrap items-center gap-2 mb-3">
                        <span
                            v-if="course.category"
                            class="rounded-full bg-blue-900/50 px-3 py-1 text-xs font-medium text-blue-300"
                        >{{ course.category.name }}</span>
                        <span
                            v-for="tag in course.tags"
                            :key="tag.id"
                            class="rounded-full bg-slate-800 px-3 py-1 text-xs text-slate-400"
                        >{{ tag.name }}</span>
                    </div>

                    <h1 class="text-2xl font-semibold text-white">{{ course.title }}</h1>
                    <p class="mt-2 text-slate-400 text-sm">by {{ course.instructor?.name }}</p>

                    <div class="mt-4 flex flex-wrap gap-4 text-sm text-slate-400">
                        <span>{{ course.lessons_count }} {{ t('courses.lessons') }}</span>
                        <span v-if="course.reviews_avg_rating" class="text-amber-400">
                            ★ {{ Number(course.reviews_avg_rating).toFixed(1) }}
                        </span>
                    </div>

                    <p v-if="course.description" class="mt-4 text-slate-300 leading-7">{{ course.description }}</p>
                </div>
            </div>

            <!-- Lessons list -->
            <h2 class="mb-4 text-lg font-semibold text-white">{{ t('courses.lessonsTitle', { count: course.lessons?.length ?? 0 }) }}</h2>

            <div v-if="!course.lessons?.length" class="rounded-2xl border border-white/10 bg-slate-900 p-8 text-center text-slate-500">
                {{ t('courses.noLessons') }}
            </div>

            <div v-else class="space-y-2">
                <div
                    v-for="(lesson, index) in course.lessons"
                    :key="lesson.id"
                    class="flex items-center gap-4 rounded-xl border border-white/10 bg-slate-900 px-5 py-4 transition hover:border-white/20"
                >
                    <div class="grid h-8 w-8 flex-none place-items-center rounded-full bg-slate-800 text-sm font-semibold text-blue-400">
                        {{ index + 1 }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="truncate text-sm font-medium text-white">{{ lesson.title }}</p>
                        <p v-if="lesson.description" class="mt-0.5 truncate text-xs text-slate-500">{{ lesson.description }}</p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute } from 'vue-router';
import axios from 'axios';
import Navbar from '../components/Navbar.vue';

const { t } = useI18n();
const route = useRoute();

interface Lesson {
    id: number;
    title: string;
    description: string | null;
}

interface Course {
    id: number;
    title: string;
    slug: string;
    description: string | null;
    thumbnail: string | null;
    lessons_count: number;
    reviews_avg_rating: string | null;
    instructor: { id: number; name: string } | null;
    category: { id: number; name: string } | null;
    tags: { id: number; name: string }[];
    lessons: Lesson[];
}

const course = ref<Course | null>(null);
const loading = ref(true);

onMounted(async () => {
    try {
        const { data } = await axios.get<Course>(`/api/courses/${route.params.slug}`);
        course.value = data;
    } catch {
        course.value = null;
    } finally {
        loading.value = false;
    }
});
</script>
