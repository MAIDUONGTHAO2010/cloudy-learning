<template>
    <div class="min-h-screen bg-gray-50 text-gray-900">
        <Navbar />

        <main class="mx-auto max-w-7xl px-6 py-10">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-semibold">All Courses</h1>
                <p class="mt-1 text-sm text-gray-500">{{ total }} course{{ total !== 1 ? 's' : '' }} found</p>
            </div>

            <div class="flex flex-col gap-6 lg:flex-row">
                <!-- ── Sidebar filters ── -->
                <aside class="w-full shrink-0 lg:w-64">
                    <!-- Search -->
                    <div class="mb-4">
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                            </svg>
                            <input
                                v-model="filters.search"
                                @input="debouncedFetch"
                                type="text"
                                placeholder="Search courses…"
                                class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-9 pr-4 text-sm text-gray-900 placeholder-gray-400 outline-none transition focus:border-orange-400/60 focus:ring-1 focus:ring-orange-400/30"
                            />
                        </div>
                    </div>

                    <!-- Category -->
                    <div class="mb-4">
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-widest text-gray-500">Category</label>
                        <select
                            :value="filters.category_id ?? ''"
                            @change="setFilter('category_id', ($event.target as HTMLSelectElement).value ? Number(($event.target as HTMLSelectElement).value) : null)"
                            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 outline-none transition focus:border-orange-400/60 focus:ring-1 focus:ring-orange-400/30"
                        >
                            <option value="">All categories</option>
                            <option v-for="cat in filterData.categories" :key="cat.id" :value="cat.id">
                                {{ cat.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Instructor -->
                    <div class="mb-4">
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-widest text-gray-500">Instructor</label>
                        <select
                            :value="filters.instructor_id ?? ''"
                            @change="setFilter('instructor_id', ($event.target as HTMLSelectElement).value ? Number(($event.target as HTMLSelectElement).value) : null)"
                            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 outline-none transition focus:border-orange-400/60 focus:ring-1 focus:ring-orange-400/30"
                        >
                            <option value="">All instructors</option>
                            <option v-for="inst in filterData.instructors" :key="inst.id" :value="inst.id">
                                {{ inst.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Tags -->
                    <div v-if="filterData.tags.length" class="mb-4">
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-widest text-gray-500">Tag</label>
                        <select
                            :value="filters.tag ?? ''"
                            @change="setFilter('tag', ($event.target as HTMLSelectElement).value || null)"
                            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 outline-none transition focus:border-orange-400/60 focus:ring-1 focus:ring-orange-400/30"
                        >
                            <option value="">All tags</option>
                            <option v-for="tag in filterData.tags" :key="tag.id" :value="tag.slug">
                                {{ tag.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Clear filters -->
                    <button
                        v-if="hasActiveFilters"
                        @click="clearFilters"
                        class="mt-4 w-full rounded-xl border border-rose-500/30 py-2 text-sm text-rose-400 transition hover:bg-rose-500/10"
                    >
                        Clear all filters
                    </button>
                </aside>

                <!-- ── Course grid ── -->
                <div class="flex-1 min-w-0">
                    <!-- Loading skeleton -->
                        <div v-if="loading" class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                        <div v-for="n in 9" :key="n" class="h-56 animate-pulse rounded-2xl border border-gray-100 bg-gray-200" />
                    </div>

                    <div v-else-if="courses.length === 0" class="flex flex-col items-center justify-center py-24 text-center">
                            <svg class="mb-4 h-12 w-12 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                        </svg>
                        <p class="text-gray-400">No courses match your filters.</p>
                        <button @click="clearFilters" class="mt-3 text-sm text-orange-500 hover:underline">Clear filters</button>
                    </div>

                    <div v-else class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                        <RouterLink
                            v-for="course in courses"
                            :key="course.id"
                            :to="`/courses/${course.slug}`"
                            class="group flex flex-col rounded-2xl border border-gray-200 bg-white overflow-hidden transition hover:border-orange-400/50 hover:shadow-lg hover:shadow-orange-100"
                        >
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
                                    <span class="text-4xl font-bold text-orange-300/40">{{ course.title.charAt(0) }}</span>
                                </div>
                            </div>

                            <div class="flex flex-1 flex-col p-4">
                                <span v-if="course.category" class="mb-1 text-xs font-medium text-orange-500">
                                    {{ course.category.name }}
                                </span>
                                <h3 class="line-clamp-2 text-sm font-semibold text-gray-900 leading-snug">{{ course.title }}</h3>
                                <p class="mt-1 text-xs text-gray-500">by {{ course.instructor?.name }}</p>

                                <!-- Tags -->
                                <div v-if="course.tags?.length" class="mt-2 flex flex-wrap gap-1">
                                    <span
                                        v-for="tag in course.tags"
                                        :key="tag.id"
                                        class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-500"
                                    >{{ tag.name }}</span>
                                </div>

                                <div class="mt-auto flex items-center justify-between pt-3 border-t border-gray-100">
                                    <span class="text-xs text-gray-400">{{ course.lessons_count }} lessons</span>
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
                            class="rounded-xl border border-gray-200 px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-100 disabled:opacity-30"
                        >
                            ← Previous
                        </button>
                        <span class="text-sm text-gray-500">Page {{ currentPage }} of {{ lastPage }}</span>
                        <button
                            :disabled="currentPage >= lastPage"
                            @click="fetchPage(currentPage + 1)"
                            class="rounded-xl border border-gray-200 px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-100 disabled:opacity-30"
                        >
                            Next →
                        </button>
                    </div>
                </div>
            </div>
        </main>
        <AppFooter />
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import Navbar from '../components/Navbar.vue';
import AppFooter from '../components/Footer.vue';

interface Course {
    id: number;
    title: string;
    slug: string;
    thumbnail: string | null;
    lessons_count: number;
    reviews_avg_rating: string | null;
    instructor: { id: number; name: string } | null;
    category: { id: number; name: string } | null;
    tags: { id: number; name: string; slug: string }[];
}

interface FilterData {
    categories: { id: number; name: string }[];
    instructors: { id: number; name: string }[];
    tags: { id: number; name: string; slug: string }[];
}

const route = useRoute();

const courses = ref<Course[]>([]);
const loading = ref(true);
const currentPage = ref(1);
const lastPage = ref(1);
const total = ref(0);

const filters = reactive<{
    search: string;
    category_id: number | null;
    instructor_id: number | null;
    tag: string | null;
}>({
    search: route.query.search ? String(route.query.search) : '',
    category_id: route.query.category_id ? Number(route.query.category_id) : null,
    instructor_id: route.query.instructor_id ? Number(route.query.instructor_id) : null,
    tag: route.query.tag ? String(route.query.tag) : null,
});

const filterData = reactive<FilterData>({
    categories: [],
    instructors: [],
    tags: [],
});

const hasActiveFilters = computed(() =>
    !!filters.search || !!filters.category_id || !!filters.instructor_id || !!filters.tag,
);

const buildParams = (page: number) => {
    const params: Record<string, string | number> = { page };
    if (filters.search)       params.search        = filters.search;
    if (filters.category_id)  params.category_id   = filters.category_id;
    if (filters.instructor_id) params.instructor_id = filters.instructor_id;
    if (filters.tag)          params.tag           = filters.tag;
    return params;
};

const fetchPage = async (page: number) => {
    loading.value = true;
    try {
        const { data } = await axios.get('/api/courses', { params: buildParams(page) });
        courses.value = data.data;
        currentPage.value = data.current_page;
        lastPage.value = data.last_page;
        total.value = data.total;
    } finally {
        loading.value = false;
    }
};

const setFilter = (key: keyof typeof filters, value: any) => {
    (filters as any)[key] = value;
    fetchPage(1);
};

const clearFilters = () => {
    filters.search = '';
    filters.category_id = null;
    filters.instructor_id = null;
    filters.tag = null;
    fetchPage(1);
};

// Debounce search input
let searchTimer: ReturnType<typeof setTimeout> | null = null;
const debouncedFetch = () => {
    if (searchTimer) clearTimeout(searchTimer);
    searchTimer = setTimeout(() => fetchPage(1), 350);
};

onMounted(() => {
    fetchPage(1);

    axios.get<{ id: number; name: string }[]>('/api/categories')
        .then(({ data }) => { filterData.categories = data; })
        .catch(() => {});

    axios.get<{ id: number; name: string }[]>('/api/instructors')
        .then(({ data }) => { filterData.instructors = data; })
        .catch(() => {});
});
</script>

