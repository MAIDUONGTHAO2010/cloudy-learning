<template>
  <div class="space-y-8">
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
      <article
        v-for="stat in stats"
        :key="stat.label"
        class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-[0_20px_60px_-40px_rgba(15,23,42,0.45)]"
      >
        <div class="flex items-start justify-between gap-4">
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">{{ stat.label }}</p>
            <p class="mt-3 text-3xl font-semibold text-slate-900">{{ stat.value }}</p>
          </div>
          <div :class="stat.badgeClass" class="rounded-2xl px-3 py-2 text-xs font-semibold uppercase tracking-[0.25em]">
            {{ stat.badge }}
          </div>
        </div>
        <p class="mt-4 text-sm text-slate-500">{{ stat.description }}</p>
      </article>
    </section>

    <section class="grid gap-8 xl:grid-cols-[1.5fr_1fr]">
      <article class="overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white shadow-[0_20px_60px_-40px_rgba(15,23,42,0.45)]">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Growth</p>
            <h3 class="mt-2 text-xl font-semibold text-slate-900">Lesson growth over six months</h3>
          </div>
          <p class="text-sm text-slate-400">Updated weekly</p>
        </div>

        <div class="px-6 py-8">
          <div class="flex h-72 items-end gap-4 rounded-[1.5rem] bg-slate-950 px-5 pb-5 pt-8 text-white">
            <div v-for="month in growth" :key="month.label" class="flex flex-1 flex-col items-center justify-end gap-3">
              <span class="text-xs text-slate-400">{{ month.value }}</span>
              <div
                class="w-full rounded-t-2xl bg-linear-to-t from-blue-500 via-cyan-400 to-emerald-300"
                :style="{ height: `${month.height}%` }"
              ></div>
              <span class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">{{ month.label }}</span>
            </div>
          </div>
        </div>
      </article>

      <article class="rounded-[2rem] border border-slate-200/80 bg-white p-6 shadow-[0_20px_60px_-40px_rgba(15,23,42,0.45)]">
        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Category Mix</p>
        <h3 class="mt-2 text-xl font-semibold text-slate-900">Course distribution</h3>

        <div class="mx-auto mt-8 grid h-64 w-64 place-items-center rounded-full bg-[conic-gradient(#3b82f6_0_45%,#f97316_45%_70%,#8b5cf6_70%_88%,#10b981_88%_100%)] p-8">
          <div class="grid h-full w-full place-items-center rounded-full bg-white text-center">
            <div>
              <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Top segment</p>
              <p class="mt-2 text-3xl font-semibold text-slate-900">45%</p>
              <p class="text-sm text-slate-500">Programming</p>
            </div>
          </div>
        </div>

        <div class="mt-8 space-y-4">
          <div v-for="category in categories" :key="category.label" class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
              <span class="h-3 w-3 rounded-full" :style="{ backgroundColor: category.color }"></span>
              <span class="text-sm font-medium text-slate-700">{{ category.label }}</span>
            </div>
            <span class="text-sm text-slate-500">{{ category.value }}</span>
          </div>
        </div>
      </article>
    </section>

    <section class="grid gap-8 lg:grid-cols-[1.2fr_0.8fr]">
      <article class="rounded-[2rem] border border-slate-200/80 bg-slate-950 p-8 text-white shadow-[0_20px_60px_-40px_rgba(15,23,42,0.65)]">
        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Quick Actions</p>
        <h3 class="mt-3 text-2xl font-semibold">Manage content faster</h3>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-400">
          Add a new lesson, review category coverage, or follow recent performance without leaving the dashboard.
        </p>

        <div class="mt-6 flex flex-wrap gap-3">
          <button class="rounded-2xl bg-white px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-slate-100">Add lesson</button>
          <button class="rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-500">Manage categories</button>
        </div>
      </article>

      <article class="rounded-[2rem] border border-slate-200/80 bg-white p-6 shadow-[0_20px_60px_-40px_rgba(15,23,42,0.45)]">
        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Recent Updates</p>
        <div class="mt-6 space-y-4">
          <div v-for="activity in activityFeed" :key="activity.title" class="rounded-2xl bg-slate-50 p-4">
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="font-medium text-slate-900">{{ activity.title }}</p>
                <p class="mt-1 text-sm text-slate-500">{{ activity.description }}</p>
              </div>
              <span class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">{{ activity.when }}</span>
            </div>
          </div>
        </div>
      </article>
    </section>
  </div>
</template>

<script setup lang="ts">
const stats = [
  {
    label: 'Total lessons',
    value: '1,402',
    badge: '+12%',
    badgeClass: 'bg-blue-50 text-blue-600',
    description: 'New publishing volume is ahead of the monthly target.',
  },
  {
    label: 'Categories',
    value: '24',
    badge: '+3',
    badgeClass: 'bg-orange-50 text-orange-600',
    description: 'Taxonomy expanded with business and design topics.',
  },
  {
    label: 'Active learners',
    value: '856',
    badge: 'Stable',
    badgeClass: 'bg-emerald-50 text-emerald-600',
    description: 'Weekly engagement remains strong across premium tracks.',
  },
  {
    label: 'Completion rate',
    value: '92%',
    badge: 'Top',
    badgeClass: 'bg-violet-50 text-violet-600',
    description: 'Course completion improved after the latest lesson refresh.',
  },
];

const growth = [
  { label: 'Nov', value: 120, height: 24 },
  { label: 'Dec', value: 190, height: 38 },
  { label: 'Jan', value: 300, height: 58 },
  { label: 'Feb', value: 250, height: 48 },
  { label: 'Mar', value: 420, height: 78 },
  { label: 'Apr', value: 510, height: 96 },
];

const categories = [
  { label: 'Programming', value: '45%', color: '#3b82f6' },
  { label: 'Languages', value: '25%', color: '#f97316' },
  { label: 'Design', value: '18%', color: '#8b5cf6' },
  { label: 'Business', value: '12%', color: '#10b981' },
];

const activityFeed = [
  {
    title: 'React performance course published',
    description: 'A new advanced module was added to the frontend path.',
    when: '2h ago',
  },
  {
    title: 'Category audit completed',
    description: 'Duplicate labels were merged and metadata was refreshed.',
    when: 'Today',
  },
  {
    title: 'Instructor onboarding approved',
    description: 'Three pending experts can now create premium learning content.',
    when: 'Yesterday',
  },
];
</script>
