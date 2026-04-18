<template>
  <div class="space-y-8">
    <!-- Stats cards -->
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
      <article
        v-for="stat in statCards"
        :key="stat.label"
        class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-[0_20px_60px_-40px_rgba(15,23,42,0.45)]"
      >
        <div class="flex items-start justify-between gap-4">
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">{{ stat.label }}</p>
            <p class="mt-3 text-3xl font-semibold text-slate-900">
              <span v-if="loading" class="inline-block h-8 w-16 animate-pulse rounded-xl bg-slate-100"></span>
              <span v-else>{{ stat.value }}</span>
            </p>
          </div>
          <div :class="stat.badgeClass" class="rounded-2xl px-3 py-2 text-xs font-semibold uppercase tracking-[0.25em]">
            {{ stat.badge }}
          </div>
        </div>
        <p class="mt-4 text-sm text-slate-500">{{ stat.description }}</p>
      </article>
    </section>

    <section class="grid gap-8 xl:grid-cols-[1.5fr_1fr]">
      <!-- Monthly lesson growth bar chart -->
      <article class="overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white shadow-[0_20px_60px_-40px_rgba(15,23,42,0.45)]">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Growth</p>
            <h3 class="mt-2 text-xl font-semibold text-slate-900">Lesson growth over six months</h3>
          </div>
          <p class="text-sm text-slate-400">Last 6 months</p>
        </div>

        <div class="px-6 py-8">
          <div class="flex h-72 items-end gap-4 rounded-[1.5rem] bg-slate-950 px-5 pb-5 pt-8 text-white">
            <template v-if="loading">
              <div v-for="i in 6" :key="i" class="flex flex-1 flex-col items-center justify-end gap-3">
                <div class="w-full animate-pulse rounded-t-2xl bg-slate-700" style="height: 40%"></div>
                <span class="h-3 w-8 animate-pulse rounded bg-slate-700"></span>
              </div>
            </template>
            <template v-else-if="monthlyLessons.length">
              <div v-for="month in monthlyLessons" :key="month.month" class="flex flex-1 flex-col items-center justify-end gap-3">
                <span class="text-xs text-slate-400">{{ month.count }}</span>
                <div
                  class="w-full rounded-t-2xl bg-gradient-to-t from-blue-500 via-cyan-400 to-emerald-300"
                  :style="{ height: `${maxMonthHeight(month.count)}%` }"
                ></div>
                <span class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">{{ month.month }}</span>
              </div>
            </template>
            <div v-else class="flex w-full items-center justify-center text-sm text-slate-500">
              No lesson data yet.
            </div>
          </div>
        </div>
      </article>

      <!-- Category distribution -->
      <article class="rounded-[2rem] border border-slate-200/80 bg-white p-6 shadow-[0_20px_60px_-40px_rgba(15,23,42,0.45)]">
        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Category Mix</p>
        <h3 class="mt-2 text-xl font-semibold text-slate-900">Course distribution</h3>

        <div v-if="loading" class="mt-8 space-y-4">
          <div v-for="i in 4" :key="i" class="h-5 animate-pulse rounded-xl bg-slate-100"></div>
        </div>
        <template v-else-if="categoryDistribution.length">
          <div
            class="mx-auto mt-8 grid h-64 w-64 place-items-center rounded-full p-8"
            :style="{ background: conicGradient }"
          >
            <div class="grid h-full w-full place-items-center rounded-full bg-white text-center">
              <div v-if="categoryDistribution[0]">
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Top segment</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900">{{ categoryDistribution[0].percent }}%</p>
                <p class="text-sm text-slate-500">{{ categoryDistribution[0].name }}</p>
              </div>
            </div>
          </div>

          <div class="mt-8 space-y-4">
            <div v-for="(cat, i) in categoryDistribution" :key="cat.name" class="flex items-center justify-between gap-4">
              <div class="flex items-center gap-3">
                <span class="h-3 w-3 rounded-full" :style="{ backgroundColor: palette[i % palette.length] }"></span>
                <span class="text-sm font-medium text-slate-700">{{ cat.name }}</span>
              </div>
              <span class="text-sm text-slate-500">{{ cat.percent }}%</span>
            </div>
          </div>
        </template>
        <div v-else class="mt-12 text-center text-sm text-slate-400">No category data yet.</div>
      </article>
    </section>

    <!-- Recent courses -->
    <section class="grid gap-8 lg:grid-cols-[1.2fr_0.8fr]">
      <article class="rounded-[2rem] border border-slate-200/80 bg-slate-950 p-8 text-white shadow-[0_20px_60px_-40px_rgba(15,23,42,0.65)]">
        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Quick Actions</p>
        <h3 class="mt-3 text-2xl font-semibold">Manage content faster</h3>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-400">
          Add a new lesson, review category coverage, or follow recent performance without leaving the dashboard.
        </p>
        <div class="mt-6 flex flex-wrap gap-3">
          <RouterLink to="/courses" class="rounded-2xl bg-white px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-slate-100">
            Manage courses
          </RouterLink>
          <RouterLink to="/categories" class="rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-500">
            Manage categories
          </RouterLink>
        </div>
      </article>

      <article class="rounded-[2rem] border border-slate-200/80 bg-white p-6 shadow-[0_20px_60px_-40px_rgba(15,23,42,0.45)]">
        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Recent Courses</p>
        <div class="mt-6 space-y-3">
          <div v-if="loading" v-for="i in 4" :key="i" class="h-14 animate-pulse rounded-2xl bg-slate-100"></div>
          <div v-else-if="!recentCourses.length" class="py-6 text-center text-sm text-slate-400">No courses yet.</div>
          <div
            v-else
            v-for="course in recentCourses"
            :key="course.id"
            class="rounded-2xl bg-slate-50 p-4"
          >
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="font-medium text-slate-900">{{ course.title }}</p>
                <p class="mt-1 text-sm text-slate-500">{{ course.instructor?.name ?? '—' }}</p>
              </div>
              <span
                :class="course.is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-400'"
                class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold uppercase tracking-[0.2em]"
              >
                {{ course.is_active ? 'Active' : 'Draft' }}
              </span>
            </div>
          </div>
        </div>
      </article>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

// ── Types ─────────────────────────────────────────────────────────────────────
type Stats = {
    total_users: number;
    total_courses: number;
    total_lessons: number;
    total_categories: number;
};
type MonthlyLesson = { month: string; count: number };
type CategoryDist  = { name: string; count: number; percent: number };
type RecentCourse  = { id: number; title: string; is_active: boolean; instructor?: { name: string } | null };

// ── State ─────────────────────────────────────────────────────────────────────
const loading             = ref(true);
const stats               = ref<Stats | null>(null);
const monthlyLessons      = ref<MonthlyLesson[]>([]);
const categoryDistribution = ref<CategoryDist[]>([]);
const recentCourses       = ref<RecentCourse[]>([]);

// ── Palette for category chart ────────────────────────────────────────────────
const palette = ['#3b82f6', '#f97316', '#8b5cf6', '#10b981', '#ef4444', '#eab308'];

// ── Computed stat cards ───────────────────────────────────────────────────────
const statCards = computed(() => [
    {
        label: 'Total Users',
        value: stats.value?.total_users.toLocaleString() ?? '—',
        badge: 'All',
        badgeClass: 'bg-blue-50 text-blue-600',
        description: 'Total registered users across all roles.',
    },
    {
        label: 'Courses',
        value: stats.value?.total_courses.toLocaleString() ?? '—',
        badge: 'Published',
        badgeClass: 'bg-orange-50 text-orange-600',
        description: 'Courses available on the platform.',
    },
    {
        label: 'Lessons',
        value: stats.value?.total_lessons.toLocaleString() ?? '—',
        badge: 'Total',
        badgeClass: 'bg-emerald-50 text-emerald-600',
        description: 'Individual lessons across all courses.',
    },
    {
        label: 'Categories',
        value: stats.value?.total_categories.toLocaleString() ?? '—',
        badge: 'Active',
        badgeClass: 'bg-violet-50 text-violet-600',
        description: 'Content categories currently in use.',
    },
]);

// ── Bar chart: normalise heights relative to max ──────────────────────────────
const maxCount = computed(() => Math.max(...monthlyLessons.value.map((m) => m.count), 1));
const maxMonthHeight = (count: number) => Math.max(Math.round((count / maxCount.value) * 90), 4);

// ── Conic gradient for category donut ────────────────────────────────────────
const conicGradient = computed(() => {
    let pos = 0;
    const stops = categoryDistribution.value.map((cat, i) => {
        const color = palette[i % palette.length];
        const start = pos;
        pos += cat.percent;
        return `${color} ${start}% ${pos}%`;
    });
    return stops.length ? `conic-gradient(${stops.join(', ')})` : 'conic-gradient(#e2e8f0 0% 100%)';
});

// ── Fetch ─────────────────────────────────────────────────────────────────────
onMounted(async () => {
    try {
        const res = await axios.get('/admin/api/dashboard');
        stats.value               = res.data.stats;
        monthlyLessons.value      = res.data.monthly_lessons;
        categoryDistribution.value = res.data.category_distribution;
        recentCourses.value       = res.data.recent_courses;
    } finally {
        loading.value = false;
    }
});
</script>
