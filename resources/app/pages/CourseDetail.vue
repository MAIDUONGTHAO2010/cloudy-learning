<template>
    <div class="min-h-screen bg-gray-50 text-gray-900">
        <Navbar />

        <div v-if="loading" class="mx-auto max-w-4xl px-6 py-20 text-center text-gray-400">
            {{ t('courses.loadingCourse') }}
        </div>

        <div v-else-if="!course" class="mx-auto max-w-4xl px-6 py-20 text-center">
            <p class="text-gray-400">{{ t('courses.notFound') }}</p>
            <RouterLink to="/courses" class="mt-4 inline-block text-orange-500 hover:underline">{{ t('courses.backToCourses') }}</RouterLink>
        </div>

        <main v-else class="mx-auto max-w-4xl px-6 py-10">
            <!-- Breadcrumb -->
            <nav class="mb-6 flex items-center gap-2 text-sm text-gray-400">
                <RouterLink to="/courses" class="hover:text-gray-900 transition">{{ t('nav.courses') }}</RouterLink>
                <span>/</span>
                <span class="text-gray-600">{{ course.title }}</span>
            </nav>

            <!-- Course header -->
            <div class="mb-8 rounded-2xl border border-gray-200 bg-white overflow-hidden">
                <div v-if="course.thumbnail" class="aspect-video w-full overflow-hidden">
                    <img :src="course.thumbnail" :alt="course.title" class="h-full w-full object-cover" />
                </div>
                <div
                    v-else
                    class="flex aspect-video w-full items-center justify-center bg-gradient-to-br from-orange-100 to-gray-100"
                >
                    <span class="text-8xl font-bold text-orange-300/20">{{ course.title.charAt(0) }}</span>
                </div>

                <div class="p-6">
                    <div class="flex flex-wrap items-center gap-2 mb-3">
                        <span
                            v-if="course.category"
                            class="rounded-full bg-[#1a1a4e] px-3 py-1 text-xs font-medium text-white"
                        >{{ course.category.name }}</span>
                        <span
                            v-for="tag in course.tags"
                            :key="tag.id"
                            class="rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-600"
                        >{{ tag.name }}</span>
                    </div>

                    <h1 class="text-2xl font-semibold text-gray-900">{{ course.title }}</h1>
                    <p class="mt-2 text-gray-500 text-sm">by {{ course.instructor?.name }}</p>

                    <div class="mt-4 flex flex-wrap gap-4 text-sm text-gray-500">
                        <span>{{ course.lessons_count }} {{ t('courses.lessons') }}</span>
                        <span v-if="course.reviews_avg_rating" class="text-amber-400">
                            ★ {{ Number(course.reviews_avg_rating).toFixed(1) }}
                        </span>
                    </div>

                    <p v-if="course.description" class="mt-4 text-gray-600 leading-7">{{ course.description }}</p>

                    <div class="mt-6 flex flex-wrap items-center gap-3">
                        <RouterLink
                            v-if="course.lessons?.length"
                            :to="`/learn/${course.slug}`"
                            class="rounded-xl bg-[#1a1a4e] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#0f2460]"
                        >
                            {{ course.can_access_full_course ? t('courses.startLearning') : t('courses.previewFirstLesson') }}
                        </RouterLink>

                        <button
                            v-if="user && course.enrollment_status_label !== 'request' && !course.can_access_full_course"
                            @click="enrollCourse"
                            :disabled="enrolling"
                            class="rounded-xl bg-orange-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-orange-600 disabled:opacity-60"
                        >
                            {{ enrolling ? t('courses.enrolling') : (course.enrollment_status_label === 'canceled' ? t('courses.reapplyEnrollment') : t('courses.enrollNow')) }}
                        </button>

                        <RouterLink
                            v-if="course.can_access_full_course"
                            to="/dashboard"
                            class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-50"
                        >
                            {{ t('courses.goToMyCourses') }}
                        </RouterLink>

                        <RouterLink
                            v-else-if="!user"
                            to="/login"
                            class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-50"
                        >
                            {{ t('nav.signIn') }}
                        </RouterLink>

                        <span
                            v-if="course.enrollment_status_label === 'approved'"
                            class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700"
                        >
                            {{ t('courses.enrolled') }}
                        </span>
                        <span
                            v-else-if="course.enrollment_status_label === 'request'"
                            class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700"
                        >
                            {{ t('courses.requestPending') }}
                        </span>
                        <span
                            v-else-if="course.enrollment_status_label === 'canceled'"
                            class="rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700"
                        >
                            {{ t('courses.enrollmentCanceled') }}
                        </span>
                    </div>

                    <p v-if="course.enrollment_status_label === 'request'" class="mt-3 text-sm text-amber-600">
                        {{ t('courses.requestPendingMessage') }}
                    </p>
                    <p v-else-if="course.enrollment_status_label === 'canceled'" class="mt-3 text-sm text-rose-600">
                        {{ t('courses.cancelReason') }}: {{ course.enrollment_note || t('common.noData') }}
                    </p>
                    <p v-else-if="!course.can_access_full_course" class="mt-3 text-sm text-orange-500">
                        {{ t('courses.firstLessonFree') }}
                    </p>

                    <p v-if="enrollmentMessage" class="mt-3 text-sm text-emerald-600">
                        {{ enrollmentMessage }}
                    </p>
                </div>
            </div>

            <!-- Lessons list -->
            <h2 class="mb-4 text-lg font-semibold text-gray-900">{{ t('courses.lessonsTitle', { count: course.lessons?.length ?? 0 }) }}</h2>

            <div v-if="!course.lessons?.length" class="rounded-2xl border border-gray-200 bg-gray-50 p-8 text-center text-gray-400">
                {{ t('courses.noLessons') }}
            </div>

            <div v-else class="space-y-2">
                <div
                    v-for="(lesson, index) in course.lessons"
                    :key="lesson.id"
                    class="flex items-center gap-4 rounded-xl border border-gray-200 bg-white px-5 py-4 transition hover:border-gray-300"
                >
                    <div class="grid h-8 w-8 flex-none place-items-center rounded-full bg-orange-50 text-sm font-semibold text-orange-500">
                        {{ index + 1 }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="truncate text-sm font-medium text-gray-900">{{ lesson.title }}</p>
                        <p v-if="lesson.description" class="mt-0.5 truncate text-xs text-gray-400">{{ lesson.description }}</p>
                    </div>
                    <span v-if="lesson.is_preview" class="rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-semibold text-emerald-700">
                        {{ t('courses.freePreview') }}
                    </span>
                    <span v-else-if="lesson.is_locked" class="rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-semibold text-amber-700">
                        {{ t('courses.enrollToUnlock') }}
                    </span>
                </div>
            </div>

            <!-- Reviews section -->
            <div class="mt-10">
                <h2 class="mb-4 text-lg font-semibold text-gray-900">
                    {{ t('courses.reviewsTitle') }}
                    <span v-if="reviewData.reviews_count" class="ml-2 text-sm font-normal text-gray-400">
                        ★ {{ reviewData.avg_rating.toFixed(1) }} · {{ reviewData.reviews_count }} {{ reviewData.reviews_count === 1 ? 'review' : 'reviews' }}
                    </span>
                </h2>

                <!-- Write / edit review form (enrolled + approved students only) -->
                <div v-if="course.can_access_full_course && user" class="mb-6 rounded-2xl border border-gray-200 bg-white p-5">
                    <h3 class="mb-3 text-sm font-semibold text-gray-800">
                        {{ myReview ? t('courses.editReview') : t('courses.writeReview') }}
                    </h3>

                    <!-- Star rating input -->
                    <div class="mb-3 flex items-center gap-1">
                        <button
                            v-for="star in 5"
                            :key="star"
                            type="button"
                            @click="reviewForm.rating = star"
                            class="text-2xl leading-none transition"
                            :class="star <= reviewForm.rating ? 'text-amber-400' : 'text-gray-300 hover:text-amber-300'"
                        >★</button>
                    </div>

                    <textarea
                        v-model="reviewForm.comment"
                        :placeholder="t('courses.commentPlaceholder')"
                        rows="3"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700 outline-none focus:border-orange-400 focus:ring-0 resize-none"
                    />

                    <div class="mt-3 flex flex-wrap items-center gap-3">
                        <button
                            @click="submitReview"
                            :disabled="reviewSubmitting || reviewForm.rating === 0"
                            class="rounded-xl bg-orange-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-orange-600 disabled:opacity-60"
                        >
                            {{ reviewSubmitting ? t('courses.submittingReview') : (myReview ? t('courses.updateReview') : t('courses.submitReview')) }}
                        </button>
                        <button
                            v-if="myReview"
                            @click="deleteReview"
                            :disabled="reviewSubmitting"
                            class="rounded-xl border border-rose-200 px-4 py-2 text-sm font-semibold text-rose-600 transition hover:bg-rose-50 disabled:opacity-60"
                        >{{ t('courses.deleteReview') }}</button>
                    </div>

                    <p v-if="reviewMessage" class="mt-3 text-sm" :class="reviewMessageError ? 'text-rose-600' : 'text-emerald-600'">
                        {{ reviewMessage }}
                    </p>
                </div>

                <!-- Reviews list -->
                <div v-if="reviewData.reviews_count === 0" class="rounded-2xl border border-gray-200 bg-gray-50 p-8 text-center text-gray-400">
                    {{ t('courses.noReviews') }}
                    <span v-if="course.can_access_full_course && user"> {{ t('courses.beFirst') }}</span>
                </div>

                <div v-else class="space-y-4">
                    <div
                        v-for="review in reviewData.reviews"
                        :key="review.id"
                        class="rounded-xl border border-gray-200 bg-white px-5 py-4"
                    >
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <div class="grid h-8 w-8 flex-none place-items-center rounded-full bg-orange-100 text-sm font-semibold text-orange-600">
                                    {{ review.user?.name?.charAt(0)?.toUpperCase() ?? '?' }}
                                </div>
                                <span class="text-sm font-medium text-gray-800">{{ review.user?.name }}</span>
                            </div>
                            <span class="text-amber-400 text-sm font-semibold">{{ '★'.repeat(review.rating) }}<span class="text-gray-200">{{ '★'.repeat(5 - review.rating) }}</span></span>
                        </div>
                        <p v-if="review.comment" class="mt-2 text-sm text-gray-600 leading-6">{{ review.comment }}</p>
                    </div>
                </div>
            </div>
        </main>
        <AppFooter />
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, reactive } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute } from 'vue-router';
import axios from 'axios';
import Navbar from '../components/Navbar.vue';
import AppFooter from '../components/Footer.vue';
import { useAuth } from '../composables/useAuth';

const { t } = useI18n();
const { user } = useAuth();
const route = useRoute();

interface Lesson {
    id: number;
    title: string;
    description: string | null;
    is_locked?: boolean;
    is_preview?: boolean;
}

interface ReviewUser {
    id: number;
    name: string;
    email: string;
}

interface Review {
    id: number;
    rating: number;
    comment: string | null;
    created_at: string;
    user: ReviewUser | null;
}

interface ReviewData {
    reviews: Review[];
    avg_rating: number;
    reviews_count: number;
}

interface MyReview {
    id: number;
    rating: number;
    comment: string | null;
}

interface Course {
    id: number;
    title: string;
    slug: string;
    description: string | null;
    thumbnail: string | null;
    lessons_count: number;
    reviews_avg_rating: string | null;
    is_enrolled?: boolean;
    can_access_full_course?: boolean;
    enrollment_status?: number | null;
    enrollment_status_label?: string | null;
    enrollment_note?: string | null;
    my_review?: MyReview | null;
    instructor: { id: number; name: string } | null;
    category: { id: number; name: string } | null;
    tags: { id: number; name: string }[];
    lessons: Lesson[];
}

const course = ref<Course | null>(null);
const loading = ref(true);
const enrolling = ref(false);
const enrollmentMessage = ref('');

// Reviews state
const reviewData = reactive<ReviewData>({ reviews: [], avg_rating: 0, reviews_count: 0 });
const myReview = ref<MyReview | null>(null);
const reviewForm = reactive({ rating: 0, comment: '' });
const reviewSubmitting = ref(false);
const reviewMessage = ref('');
const reviewMessageError = ref(false);

const enrollCourse = async () => {
    if (!course.value) {
        return;
    }

    enrolling.value = true;
    enrollmentMessage.value = '';

    try {
        const { data } = await axios.post(`/api/courses/${course.value.slug}/enroll`);
        course.value = data.course;
        enrollmentMessage.value = data.message;
    } catch (error: any) {
        enrollmentMessage.value = error?.response?.data?.message ?? 'Unable to enroll in this course right now.';
    } finally {
        enrolling.value = false;
    }
};

const loadReviews = async (courseId: number) => {
    try {
        const { data } = await axios.get<ReviewData>(`/api/courses/${courseId}/reviews`);
        reviewData.reviews = data.reviews;
        reviewData.avg_rating = data.avg_rating;
        reviewData.reviews_count = data.reviews_count;
    } catch {
        // ignore
    }
};

const submitReview = async () => {
    if (!course.value || reviewForm.rating === 0) return;

    reviewSubmitting.value = true;
    reviewMessage.value = '';
    reviewMessageError.value = false;

    try {
        if (myReview.value) {
            const { data } = await axios.put(`/api/courses/${course.value.id}/reviews/${myReview.value.id}`, {
                rating: reviewForm.rating,
                comment: reviewForm.comment || null,
            });
            myReview.value = { id: data.id, rating: data.rating, comment: data.comment };
            reviewMessage.value = t('courses.reviewUpdated');
        } else {
            const { data } = await axios.post(`/api/courses/${course.value.id}/reviews`, {
                rating: reviewForm.rating,
                comment: reviewForm.comment || null,
            });
            myReview.value = { id: data.id, rating: data.rating, comment: data.comment };
            reviewMessage.value = t('courses.reviewSuccess');
        }
        await loadReviews(course.value.id);
    } catch (error: any) {
        reviewMessage.value = error?.response?.data?.message ?? 'Something went wrong.';
        reviewMessageError.value = true;
    } finally {
        reviewSubmitting.value = false;
    }
};

const deleteReview = async () => {
    if (!course.value || !myReview.value) return;

    reviewSubmitting.value = true;
    reviewMessage.value = '';
    reviewMessageError.value = false;

    try {
        await axios.delete(`/api/courses/${course.value.id}/reviews/${myReview.value.id}`);
        myReview.value = null;
        reviewForm.rating = 0;
        reviewForm.comment = '';
        reviewMessage.value = t('courses.reviewDeleted');
        await loadReviews(course.value.id);
    } catch (error: any) {
        reviewMessage.value = error?.response?.data?.message ?? 'Something went wrong.';
        reviewMessageError.value = true;
    } finally {
        reviewSubmitting.value = false;
    }
};

onMounted(async () => {
    try {
        const { data } = await axios.get<Course>(`/api/courses/${route.params.slug}`);
        course.value = data;

        if (data.my_review) {
            myReview.value = data.my_review;
            reviewForm.rating = data.my_review.rating;
            reviewForm.comment = data.my_review.comment ?? '';
        }

        await loadReviews(data.id);
    } catch {
        course.value = null;
    } finally {
        loading.value = false;
    }
});
</script>
