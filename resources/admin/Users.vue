<template>
  <div class="space-y-8">
    <!-- Page header + stat cards -->
    <section class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
      <div>
        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Users</p>
        <h1 class="mt-2 text-3xl font-semibold text-slate-900">User management</h1>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-500">
          Browse all registered users, filter by role, or search by name / email.
        </p>
      </div>

      <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-3xl border border-slate-200/80 bg-white px-5 py-4 shadow-sm">
          <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Total users</p>
          <p class="mt-2 text-2xl font-semibold text-slate-900">
            <span v-if="statsLoading" class="inline-block h-7 w-14 animate-pulse rounded-lg bg-slate-100"></span>
            <span v-else>{{ userStats.total.toLocaleString() }}</span>
          </p>
        </div>
        <div class="rounded-3xl border border-slate-200/80 bg-white px-5 py-4 shadow-sm">
          <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Instructors</p>
          <p class="mt-2 text-2xl font-semibold text-slate-900">
            <span v-if="statsLoading" class="inline-block h-7 w-10 animate-pulse rounded-lg bg-slate-100"></span>
            <span v-else>{{ userStats.instructors.toLocaleString() }}</span>
          </p>
        </div>
        <div class="rounded-3xl border border-slate-200/80 bg-white px-5 py-4 shadow-sm">
          <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Students</p>
          <p class="mt-2 text-2xl font-semibold text-slate-900">
            <span v-if="statsLoading" class="inline-block h-7 w-10 animate-pulse rounded-lg bg-slate-100"></span>
            <span v-else>{{ userStats.students.toLocaleString() }}</span>
          </p>
        </div>
      </div>
    </section>

    <!-- Table section -->
    <section class="overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white shadow-[0_20px_60px_-40px_rgba(15,23,42,0.45)]">
      <!-- Toolbar -->
      <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 md:flex-row md:items-center md:justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Directory</p>
          <h2 class="mt-2 text-xl font-semibold text-slate-900">All users</h2>
        </div>
        <div class="flex flex-wrap items-center gap-3">
          <!-- Search -->
          <input
            v-model="search"
            @input="onSearchInput"
            type="text"
            placeholder="Search name or email…"
            class="rounded-2xl border border-slate-200 px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100 w-64"
          />
          <!-- Role filter -->
          <select
            v-model="roleFilter"
            @change="fetchUsers(1)"
            class="rounded-2xl border border-slate-200 px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
          >
            <option value="">All roles</option>
            <option value="1">Student</option>
            <option value="2">Instructor</option>
            <option value="3">Admin</option>
          </select>
        </div>
      </div>

      <!-- Loading skeleton -->
      <div v-if="loading" class="divide-y divide-slate-100">
        <div v-for="i in 8" :key="i" class="flex items-center gap-4 px-6 py-5">
          <div class="h-11 w-11 animate-pulse rounded-2xl bg-slate-100"></div>
          <div class="flex-1 space-y-2">
            <div class="h-4 w-48 animate-pulse rounded bg-slate-100"></div>
            <div class="h-3 w-64 animate-pulse rounded bg-slate-100"></div>
          </div>
        </div>
      </div>

      <!-- Empty -->
      <div v-else-if="!users.length" class="py-16 text-center text-sm text-slate-400">
        No users found.
      </div>

      <!-- Table -->
      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100">
          <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
            <tr>
              <th class="px-6 py-4">User</th>
              <th class="px-6 py-4">Role</th>
              <th class="px-6 py-4">Joined</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 bg-white">
            <tr v-for="user in users" :key="user.id" class="text-sm text-slate-600 transition hover:bg-slate-50/50">
              <td class="px-6 py-5">
                <div class="flex items-center gap-4">
                  <div class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-slate-900 font-semibold text-white">
                    {{ initials(user.name) }}
                  </div>
                  <div>
                    <p class="font-medium text-slate-900">{{ user.name }}</p>
                    <p class="text-slate-500">{{ user.email }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-5">
                <span :class="roleClass(user.role)" class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em]">
                  {{ roleLabel(user.role) }}
                </span>
              </td>
              <td class="px-6 py-5 text-slate-400">{{ formatDate(user.created_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="lastPage > 1" class="flex items-center justify-between border-t border-slate-100 px-6 py-4">
        <p class="text-sm text-slate-400">
          Page {{ currentPage }} of {{ lastPage }} &nbsp;·&nbsp; {{ total.toLocaleString() }} users
        </p>
        <div class="flex gap-2">
          <button
            @click="fetchUsers(currentPage - 1)"
            :disabled="currentPage === 1"
            class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-50 disabled:opacity-40"
          >
            ← Prev
          </button>
          <button
            @click="fetchUsers(currentPage + 1)"
            :disabled="currentPage === lastPage"
            class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-50 disabled:opacity-40"
          >
            Next →
          </button>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';

// ── Types ─────────────────────────────────────────────────────────────────────
type User = {
    id: number;
    name: string;
    email: string;
    role: number;
    created_at: string;
};

// ── State ─────────────────────────────────────────────────────────────────────
const users       = ref<User[]>([]);
const loading     = ref(false);
const search      = ref('');
const roleFilter  = ref('');
const currentPage = ref(1);
const lastPage    = ref(1);
const total       = ref(0);

const statsLoading = ref(true);
const userStats    = ref({ total: 0, students: 0, instructors: 0, admins: 0 });

let searchTimer: ReturnType<typeof setTimeout> | null = null;

// ── Fetch users list ──────────────────────────────────────────────────────────
const fetchUsers = async (page = 1) => {
    loading.value = true;
    try {
        const params: Record<string, string | number> = { page };
        if (search.value)     params.search = search.value;
        if (roleFilter.value) params.role   = roleFilter.value;

        const res = await axios.get('/admin/api/users', { params });
        users.value       = res.data.data;
        currentPage.value = res.data.current_page;
        lastPage.value    = res.data.last_page;
        total.value       = res.data.total;
    } finally {
        loading.value = false;
    }
};

// ── Fetch stats ───────────────────────────────────────────────────────────────
const fetchStats = async () => {
    try {
        const res = await axios.get('/admin/api/users/stats');
        userStats.value = res.data;
    } finally {
        statsLoading.value = false;
    }
};

// ── Debounced search ──────────────────────────────────────────────────────────
const onSearchInput = () => {
    if (searchTimer) clearTimeout(searchTimer);
    searchTimer = setTimeout(() => fetchUsers(1), 350);
};

// ── Helpers ───────────────────────────────────────────────────────────────────
const initials = (name: string) =>
    name.split(' ').slice(0, 2).map((w) => w[0]).join('').toUpperCase();

const roleLabel = (role: number) => ({ 1: 'Student', 2: 'Instructor', 3: 'Admin' }[role] ?? 'Unknown');

const roleClass = (role: number) => ({
    1: 'bg-slate-100 text-slate-500',
    2: 'bg-blue-50 text-blue-600',
    3: 'bg-violet-50 text-violet-600',
}[role] ?? 'bg-slate-100 text-slate-400');

const formatDate = (iso: string) =>
    new Date(iso).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });

// ── Init ──────────────────────────────────────────────────────────────────────
onMounted(() => {
    fetchUsers();
    fetchStats();
});
</script>
