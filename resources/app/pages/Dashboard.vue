<template>
    <div class="min-h-screen bg-gray-50 text-gray-900">
        <Navbar />

        <main class="mx-auto max-w-7xl px-6 py-10">
            <!-- Welcome header -->
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">
                        {{ t('dashboard.welcomeBack') }} <span class="text-orange-500">{{ user?.name }}</span> 👋
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ isInstructor ? t('dashboard.instructorSubtitle') : t('dashboard.myCoursesSubtitle') }}
                    </p>
                </div>
                <RouterLink
                    to="/courses"
                    class="rounded-xl bg-[#1a1a4e] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#0f2460]"
                >
                    {{ t('dashboard.browseAll') }}
                </RouterLink>
            </div>

            <!-- Stats row -->
            <div class="mb-10 grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-gray-200 bg-white p-5">
                    <p class="text-xs uppercase tracking-widest text-gray-500">
                        {{ isInstructor ? t('dashboard.pendingRequests') : t('dashboard.myCourses') }}
                    </p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ isInstructor ? pendingRequests.length : total }}</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-5">
                    <p class="text-xs uppercase tracking-widest text-gray-500">{{ t('dashboard.yourAccount') }}</p>
                    <p class="mt-2 text-sm text-gray-900 truncate">{{ user?.email }}</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-5">
                    <p class="text-xs uppercase tracking-widest text-gray-500">{{ t('dashboard.memberSince') }}</p>
                    <p class="mt-2 text-sm text-gray-900">{{ t('common.today') }}</p>
                </div>
            </div>

            <section v-if="isInstructor" class="mb-10">
                <h2 class="mb-4 text-lg font-semibold text-gray-900">{{ t('dashboard.pendingRequests') }}</h2>

                <div v-if="requestLoading" class="grid gap-4 lg:grid-cols-2">
                    <div v-for="n in 4" :key="n" class="h-48 animate-pulse rounded-2xl border border-gray-100 bg-gray-200"></div>
                </div>

                <div v-else-if="pendingRequests.length === 0" class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 py-12 text-center text-gray-400">
                    {{ t('dashboard.noPendingRequests') }}
                </div>

                <div v-else class="grid gap-4 lg:grid-cols-2">
                    <div
                        v-for="request in pendingRequests"
                        :key="`${request.course_id}-${request.user_id}`"
                        class="rounded-2xl border border-gray-200 bg-white p-5"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-lg font-semibold text-gray-900">{{ request.course_title }}</p>
                                <p class="mt-1 text-sm text-gray-700">{{ request.student_name }}</p>
                                <p class="text-xs text-gray-500">{{ request.student_email }}</p>
                            </div>
                            <span class="rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-semibold text-amber-700">
                                {{ t('courses.requestPending') }}
                            </span>
                        </div>

                        <p class="mt-3 text-xs text-gray-400">{{ new Date(request.requested_at).toLocaleString() }}</p>

                        <textarea
                            v-model="requestNotes[`${request.course_id}-${request.user_id}`]"
                            :placeholder="t('dashboard.cancelReasonPlaceholder')"
                            rows="3"
                            class="mt-4 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-orange-400"
                        ></textarea>

                        <div class="mt-4 flex flex-wrap gap-3">
                            <button
                                @click="reviewRequest(request, APPROVED_STATUS)"
                                :disabled="requestActionLoading === `${request.course_id}-${request.user_id}`"
                                class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-50"
                            >
                                {{ t('dashboard.reviewApprove') }}
                            </button>
                            <button
                                @click="reviewRequest(request, CANCELED_STATUS)"
                                :disabled="requestActionLoading === `${request.course_id}-${request.user_id}`"
                                class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700 disabled:opacity-50"
                            >
                                {{ t('dashboard.reviewCancel') }}
                            </button>
                        </div>
                    </div>
                </div>

                <p v-if="reviewFeedback" class="mt-4 text-sm text-emerald-600">{{ reviewFeedback }}</p>
            </section>

            <template v-else>
                <h2 class="mb-4 text-lg font-semibold text-gray-900">{{ t('dashboard.myCourses') }}</h2>

                <div v-if="loading" class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <div
                        v-for="n in 8"
                        :key="n"
                        class="h-56 animate-pulse rounded-2xl border border-gray-100 bg-gray-200"
                    />
                </div>

                <div v-else-if="courses.length === 0" class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 py-20 text-center text-gray-400">
                    {{ t('dashboard.noEnrolledCourses') }}
                </div>

                <div v-else class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <RouterLink
                    v-for="course in courses"
                    :key="course.id"
                    :to="`/courses/${course.slug}`"
                    class="group flex flex-col rounded-2xl border border-gray-200 bg-white overflow-hidden transition hover:border-orange-400/50 hover:shadow-lg hover:shadow-orange-100"
                >
                    <!-- Thumbnail -->
                    <div class="aspect-video w-full bg-gray-100 overflow-hidden">
                        <img
                            v-if="course.thumbnail"
                            :src="course.thumbnail"
                            :alt="course.title"
                            class="h-full w-full object-cover transition group-hover:scale-105"
                        />
                        <div
                            v-else
                            class="flex h-full w-full items-center justify-center bg-gradient-to-br from-orange-100 to-gray-100"
                        >
                            <span class="text-4xl font-bold text-orange-300/30">{{ course.title.charAt(0) }}</span>
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="flex flex-1 flex-col p-4">
                        <div class="mb-1 flex items-center justify-between gap-2">
                            <span
                                v-if="course.category"
                                class="text-xs font-medium text-orange-500"
                            >{{ course.category.name }}</span>
                            <span
                                class="rounded-full px-2.5 py-1 text-[10px] font-semibold"
                                :class="statusBadgeClass(course.enrollment_status)"
                            >
                                {{ statusLabel(course.enrollment_status) }}
                            </span>
                        </div>
                        <h3 class="line-clamp-2 text-sm font-semibold text-gray-900 leading-snug">{{ course.title }}</h3>
                        <p class="mt-1 text-xs text-gray-500">by {{ course.instructor?.name }}</p>
                        <p v-if="course.enrollment_status === CANCELED_STATUS && course.enrollment_note" class="mt-1 line-clamp-2 text-[11px] text-rose-600">
                            {{ t('courses.cancelReason') }}: {{ course.enrollment_note }}
                        </p>
                        <p v-else-if="course.enrollment_status !== APPROVED_STATUS" class="mt-1 line-clamp-2 text-[11px] text-amber-600">
                            {{ t('courses.requestPendingMessage') }}
                        </p>

                        <div class="mt-auto flex items-center justify-between pt-3 border-t border-gray-100">
                            <span class="text-xs text-gray-400">{{ course.lessons_count }} lessons</span>
                            <span v-if="course.reviews_avg_rating" class="text-xs text-amber-400">
                                ★ {{ Number(course.reviews_avg_rating).toFixed(1) }}
                            </span>
                        </div>

                        <!-- Progress bar (approved enrollments) -->
                        <div v-if="course.enrollment_status === APPROVED_STATUS && course.lessons_count > 0" class="mt-3">
                            <div class="mb-1 flex items-center justify-between text-xs">
                                <span class="text-gray-400">Progress</span>
                                <span class="font-medium text-gray-600">{{ course.progress ?? 0 }}%</span>
                            </div>
                            <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-100">
                                <div
                                    class="h-full rounded-full bg-orange-500 transition-all duration-300"
                                    :style="{ width: `${course.progress ?? 0}%` }"
                                />
                            </div>
                        </div>
                    </div>
                </RouterLink>
            </div>

                <!-- Pagination -->
                <div v-if="lastPage > 1" class="mt-8 flex items-center justify-center gap-4">
                <button
                    :disabled="currentPage <= 1"
                    @click="fetchPage(currentPage - 1)"
                    class="rounded-xl border border-gray-200 px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-100 disabled:opacity-30"
                >
                    {{ t('dashboard.previous') }}
                </button>
                <span class="text-sm text-gray-500">{{ t('dashboard.pageOf', { current: currentPage, total: lastPage }) }}</span>
                <button
                    :disabled="currentPage >= lastPage"
                    @click="fetchPage(currentPage + 1)"
                    class="rounded-xl border border-gray-200 px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-100 disabled:opacity-30"
                >
                    {{ t('dashboard.nextPage') }}
                </button>
                </div>
            </template>
        </main>
        <AppFooter />
    </div>
</template>

<script setup lang="ts">
import { computed, ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import axios from 'axios';
import { useAuth } from '../composables/useAuth';
import Navbar from '../components/Navbar.vue';
import AppFooter from '../components/Footer.vue';

const { t } = useI18n();
const { user } = useAuth();

interface Course {
    id: number;
    title: string;
    slug: string;
    thumbnail: string | null;
    lessons_count: number;
    reviews_avg_rating: string | null;
    enrollment_status?: number | null;
    enrollment_note?: string | null;
    progress?: number;
    instructor: { id: number; name: string } | null;
    category: { id: number; name: string } | null;
}

interface EnrollmentRequest {
    course_id: number;
    user_id: number;
    course_title: string;
    student_name: string;
    student_email: string;
    status: number;
    note: string | null;
    requested_at: string;
}

const APPROVED_STATUS = 2;
const CANCELED_STATUS = 3;

const isInstructor = computed(() => user.value?.role === 2);
const courses = ref<Course[]>([]);
const pendingRequests = ref<EnrollmentRequest[]>([]);
const requestNotes = ref<Record<string, string>>({});
const requestActionLoading = ref<string | null>(null);
const reviewFeedback = ref('');
const loading = ref(true);
const requestLoading = ref(true);
const currentPage = ref(1);
const lastPage = ref(1);
const total = ref(0);

const statusLabel = (status?: number | null) => {
    if (status === APPROVED_STATUS) return t('dashboard.enrolled');
    if (status === CANCELED_STATUS) return t('courses.enrollmentCanceled');
    return t('courses.requestPending');
};

const statusBadgeClass = (status?: number | null) => {
    if (status === APPROVED_STATUS) return 'bg-emerald-50 text-emerald-700';
    if (status === CANCELED_STATUS) return 'bg-rose-50 text-rose-700';
    return 'bg-amber-50 text-amber-700';
};

const fetchPage = async (page: number) => {
    loading.value = true;
    try {
        const { data } = await axios.get(`/api/my-courses?page=${page}`);
        courses.value = data.data;
        currentPage.value = data.current_page;
        lastPage.value = data.last_page;
        total.value = data.total;
    } finally {
        loading.value = false;
    }
};

const fetchPendingRequests = async () => {
    requestLoading.value = true;
    try {
        const { data } = await axios.get('/api/instructor/course-requests');
        pendingRequests.value = data;
    } finally {
        requestLoading.value = false;
    }
};

const reviewRequest = async (request: EnrollmentRequest, status: number) => {
    const key = `${request.course_id}-${request.user_id}`;
    const note = requestNotes.value[key] ?? '';

    if (status === CANCELED_STATUS && !note.trim()) {
        reviewFeedback.value = t('dashboard.cancelReasonRequired');
        return;
    }

    requestActionLoading.value = key;
    reviewFeedback.value = '';

    try {
        const { data } = await axios.put(`/api/instructor/course-requests/${request.course_id}/${request.user_id}`, {
            status,
            note,
        });

        reviewFeedback.value = data.message;
        pendingRequests.value = pendingRequests.value.filter((item) => !(item.course_id === request.course_id && item.user_id === request.user_id));
    } catch (error: any) {
        reviewFeedback.value = error?.response?.data?.message ?? t('common.error');
    } finally {
        requestActionLoading.value = null;
    }
};

onMounted(() => {
    if (isInstructor.value) {
        fetchPendingRequests();
        loading.value = false;
        return;
    }

    fetchPage(1);
});
</script>
