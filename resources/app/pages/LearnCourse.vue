<template>
    <div class="min-h-screen bg-gray-50 text-gray-900">
        <Navbar />

        <div v-if="loading" class="mx-auto max-w-6xl px-6 py-20 text-center text-gray-500">
            {{ t('courses.loadingCourse') }}
        </div>

        <div v-else-if="!course" class="mx-auto max-w-4xl px-6 py-20 text-center">
            <p class="text-gray-400">{{ t('courses.notFound') }}</p>
            <RouterLink to="/courses" class="mt-4 inline-block text-orange-500 hover:underline">{{ t('courses.backToCourses') }}</RouterLink>
        </div>

        <main v-else class="mx-auto max-w-7xl px-6 py-10">
            <!-- Header -->
            <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <RouterLink to="/courses" class="text-sm text-orange-500 transition hover:underline">{{ t('courses.backToCourses') }}</RouterLink>
                    <h1 class="mt-2 text-3xl font-semibold text-gray-900">{{ course.title }}</h1>
                    <p class="mt-2 text-sm text-gray-500">{{ course.lessons?.length ?? 0 }} {{ t('courses.lessons') }}</p>
                </div>

                <!-- Course progress bar (approved students only) -->
                <div v-if="courseProgress && course.can_access_full_course" class="min-w-[220px]">
                    <div class="mb-1 flex items-center justify-between text-sm">
                        <span class="font-medium text-gray-700">Tiến độ khoá học</span>
                        <span class="font-semibold text-orange-500">{{ courseProgress.percentage }}%</span>
                    </div>
                    <div class="h-2.5 w-full overflow-hidden rounded-full bg-gray-200">
                        <div
                            class="h-full rounded-full bg-orange-500 transition-all duration-500"
                            :style="{ width: `${courseProgress.percentage}%` }"
                        />
                    </div>
                    <p class="mt-1 text-xs text-gray-400">{{ courseProgress.completed }}/{{ courseProgress.total }} bài học hoàn thành</p>
                </div>
            </div>

            <!-- Enrollment banner -->
            <div v-if="!course.can_access_full_course" class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                <p v-if="course.enrollment_status_label === 'request'">{{ t('courses.requestPendingMessage') }}</p>
                <p v-else-if="course.enrollment_status_label === 'canceled'">
                    {{ t('courses.cancelReason') }}: {{ course.enrollment_note || t('common.noData') }}
                </p>
                <p v-else>{{ t('courses.firstLessonFree') }}</p>

                <div class="mt-3 flex flex-wrap gap-3">
                    <RouterLink
                        v-if="user"
                        :to="`/courses/${course.slug}`"
                        class="rounded-xl bg-orange-500 px-5 py-2 text-sm font-semibold text-white hover:bg-orange-600"
                    >
                        {{ t('courses.enrollNow') }}
                    </RouterLink>
                    <RouterLink
                        v-else
                        to="/login"
                        class="rounded-xl bg-orange-500 px-5 py-2 text-sm font-semibold text-white hover:bg-orange-600"
                    >
                        {{ t('nav.signIn') }}
                    </RouterLink>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
                <!-- Sidebar: lesson list -->
                <aside class="rounded-2xl border border-gray-200 bg-white p-4">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-widest text-gray-500">{{ t('courses.lessons') }}</h2>
                    <div v-if="!course.lessons?.length" class="rounded-xl bg-gray-100 p-4 text-sm text-gray-400">
                        {{ t('courses.noLessons') }}
                    </div>
                    <div v-else class="space-y-2">
                        <button
                            v-for="(lesson, index) in course.lessons"
                            :key="lesson.id"
                            type="button"
                            @click="selectLesson(lesson)"
                            class="w-full rounded-xl border px-3 py-3 text-left transition"
                            :class="selectedLesson?.id === lesson.id
                                ? 'border-[#1a1a4e] bg-[#1a1a4e]/5 text-[#1a1a4e]'
                                : lesson.is_locked
                                    ? 'border-amber-200 bg-amber-50 text-amber-700 hover:border-amber-300'
                                    : 'border-gray-200 bg-gray-50 text-gray-700 hover:border-gray-300'"
                        >
                            <div class="flex items-start gap-3">
                                <span
                                    class="mt-0.5 grid h-6 w-6 shrink-0 place-items-center rounded-full text-xs font-semibold"
                                    :class="lessonProgressMap[lesson.id]?.lesson_completed
                                        ? 'bg-emerald-500 text-white'
                                        : 'bg-gray-200 text-gray-700'"
                                >
                                    <svg v-if="lessonProgressMap[lesson.id]?.lesson_completed" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span v-else>{{ index + 1 }}</span>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="truncate text-sm font-medium">{{ lesson.title }}</p>
                                        <span v-if="lesson.is_preview" class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">
                                            {{ t('courses.freePreview') }}
                                        </span>
                                        <span v-else-if="lesson.is_locked" class="rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-700">
                                            {{ t('courses.enrollToUnlock') }}
                                        </span>
                                        <span v-else-if="lesson.has_quiz" class="rounded-full bg-violet-50 px-2 py-0.5 text-[10px] font-semibold text-violet-600">
                                            Quiz
                                        </span>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-400">{{ lesson.duration ? `${lesson.duration} min` : '—' }}</p>
                                </div>
                            </div>
                        </button>
                    </div>
                </aside>

                <!-- Main content -->
                <section class="space-y-4">
                    <!-- Video player -->
                    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
                        <div v-if="selectedLesson?.video_url" class="aspect-video bg-black">
                            <video
                                ref="videoRef"
                                :key="selectedLesson.id"
                                :src="selectedLesson.video_url"
                                controls
                                playsinline
                                class="h-full w-full"
                                @timeupdate="handleTimeUpdate"
                                @ended="handleVideoEnded"
                            />
                        </div>
                        <div v-else class="flex aspect-video items-center justify-center bg-gray-100 text-gray-400">
                            {{ t('courses.noVideoYet') }}
                        </div>

                        <!-- Video watch progress bar -->
                        <div v-if="selectedLesson?.video_url && course.can_access_full_course" class="border-t border-gray-100 px-4 py-2">
                            <div class="flex items-center gap-3">
                                <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-gray-200">
                                    <div
                                        class="h-full rounded-full transition-all duration-300"
                                        :class="localWatchPercent >= 80 ? 'bg-emerald-500' : 'bg-orange-400'"
                                        :style="{ width: `${localWatchPercent}%` }"
                                    />
                                </div>
                                <span class="shrink-0 text-xs text-gray-400">{{ localWatchPercent }}%</span>
                                <span v-if="localWatchPercent >= 80" class="shrink-0 text-xs font-semibold text-emerald-600">✓ Video hoàn thành</span>
                            </div>
                        </div>
                    </div>

                    <!-- Lesson info -->
                    <div class="rounded-2xl border border-gray-200 bg-white p-6">
                        <div v-if="selectedLesson">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h2 class="text-xl font-semibold text-gray-900">{{ selectedLesson.title }}</h2>
                                    <p class="mt-1 text-sm text-gray-400">{{ selectedLesson.duration ? `${selectedLesson.duration} min` : '' }}</p>
                                </div>
                                <div v-if="currentLessonProgress?.lesson_completed" class="flex items-center gap-1.5 rounded-xl bg-emerald-50 px-3 py-1.5 text-sm font-semibold text-emerald-700 shrink-0">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Bài học hoàn thành
                                </div>
                            </div>

                            <div class="mt-6 border-t border-gray-100 pt-6">
                                <h3 class="mb-3 text-sm font-semibold uppercase tracking-widest text-gray-500">{{ t('courses.lessonContent') }}</h3>
                                <p v-if="selectedLesson.content" class="whitespace-pre-line text-sm leading-7 text-gray-700">
                                    {{ selectedLesson.content }}
                                </p>
                                <p v-else class="text-sm text-gray-400">{{ t('common.noData') }}</p>
                            </div>
                        </div>
                        <div v-else class="text-sm text-gray-400">
                            {{ t('courses.chooseLesson') }}
                        </div>

                        <p v-if="accessMessage" class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                            {{ accessMessage }}
                        </p>
                    </div>

                    <!-- Quiz section -->
                    <div
                        v-if="selectedLesson && selectedLesson.has_quiz && course.can_access_full_course && !selectedLesson.is_locked"
                        class="rounded-2xl border border-violet-200 bg-white p-6"
                    >
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <h3 class="text-base font-semibold text-gray-900">
                                📝 Quiz
                                <span v-if="quizData" class="ml-2 text-sm font-normal text-gray-500">— {{ quizData.title }}</span>
                            </h3>
                            <span v-if="currentLessonProgress?.quiz_passed" class="flex items-center gap-1.5 rounded-xl bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                Đã vượt qua ({{ currentLessonProgress.quiz_score }}%)
                            </span>
                        </div>

                        <!-- Must watch video first -->
                        <div v-if="!currentLessonProgress?.video_completed" class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                            ⚠️ Xem ít nhất <strong>80% video</strong> để mở khoá quiz.
                        </div>

                        <!-- Loading -->
                        <div v-else-if="quizLoading" class="py-6 text-center text-sm text-gray-400">
                            Đang tải quiz…
                        </div>

                        <!-- Quiz loaded -->
                        <div v-else-if="quizData">
                            <p v-if="quizData.description" class="mb-4 text-sm text-gray-500">{{ quizData.description }}</p>

                            <!-- Attempt result -->
                            <div
                                v-if="quizResult"
                                class="mb-4 rounded-xl border px-4 py-3 text-sm"
                                :class="quizResult.passed ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-rose-200 bg-rose-50 text-rose-800'"
                            >
                                <p class="font-semibold">
                                    {{ quizResult.passed ? '✓ Vượt qua!' : '✗ Chưa đạt' }}
                                    — {{ quizResult.score }}% (cần {{ quizResult.passing_score }}%)
                                </p>
                                <p class="mt-0.5">Đúng {{ quizResult.correct }}/{{ quizResult.total }} câu</p>
                            </div>

                            <!-- Questions -->
                            <div class="space-y-5">
                                <div
                                    v-for="(question, qi) in quizData.questions"
                                    :key="question.id"
                                    class="rounded-xl border border-gray-100 bg-gray-50 p-4"
                                >
                                    <p class="mb-3 text-sm font-medium text-gray-800">
                                        <span class="mr-2 font-bold text-violet-600">{{ qi + 1 }}.</span>{{ question.content }}
                                    </p>
                                    <div class="space-y-2">
                                        <label
                                            v-for="option in question.options"
                                            :key="option.id"
                                            class="flex cursor-pointer items-center gap-3 rounded-lg border px-3 py-2.5 text-sm transition"
                                            :class="quizAnswers[question.id] === option.id
                                                ? 'border-violet-400 bg-violet-50 text-violet-900'
                                                : 'border-gray-200 bg-white hover:border-violet-300 hover:bg-violet-50/40'"
                                        >
                                            <input
                                                type="radio"
                                                :name="`q_${question.id}`"
                                                :value="option.id"
                                                v-model="quizAnswers[question.id]"
                                                class="accent-violet-600"
                                            />
                                            {{ option.content }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-5 flex items-center gap-4">
                                <button
                                    type="button"
                                    @click="submitQuiz"
                                    :disabled="quizSubmitting || !allQuestionsAnswered"
                                    class="rounded-xl bg-violet-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-violet-700 disabled:opacity-40"
                                >
                                    {{ quizSubmitting ? 'Đang nộp…' : 'Nộp bài' }}
                                </button>
                                <span v-if="!allQuestionsAnswered" class="text-xs text-gray-400">
                                    Vui lòng trả lời tất cả {{ quizData.questions.length }} câu
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation -->
                    <div class="flex items-center justify-between gap-3">
                        <button
                            type="button"
                            @click="goPrevious"
                            :disabled="currentIndex <= 0"
                            class="rounded-xl border border-gray-200 px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-50 disabled:opacity-40"
                        >
                            {{ t('common.back') }}
                        </button>
                        <button
                            type="button"
                            @click="goNext"
                            :disabled="currentIndex === -1 || currentIndex >= (course.lessons?.length ?? 0) - 1"
                            class="rounded-xl bg-[#1a1a4e] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#0f2460] disabled:opacity-40"
                        >
                            {{ t('common.next') }}
                        </button>
                    </div>
                </section>
            </div>
        </main>
        <AppFooter />
    </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute } from 'vue-router';
import axios from 'axios';
import Navbar from '../components/Navbar.vue';
import AppFooter from '../components/Footer.vue';
import { useAuth } from '../composables/useAuth';

// ── Types ────────────────────────────────────────────────────────────────────

type QuizOption = { id: number; label: number; content: string };
type QuizQuestion = { id: number; content: string; type: number; order: number; options: QuizOption[] };
type Quiz = {
    id: number;
    title: string;
    description: string | null;
    time_limit: number | null;
    passing_score: number;
    questions: QuizQuestion[];
};

type Lesson = {
    id: number;
    title: string;
    content: string | null;
    video_url: string | null;
    duration: number | null;
    has_quiz?: boolean;
    is_locked?: boolean;
    is_preview?: boolean;
};

type LessonProgress = {
    watch_percent: number;
    video_completed: boolean;
    lesson_completed: boolean;
    quiz_passed: boolean;
    quiz_score: number | null;
};

type CourseProgress = { completed: number; total: number; percentage: number };

type Course = {
    id: number;
    title: string;
    slug: string;
    can_access_full_course?: boolean;
    enrollment_status_label?: string | null;
    enrollment_note?: string | null;
    lessons: Lesson[];
};

type QuizResult = { score: number; passed: boolean; passing_score: number; correct: number; total: number };

// ── State ────────────────────────────────────────────────────────────────────

const { t } = useI18n();
const route = useRoute();
const { user } = useAuth();

const course            = ref<Course | null>(null);
const selectedLesson    = ref<Lesson | null>(null);
const loading           = ref(true);
const accessMessage     = ref('');

// Progress
const lessonProgressMap = ref<Record<number, LessonProgress>>({});
const courseProgress    = ref<CourseProgress | null>(null);
const localWatchPercent = ref(0);
const progressReported  = ref(false);

// Video
const videoRef = ref<HTMLVideoElement | null>(null);

// Quiz
const quizData       = ref<Quiz | null>(null);
const quizLoading    = ref(false);
const quizAnswers    = ref<Record<number, number>>({});
const quizSubmitting = ref(false);
const quizResult     = ref<QuizResult | null>(null);

// ── Computed ─────────────────────────────────────────────────────────────────

const currentIndex = computed(() => {
    if (!course.value || !selectedLesson.value) return -1;
    return course.value.lessons.findIndex((l) => l.id === selectedLesson.value?.id);
});

const currentLessonProgress = computed(() =>
    selectedLesson.value ? (lessonProgressMap.value[selectedLesson.value.id] ?? null) : null
);

const allQuestionsAnswered = computed(() => {
    if (!quizData.value) return false;
    return quizData.value.questions.every((q) => quizAnswers.value[q.id] !== undefined);
});

// ── Lesson selection ─────────────────────────────────────────────────────────

const selectLesson = (lesson: Lesson) => {
    if (lesson.is_locked) {
        if (course.value?.enrollment_status_label === 'request') {
            accessMessage.value = t('courses.requestPendingMessage');
            return;
        }
        if (course.value?.enrollment_status_label === 'canceled') {
            accessMessage.value = `${t('courses.cancelReason')}: ${course.value?.enrollment_note || t('common.noData')}`;
            return;
        }
        accessMessage.value = t('courses.unlockMoreLessons');
        return;
    }

    accessMessage.value     = '';
    selectedLesson.value    = lesson;
    localWatchPercent.value = 0;
    progressReported.value  = false;
    quizAnswers.value        = {};
    quizResult.value         = null;
    quizData.value           = null;

    if (user.value && course.value?.can_access_full_course) {
        loadLessonProgress(lesson.id);
    }
};

const goPrevious = () => {
    if (!course.value || currentIndex.value <= 0) return;
    selectLesson(course.value.lessons[currentIndex.value - 1]);
};

const goNext = () => {
    if (!course.value || currentIndex.value === -1 || currentIndex.value >= course.value.lessons.length - 1) return;
    selectLesson(course.value.lessons[currentIndex.value + 1]);
};

// ── Video progress ────────────────────────────────────────────────────────────

const handleTimeUpdate = (e: Event) => {
    const video = e.target as HTMLVideoElement;
    if (!video.duration || !selectedLesson.value) return;

    const percent = Math.floor((video.currentTime / video.duration) * 100);
    localWatchPercent.value = percent;

    if (percent >= 80 && !progressReported.value && user.value && course.value?.can_access_full_course) {
        progressReported.value = true;
        reportVideoProgress(selectedLesson.value.id, percent);
    }
};

const handleVideoEnded = () => {
    if (!selectedLesson.value || !user.value || !course.value?.can_access_full_course) return;
    localWatchPercent.value = 100;
    if (!progressReported.value) {
        progressReported.value = true;
        reportVideoProgress(selectedLesson.value.id, 100);
    }
};

const reportVideoProgress = async (lessonId: number, percent: number) => {
    try {
        const { data } = await axios.post<LessonProgress>(`/api/lessons/${lessonId}/progress`, { watch_percent: percent });
        lessonProgressMap.value[lessonId] = data;
        refreshCourseProgress();
    } catch {
        // non-critical
    }
};

// ── Lesson progress ───────────────────────────────────────────────────────────

const loadLessonProgress = async (lessonId: number) => {
    try {
        const { data } = await axios.get<LessonProgress>(`/api/lessons/${lessonId}/progress`);
        lessonProgressMap.value[lessonId] = data;

        if (data.video_completed) {
            localWatchPercent.value = 100;
            progressReported.value  = true;
        }

        if (data.video_completed && selectedLesson.value?.id === lessonId && selectedLesson.value.has_quiz && !quizData.value) {
            loadQuizData(lessonId);
        }
    } catch {
        // ignore
    }
};

const loadAllProgressForCourse = (lessons: Lesson[]) => {
    lessons.filter((l) => !l.is_locked).forEach((l) => loadLessonProgress(l.id));
};

// ── Quiz ──────────────────────────────────────────────────────────────────────

const loadQuizData = async (lessonId: number) => {
    if (quizLoading.value || quizData.value) return;
    quizLoading.value = true;
    try {
        const { data } = await axios.get<Quiz | null>(`/api/lessons/${lessonId}/quiz`);
        quizData.value = data;
    } catch {
        // ignore
    } finally {
        quizLoading.value = false;
    }
};

const submitQuiz = async () => {
    if (!selectedLesson.value || quizSubmitting.value) return;
    quizSubmitting.value = true;
    try {
        const { data } = await axios.post<QuizResult>(
            `/api/lessons/${selectedLesson.value.id}/quiz`,
            { answers: quizAnswers.value }
        );
        quizResult.value = data;
        await loadLessonProgress(selectedLesson.value.id);
        refreshCourseProgress();
    } catch {
        // ignore
    } finally {
        quizSubmitting.value = false;
    }
};

watch(currentLessonProgress, (progress) => {
    if (progress?.video_completed && selectedLesson.value?.has_quiz && !quizData.value) {
        loadQuizData(selectedLesson.value.id);
    }
});

// ── Course progress ───────────────────────────────────────────────────────────

const refreshCourseProgress = async () => {
    if (!course.value) return;
    try {
        const { data } = await axios.get<CourseProgress>(`/api/courses/${course.value.id}/progress`);
        courseProgress.value = data;
    } catch {
        // ignore
    }
};

// ── Bootstrap ─────────────────────────────────────────────────────────────────

onMounted(async () => {
    try {
        const { data } = await axios.get<Course>(`/api/courses/${route.params.slug}`);
        course.value         = data;
        selectedLesson.value = data.lessons?.[0] ?? null;

        if (user.value && data.can_access_full_course) {
            refreshCourseProgress();
            loadAllProgressForCourse(data.lessons ?? []);
        }
    } catch {
        course.value = null;
    } finally {
        loading.value = false;
    }
});
</script>
