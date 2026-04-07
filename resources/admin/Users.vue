<template>
  <div class="space-y-8">
    <section class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
      <div>
        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Users</p>
        <h1 class="mt-2 text-3xl font-semibold text-slate-900">Learner management</h1>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-500">
          Review active learners, track subscription status, and spot accounts that need intervention.
        </p>
      </div>

      <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-3xl border border-slate-200/80 bg-white px-5 py-4 shadow-sm">
          <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Total users</p>
          <p class="mt-2 text-2xl font-semibold text-slate-900">12,480</p>
        </div>
        <div class="rounded-3xl border border-slate-200/80 bg-white px-5 py-4 shadow-sm">
          <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Premium</p>
          <p class="mt-2 text-2xl font-semibold text-slate-900">3,204</p>
        </div>
        <div class="rounded-3xl border border-slate-200/80 bg-white px-5 py-4 shadow-sm">
          <p class="text-xs uppercase tracking-[0.25em] text-slate-400">At risk</p>
          <p class="mt-2 text-2xl font-semibold text-amber-600">186</p>
        </div>
      </div>
    </section>

    <section class="overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white shadow-[0_20px_60px_-40px_rgba(15,23,42,0.45)]">
      <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 md:flex-row md:items-center md:justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Directory</p>
          <h2 class="mt-2 text-xl font-semibold text-slate-900">Recent learner activity</h2>
        </div>
        <div class="rounded-2xl bg-slate-100 px-4 py-2 text-sm text-slate-500">Showing 4 highlighted accounts</div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100">
          <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
            <tr>
              <th class="px-6 py-4">Learner</th>
              <th class="px-6 py-4">Track</th>
              <th class="px-6 py-4">Progress</th>
              <th class="px-6 py-4">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 bg-white">
            <tr v-for="user in users" :key="user.name" class="text-sm text-slate-600">
              <td class="px-6 py-5">
                <div class="flex items-center gap-4">
                  <div class="grid h-11 w-11 place-items-center rounded-2xl bg-slate-900 font-semibold text-white">
                    {{ user.initials }}
                  </div>
                  <div>
                    <p class="font-medium text-slate-900">{{ user.name }}</p>
                    <p class="text-slate-500">{{ user.email }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-5">{{ user.track }}</td>
              <td class="px-6 py-5">
                <div class="flex items-center gap-3">
                  <div class="h-2.5 w-32 rounded-full bg-slate-100">
                    <div class="h-2.5 rounded-full bg-blue-600" :style="{ width: user.progress }"></div>
                  </div>
                  <span>{{ user.progress }}</span>
                </div>
              </td>
              <td class="px-6 py-5">
                <span :class="user.statusClass" class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em]">
                  {{ user.status }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
const users = [
  {
    initials: 'AL',
    name: 'Ava Lewis',
    email: 'ava.lewis@example.com',
    track: 'Frontend Engineering',
    progress: '82%',
    status: 'Active',
    statusClass: 'bg-emerald-50 text-emerald-600',
  },
  {
    initials: 'NT',
    name: 'Noah Tran',
    email: 'noah.tran@example.com',
    track: 'Business Analytics',
    progress: '64%',
    status: 'Review',
    statusClass: 'bg-amber-50 text-amber-600',
  },
  {
    initials: 'MJ',
    name: 'Mia Johnson',
    email: 'mia.johnson@example.com',
    track: 'UI Design',
    progress: '91%',
    status: 'Active',
    statusClass: 'bg-emerald-50 text-emerald-600',
  },
  {
    initials: 'DT',
    name: 'Daniel Truong',
    email: 'daniel.truong@example.com',
    track: 'English Communication',
    progress: '47%',
    status: 'At risk',
    statusClass: 'bg-rose-50 text-rose-600',
  },
];
</script>
