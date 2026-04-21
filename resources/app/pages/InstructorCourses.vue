<template>
    <div class="min-h-screen bg-gray-50 text-gray-900">
        <Navbar />

        <main class="mx-auto max-w-7xl px-6 py-10">
            <!-- Header -->
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">My Courses</h1>
                    <p class="mt-1 text-sm text-gray-500">Manage your courses and students</p>
                </div>
                <RouterLink
                    to="/my-courses/create"
                    class="rounded-xl bg-[#1a1a4e] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#0f2460]"
                >
                    + New Course
                </RouterLink>
            </div>

            <!-- Course list -->
            <div v-if="loading" class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <div v-for="n in 6" :key="n" class="h-64 animate-pulse rounded-2xl border border-gray-100 bg-gray-200" />
            </div>

            <div v-else-if="courses.length === 0" class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 py-20 text-center text-gray-400">
                You haven't created any courses yet.
            </div>

            <div v-else class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="course in courses"
                    :key="course.id"
                    class="flex flex-col rounded-2xl border border-gray-200 bg-white overflow-hidden"
                >
                    <!-- Thumbnail -->
                    <div class="aspect-video w-full bg-gray-100 overflow-hidden">
                        <img
                            v-if="course.thumbnail"
                            :src="course.thumbnail"
                            :alt="course.title"
                            class="h-full w-full object-cover"
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
                            <span v-if="course.category" class="text-xs font-medium text-orange-500">{{ course.category.name }}</span>
                            <span
                                class="rounded-full px-2.5 py-1 text-[10px] font-semibold"
                                :class="course.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500'"
                            >
                                {{ course.is_active ? 'Active' : 'Draft' }}
                            </span>
                        </div>
                        <h3 class="line-clamp-2 text-sm font-semibold text-gray-900 leading-snug">{{ course.title }}</h3>

                        <div class="mt-2 flex items-center gap-4 text-xs text-gray-500">
                            <span>{{ course.lessons_count }} lessons</span>
                            <span>{{ course.enrolled_count }} students</span>
                            <span v-if="course.reviews_avg_rating" class="text-amber-400">★ {{ Number(course.reviews_avg_rating).toFixed(1) }}</span>
                        </div>

                        <!-- Actions -->
                        <div class="mt-auto flex flex-wrap gap-2 pt-4 border-t border-gray-100">
                            <RouterLink
                                :to="`/my-courses/${course.id}/edit`"
                                class="flex-1 rounded-xl border border-orange-400/50 px-3 py-1.5 text-center text-xs text-orange-500 transition hover:bg-orange-50"
                            >
                                Lessons
                            </RouterLink>
                            <button
                                @click="openStudents(course)"
                                class="flex-1 rounded-xl border border-gray-200 px-3 py-1.5 text-xs text-gray-600 transition hover:bg-gray-50"
                            >
                                Students
                            </button>
                            <button
                                @click="openEdit(course)"
                                class="flex-1 rounded-xl border border-gray-200 px-3 py-1.5 text-xs text-gray-600 transition hover:bg-gray-50"
                            >
                                Edit
                            </button>
                            <button
                                @click="confirmDelete(course)"
                                class="rounded-xl border border-rose-500/30 px-3 py-1.5 text-xs text-rose-400 transition hover:bg-rose-500/10"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <AppFooter />

        <!-- Course form modal -->
        <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
            <div class="w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl">
                <h2 class="mb-5 text-lg font-semibold text-gray-900">{{ editingCourse ? 'Edit Course' : 'New Course' }}</h2>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-xs text-gray-500">Title *</label>
                        <input
                            v-model="form.title"
                            type="text"
                            required
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 outline-none focus:border-orange-400"
                        />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs text-gray-500">Description</label>
                        <textarea
                            v-model="form.description"
                            rows="3"
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 outline-none focus:border-orange-400"
                        ></textarea>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs text-gray-500">Category</label>
                        <select
                            v-model="form.category_id"
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 outline-none focus:border-orange-400"
                        >
                            <option :value="null">— None —</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs text-gray-500">Tags (comma-separated)</label>
                        <input
                            v-model="tagsInput"
                            type="text"
                            placeholder="e.g. javascript, react, web"
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 outline-none focus:border-orange-400"
                        />
                    </div>

                    <!-- Thumbnail upload -->
                    <div>
                        <label class="mb-1 block text-xs text-gray-500">Thumbnail</label>
                        <div v-if="thumbnailPreviewUrl" class="mb-2">
                            <img :src="thumbnailPreviewUrl" class="h-24 w-auto rounded-xl object-cover" />
                        </div>
                        <input
                            type="file"
                            accept="image/*"
                            @change="handleThumbnailChange"
                            class="block w-full text-xs text-gray-400 file:mr-3 file:rounded-xl file:border-0 file:bg-orange-500/20 file:px-3 file:py-1.5 file:text-xs file:text-orange-600 file:cursor-pointer hover:file:bg-orange-500/30"
                        />
                        <p v-if="thumbnailUploading" class="mt-1 text-xs text-gray-500">Uploading…</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <input id="is_active" v-model="form.is_active" type="checkbox" class="rounded border-gray-300 bg-white accent-orange-500" />
                        <label for="is_active" class="text-sm text-gray-700">Published (visible to students)</label>
                    </div>

                    <p v-if="formError" class="text-xs text-rose-400">{{ formError }}</p>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="closeForm" class="rounded-xl border border-gray-200 px-5 py-2.5 text-sm text-gray-500 transition hover:text-gray-900">
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="formSubmitting || thumbnailUploading"
                            class="rounded-xl bg-[#1a1a4e] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#0f2460] disabled:opacity-50"
                        >
                            {{ formSubmitting ? 'Saving…' : 'Save' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Students modal -->
        <div v-if="showStudents" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
            <div class="w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Students — {{ selectedCourse?.title }}</h2>
                    <button @click="showStudents = false" class="text-gray-400 transition hover:text-gray-900">✕</button>
                </div>

                <!-- Add student -->
                <form @submit.prevent="addStudent" class="mb-4 flex gap-2">
                    <input
                        v-model="addStudentEmail"
                        type="email"
                        placeholder="Student email address"
                        required
                        class="flex-1 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 outline-none focus:border-orange-400"
                    />
                    <button
                        type="submit"
                        :disabled="addStudentLoading"
                        class="shrink-0 rounded-xl bg-orange-500 px-5 py-2 text-sm font-semibold text-white hover:bg-orange-600 disabled:opacity-50"
                    >
                        {{ addStudentLoading ? '…' : 'Add' }}
                    </button>
                </form>

                <div v-if="studentsLoading" class="space-y-3">
                    <div v-for="n in 4" :key="n" class="h-12 animate-pulse rounded-xl bg-gray-200" />
                </div>

                <div v-else-if="students.length === 0" class="py-10 text-center text-sm text-gray-400">
                    No students enrolled yet.
                </div>

                <ul v-else class="max-h-72 divide-y divide-gray-100 overflow-y-auto">
                    <li
                        v-for="student in students"
                        :key="student.id"
                        class="flex items-center justify-between gap-3 py-3"
                    >
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ student.name }}</p>
                            <p class="text-xs text-gray-500">{{ student.email }}</p>
                            <p class="mt-0.5 text-[11px]" :class="statusColor(student.status)">{{ statusLabel(student.status) }}</p>
                        </div>
                        <button
                            @click="removeStudent(student)"
                            :disabled="removingStudentId === student.id"
                            class="rounded-xl border border-rose-500/30 px-3 py-1.5 text-xs text-rose-400 transition hover:bg-rose-500/10 disabled:opacity-50"
                        >
                            Remove
                        </button>
                    </li>
                </ul>

                <p v-if="studentActionFeedback" class="mt-3 text-xs" :class="studentActionFeedback.type === 'success' ? 'text-emerald-300' : 'text-rose-400'">
                    {{ studentActionFeedback.message }}
                </p>
            </div>
        </div>

        <!-- Delete confirmation modal -->
        <div v-if="deletingCourse" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
            <div class="w-full max-w-sm rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl">
                <h2 class="mb-2 text-lg font-semibold text-gray-900">Delete Course?</h2>
                <p class="text-sm text-gray-500">
                    "<span class="text-gray-900">{{ deletingCourse.title }}</span>" will be permanently deleted. This cannot be undone.
                </p>
                <div class="mt-5 flex justify-end gap-3">
                    <button @click="deletingCourse = null" class="rounded-xl border border-gray-200 px-4 py-2 text-sm text-gray-500 transition hover:text-gray-900">Cancel</button>
                    <button
                        @click="deleteCourse"
                        :disabled="deleteSubmitting"
                        class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700 disabled:opacity-50"
                    >
                        {{ deleteSubmitting ? 'Deleting…' : 'Delete' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { RouterLink } from 'vue-router';
import axios from 'axios';
import Navbar from '../components/Navbar.vue';
import AppFooter from '../components/Footer.vue';

interface Category { id: number; name: string }
interface Tag { id: number; name: string; slug: string }
interface Course {
    id: number;
    title: string;
    slug: string;
    thumbnail: string | null;
    description: string | null;
    is_active: boolean;
    category: Category | null;
    category_id: number | null;
    tags: Tag[];
    lessons_count: number;
    reviews_count: number;
    reviews_avg_rating: string | null;
    enrolled_count: number;
}
interface Student {
    id: number;
    name: string;
    email: string;
    status: number;
    enrolled_at: string;
}

const APPROVED = 2;
const CANCELED = 3;

// ── State ────────────────────────────────────────────────
const courses  = ref<Course[]>([]);
const loading  = ref(true);
const categories = ref<Category[]>([]);

// Form
const showForm       = ref(false);
const editingCourse  = ref<Course | null>(null);
const formSubmitting = ref(false);
const formError      = ref('');
const tagsInput      = ref('');
const thumbnailUploading = ref(false);
const thumbnailPreviewUrl = ref<string>('');
const form = ref({
    title: '',
    description: '',
    category_id: null as number | null,
    thumbnail: null as string | null,
    is_active: false,
});

// Students
const showStudents        = ref(false);
const selectedCourse      = ref<Course | null>(null);
const students            = ref<Student[]>([]);
const studentsLoading     = ref(false);
const removingStudentId   = ref<number | null>(null);
const studentActionFeedback = ref<{ type: 'success' | 'error'; message: string } | null>(null);
const addStudentEmail     = ref('');
const addStudentLoading   = ref(false);

// Delete
const deletingCourse   = ref<Course | null>(null);
const deleteSubmitting = ref(false);

// ── Helpers ──────────────────────────────────────────────
const statusLabel = (status: number) => {
    if (status === APPROVED) return 'Enrolled';
    if (status === CANCELED) return 'Canceled';
    return 'Pending';
};
const statusColor = (status: number) => {
    if (status === APPROVED) return 'text-emerald-400';
    if (status === CANCELED) return 'text-rose-400';
    return 'text-amber-400';
};

// ── Data fetching ─────────────────────────────────────────
const fetchCourses = async () => {
    loading.value = true;
    try {
        const { data } = await axios.get('/api/instructor/courses');
        courses.value = data;
    } finally {
        loading.value = false;
    }
};

const fetchCategories = async () => {
    const { data } = await axios.get('/api/categories');
    categories.value = data;
};

// ── Course form ───────────────────────────────────────────
const openCreate = () => {
    editingCourse.value = null;
    form.value = { title: '', description: '', category_id: null, thumbnail: null, is_active: false };
    thumbnailPreviewUrl.value = '';
    tagsInput.value = '';
    formError.value = '';
    showForm.value = true;
};

const openEdit = (course: Course) => {
    editingCourse.value = course;
    form.value = {
        title: course.title,
        description: course.description ?? '',
        category_id: course.category_id,
        thumbnail: null,
        is_active: course.is_active,
    };
    thumbnailPreviewUrl.value = course.thumbnail ?? '';
    tagsInput.value = course.tags.map((t) => t.name).join(', ');
    formError.value = '';
    showForm.value = true;
};

const closeForm = () => {
    showForm.value = false;
    editingCourse.value = null;
    thumbnailPreviewUrl.value = '';
};

const handleThumbnailChange = async (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (!file) return;

    thumbnailUploading.value = true;
    try {
        const { data: presign } = await axios.post('/api/instructor/courses/presign-thumbnail', {
            file_name: file.name,
            content_type: file.type,
            file_size: file.size,
        });

        await axios.put(presign.upload_url, file, {
            headers: { 'Content-Type': file.type },
            withCredentials: false,
        });

        form.value.thumbnail = presign.path;
        thumbnailPreviewUrl.value = presign.thumbnail_url;
    } catch (err: any) {
        formError.value = err?.response?.data?.message ?? err?.message ?? 'Thumbnail upload failed.';
    } finally {
        thumbnailUploading.value = false;
    }
};

const submitForm = async () => {
    formSubmitting.value = true;
    formError.value = '';

    const tags = tagsInput.value
        .split(',')
        .map((t) => t.trim())
        .filter(Boolean);

    const payload: Record<string, unknown> = {
        title:       form.value.title,
        description: form.value.description,
        category_id: form.value.category_id,
        is_active:   form.value.is_active,
        tags,
    };
    // Only include thumbnail when a new file was uploaded in this session.
    // Omitting it prevents accidentally overwriting or clearing the existing thumbnail.
    if (form.value.thumbnail !== null) {
        payload.thumbnail = form.value.thumbnail;
    }

    try {
        if (editingCourse.value) {
            const { data } = await axios.put(`/api/instructor/courses/${editingCourse.value.id}`, payload);
            const idx = courses.value.findIndex((c) => c.id === data.id);
            if (idx !== -1) courses.value[idx] = data;
        } else {
            const { data } = await axios.post('/api/instructor/courses', payload);
            courses.value.unshift(data);
        }
        closeForm();
    } catch (e: any) {
        const errors = e?.response?.data?.errors;
        formError.value = errors ? Object.values(errors).flat().join(' ') : (e?.response?.data?.message ?? 'An error occurred.');
    } finally {
        formSubmitting.value = false;
    }
};

// ── Students ──────────────────────────────────────────────
const openStudents = async (course: Course) => {
    selectedCourse.value = course;
    students.value = [];
    studentActionFeedback.value = null;
    showStudents.value = true;
    studentsLoading.value = true;

    try {
        const { data } = await axios.get(`/api/instructor/courses/${course.id}/students`);
        students.value = data;
    } finally {
        studentsLoading.value = false;
    }
};

const removeStudent = async (student: Student) => {
    if (!selectedCourse.value) return;
    removingStudentId.value = student.id;
    studentActionFeedback.value = null;

    try {
        await axios.delete(`/api/instructor/courses/${selectedCourse.value.id}/students/${student.id}`);
        students.value = students.value.filter((s) => s.id !== student.id);

        // update enrolled count on the card
        const course = courses.value.find((c) => c.id === selectedCourse.value?.id);
        if (course && student.status === APPROVED) course.enrolled_count = Math.max(0, course.enrolled_count - 1);

        studentActionFeedback.value = { type: 'success', message: `${student.name} has been removed.` };
    } catch (e: any) {
        studentActionFeedback.value = { type: 'error', message: e?.response?.data?.message ?? 'Failed to remove student.' };
    } finally {
        removingStudentId.value = null;
    }
};

// ── Delete ────────────────────────────────────────────────
const addStudent = async () => {
    if (!selectedCourse.value || !addStudentEmail.value) return;
    addStudentLoading.value = true;
    studentActionFeedback.value = null;

    try {
        const { data } = await axios.post(`/api/instructor/courses/${selectedCourse.value.id}/students`, {
            email: addStudentEmail.value,
        });
        students.value.unshift({
            id: data.student.id,
            name: data.student.name,
            email: data.student.email,
            status: APPROVED,
            enrolled_at: new Date().toISOString(),
        });
        const course = courses.value.find((c) => c.id === selectedCourse.value?.id);
        if (course) course.enrolled_count++;
        addStudentEmail.value = '';
        studentActionFeedback.value = { type: 'success', message: data.message };
    } catch (e: any) {
        const errors = e?.response?.data?.errors;
        studentActionFeedback.value = { type: 'error', message: errors ? Object.values(errors).flat().join(' ') : (e?.response?.data?.message ?? 'Failed to add student.') };
    } finally {
        addStudentLoading.value = false;
    }
};

const confirmDelete = (course: Course) => {
    deletingCourse.value = course;
};

const deleteCourse = async () => {
    if (!deletingCourse.value) return;
    deleteSubmitting.value = true;

    try {
        await axios.delete(`/api/instructor/courses/${deletingCourse.value.id}`);
        courses.value = courses.value.filter((c) => c.id !== deletingCourse.value?.id);
        deletingCourse.value = null;
    } catch (e: any) {
        alert(e?.response?.data?.message ?? 'Failed to delete course.');
    } finally {
        deleteSubmitting.value = false;
    }
};

// ── Init ──────────────────────────────────────────────────
onMounted(() => {
    fetchCourses();
    fetchCategories();
});
</script>
