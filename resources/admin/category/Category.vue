<template>
  <div class="space-y-8">
    <!-- Page header -->
    <section class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
      <div>
        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Categories</p>
        <h1 class="mt-2 text-3xl font-semibold text-slate-900">Category management</h1>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-500">
          Organise learning content by managing parent categories and their subcategories.
        </p>
      </div>
    </section>

    <!-- Table section -->
    <section class="overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white shadow-[0_20px_60px_-40px_rgba(15,23,42,0.45)]">
      <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 md:flex-row md:items-center md:justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Directory</p>
          <h2 class="mt-2 text-xl font-semibold text-slate-900">All categories</h2>
        </div>
        <button
          @click="openCreateModal(null)"
          class="inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
          </svg>
          Add Category
        </button>
      </div>

      <!-- Loading -->
      <div v-if="loading && !items.length" class="flex items-center justify-center py-16 text-sm text-slate-400">
        Loading categories…
      </div>

      <!-- Empty -->
      <div v-else-if="!loading && !items.length" class="py-16 text-center text-sm text-slate-400">
        No categories yet. Create your first one.
      </div>

      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100">
          <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
            <tr>
              <th class="w-12 px-4 py-4"></th>
              <th class="px-6 py-4">Name</th>
              <th class="px-6 py-4">Description</th>
              <th class="px-6 py-4">Order</th>
              <th class="px-6 py-4">Status</th>
              <th class="px-6 py-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 bg-white">
            <template v-for="cat in items" :key="cat.id">

              <!-- Parent row -->
              <tr class="text-sm text-slate-600 transition hover:bg-slate-50/50">
                <td class="px-4 py-4 text-center">
                  <button
                    v-if="(cat.children_count ?? 0) > 0"
                    @click="toggleExpand(cat.id!)"
                    class="inline-flex h-7 w-7 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                  >
                    <!-- Chevron down (expanded) -->
                    <svg v-if="expandedIds.has(cat.id!)" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                    <!-- Chevron right (collapsed) -->
                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                  </button>
                  <span v-else class="inline-block h-7 w-7"></span>
                </td>

                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-blue-50 text-xs font-bold uppercase text-blue-600">
                      {{ (cat.name ?? '').slice(0, 2) }}
                    </div>
                    <div>
                      <p class="font-medium text-slate-900">{{ cat.name }}</p>
                      <p v-if="(cat.children_count ?? 0) > 0" class="text-xs text-slate-400">
                        {{ cat.children_count }} subcategor{{ cat.children_count === 1 ? 'y' : 'ies' }}
                      </p>
                    </div>
                  </div>
                </td>

                <td class="max-w-xs px-6 py-4">
                  <p class="truncate text-slate-500">{{ cat.description || '—' }}</p>
                </td>

                <td class="px-6 py-4 text-slate-500">{{ cat.order ?? 0 }}</td>

                <td class="px-6 py-4">
                  <span
                    :class="cat.is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-400'"
                    class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em]"
                  >
                    {{ cat.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>

                <td class="px-6 py-4">
                  <div class="flex items-center justify-end gap-2">
                    <button
                      @click="openCreateModal(cat.id!)"
                      class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-50"
                    >
                      + Child
                    </button>
                    <button
                      @click="openEditModal(cat)"
                      class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-50"
                    >
                      Edit
                    </button>
                    <button
                      @click="openDeleteConfirm(cat)"
                      class="rounded-xl border border-rose-100 px-3 py-1.5 text-xs font-medium text-rose-500 transition hover:bg-rose-50"
                    >
                      Delete
                    </button>
                  </div>
                </td>
              </tr>

              <!-- Children rows -->
              <template v-if="expandedIds.has(cat.id!)">
                <tr v-if="loadingChildren[cat.id!]">
                  <td colspan="6" class="bg-slate-50/40 py-4 text-center text-sm text-slate-400">
                    Loading subcategories…
                  </td>
                </tr>
                <tr
                  v-else
                  v-for="child in childrenMap[cat.id!]"
                  :key="child.id"
                  class="bg-slate-50/40 text-sm text-slate-600 transition hover:bg-slate-50/70"
                >
                  <td class="px-4 py-3"></td>

                  <td class="px-6 py-3">
                    <div class="flex items-center gap-3 pl-6">
                      <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-slate-300"></span>
                      <p class="font-medium text-slate-700">{{ child.name }}</p>
                    </div>
                  </td>

                  <td class="max-w-xs px-6 py-3">
                    <p class="truncate text-slate-400">{{ child.description || '—' }}</p>
                  </td>

                  <td class="px-6 py-3 text-slate-400">{{ child.order ?? 0 }}</td>

                  <td class="px-6 py-3">
                    <span
                      :class="child.is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-400'"
                      class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em]"
                    >
                      {{ child.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>

                  <td class="px-6 py-3">
                    <div class="flex items-center justify-end gap-2">
                      <button
                        @click="openEditModal(child)"
                        class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-50"
                      >
                        Edit
                      </button>
                      <button
                        @click="openDeleteConfirm(child)"
                        class="rounded-xl border border-rose-100 px-3 py-1.5 text-xs font-medium text-rose-500 transition hover:bg-rose-50"
                      >
                        Delete
                      </button>
                    </div>
                  </td>
                </tr>
              </template>

            </template>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="lastPage > 1" class="flex items-center justify-between border-t border-slate-100 px-6 py-4">
        <p class="text-sm text-slate-400">
          Page {{ currentPage }} of {{ lastPage }} &nbsp;·&nbsp; {{ total.toLocaleString() }} categories
        </p>
        <div class="flex gap-2">
          <button
            @click="fetch(currentPage - 1)"
            :disabled="currentPage === 1"
            class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-50 disabled:opacity-40"
          >
            ← Prev
          </button>
          <button
            @click="fetch(currentPage + 1)"
            :disabled="currentPage === lastPage"
            class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-50 disabled:opacity-40"
          >
            Next →
          </button>
        </div>
      </div>
    </section>
    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-950/40 backdrop-blur-sm" @click="closeModal"></div>
        <div class="relative z-10 w-full max-w-lg rounded-[2rem] bg-white p-8 shadow-2xl">

          <h3 class="text-xl font-semibold text-slate-900">
            {{ editingCategory ? 'Edit Category' : (modalParentId ? 'Add Subcategory' : 'Add Category') }}
          </h3>
          <p v-if="modalParentId && !editingCategory" class="mt-1 text-sm text-slate-500">
            Adding subcategory under <strong class="text-slate-700">{{ parentName }}</strong>
          </p>

          <form @submit.prevent="submitModal" class="mt-6 space-y-5">
            <div>
              <label class="mb-1.5 block text-sm font-medium text-slate-700">
                Name <span class="text-rose-500">*</span>
              </label>
              <input
                v-model="form.name"
                type="text"
                required
                placeholder="Category name"
                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
              />
            </div>

            <div>
              <label class="mb-1.5 block text-sm font-medium text-slate-700">Description</label>
              <textarea
                v-model="form.description"
                rows="3"
                placeholder="Short description (optional)"
                class="w-full resize-none rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
              ></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Order</label>
                <input
                  v-model.number="form.order"
                  type="number"
                  min="0"
                  class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                />
              </div>
              <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Status</label>
                <select
                  v-model="form.is_active"
                  class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                >
                  <option :value="true">Active</option>
                  <option :value="false">Inactive</option>
                </select>
              </div>
            </div>

            <div v-if="formError" class="rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-600">
              {{ formError }}
            </div>

            <div class="flex gap-3 pt-2">
              <button
                type="submit"
                :disabled="loading"
                class="flex-1 rounded-2xl bg-slate-900 py-3 text-sm font-semibold text-white transition hover:bg-slate-700 disabled:opacity-50"
              >
                {{ loading ? 'Saving…' : (editingCategory ? 'Save Changes' : 'Create') }}
              </button>
              <button
                type="button"
                @click="closeModal"
                class="flex-1 rounded-2xl border border-slate-200 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
              >
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- Delete Confirm Modal -->
    <Teleport to="body">
      <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-950/40 backdrop-blur-sm" @click="showDeleteModal = false"></div>
        <div class="relative z-10 w-full max-w-md rounded-[2rem] bg-white p-8 shadow-2xl">

          <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-50 text-rose-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
          </div>

          <h3 class="mt-4 text-xl font-semibold text-slate-900">Delete Category</h3>
          <p class="mt-3 text-sm text-slate-500">
            Are you sure you want to delete
            <strong class="text-slate-900">{{ deletingCategory?.name }}</strong>?
          </p>
          <p v-if="(deletingCategory?.children_count ?? 0) > 0" class="mt-2 text-sm text-rose-500">
            ⚠ This will also delete all {{ deletingCategory?.children_count }} subcategor{{ deletingCategory?.children_count === 1 ? 'y' : 'ies' }}.
          </p>

          <div v-if="formError" class="mt-3 rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-600">
            {{ formError }}
          </div>

          <div class="mt-6 flex gap-3">
            <button
              @click="confirmDeleteAction"
              :disabled="loading"
              class="flex-1 rounded-2xl bg-rose-600 py-3 text-sm font-semibold text-white transition hover:bg-rose-700 disabled:opacity-50"
            >
              {{ loading ? 'Deleting…' : 'Delete' }}
            </button>
            <button
              @click="showDeleteModal = false"
              class="flex-1 rounded-2xl border border-slate-200 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue';
import { useCategories, type Category } from '../composables/useCategories';

const { items, loading, childrenMap, loadingChildren, currentPage, lastPage, total, fetch, fetchChildren, create, update, remove } =
  useCategories();

onMounted(() => fetch());

// ── Expand / collapse ────────────────────────────────────────────────────────
const expandedIds = reactive(new Set<number>());

const toggleExpand = async (id: number) => {
  if (expandedIds.has(id)) {
    expandedIds.delete(id);
  } else {
    expandedIds.add(id);
    if (!childrenMap[id]) {
      await fetchChildren(id);
    }
  }
};

// ── Create / Edit modal ──────────────────────────────────────────────────────
const showModal = ref(false);
const editingCategory = ref<Category | null>(null);
const modalParentId = ref<number | null>(null);
const formError = ref<string | null>(null);

const form = reactive({
  name: '',
  description: '',
  order: 0,
  is_active: true as boolean,
});

const parentName = computed(
  () => items.value.find((c) => c.id === modalParentId.value)?.name ?? '',
);

const openCreateModal = (parentId: number | null) => {
  editingCategory.value = null;
  modalParentId.value = parentId;
  form.name = '';
  form.description = '';
  form.order = 0;
  form.is_active = true;
  formError.value = null;
  showModal.value = true;
};

const openEditModal = (cat: Category) => {
  editingCategory.value = cat;
  modalParentId.value = cat.parent_id ?? null;
  form.name = cat.name;
  form.description = cat.description ?? '';
  form.order = cat.order ?? 0;
  form.is_active = cat.is_active ?? true;
  formError.value = null;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const submitModal = async () => {
  formError.value = null;
  try {
    if (editingCategory.value?.id) {
      await update(editingCategory.value.id, {
        name: form.name,
        description: form.description,
        order: form.order,
        is_active: form.is_active,
      });
    } else {
      await create({
        name: form.name,
        description: form.description,
        order: form.order,
        is_active: form.is_active,
        parent_id: modalParentId.value,
      });
    }
    closeModal();
  } catch (err: any) {
    formError.value = err?.response?.data?.message ?? err?.message ?? 'Something went wrong.';
  }
};

// ── Delete modal ─────────────────────────────────────────────────────────────
const showDeleteModal = ref(false);
const deletingCategory = ref<Category | null>(null);

const openDeleteConfirm = (cat: Category) => {
  deletingCategory.value = cat;
  formError.value = null;
  showDeleteModal.value = true;
};

const confirmDeleteAction = async () => {
  if (!deletingCategory.value?.id) return;
  formError.value = null;
  try {
    await remove(deletingCategory.value.id, deletingCategory.value.parent_id);
    showDeleteModal.value = false;
  } catch (err: any) {
    formError.value = err?.response?.data?.message ?? err?.message ?? 'Delete failed.';
  }
};
</script>
