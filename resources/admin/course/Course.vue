<template>
  <div class="space-y-8">
    <!-- Page header -->
    <section class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
      <div>
        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Courses</p>
        <h1 class="mt-2 text-3xl font-semibold text-slate-900">Course management</h1>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-500">
          Manage all courses. Drag rows to reorder.
        </p>
      </div>
    </section>

    <!-- Table section -->
    <section class="overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white shadow-[0_20px_60px_-40px_rgba(15,23,42,0.45)]">
      <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 md:flex-row md:items-center md:justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Directory</p>
          <h2 class="mt-2 text-xl font-semibold text-slate-900">All courses</h2>
        </div>
        <button
          @click="openCreateModal"
          class="inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
          </svg>
          Add Course
        </button>
      </div>

      <!-- Loading -->
      <div v-if="loading && !items.length" class="flex items-center justify-center py-16 text-sm text-slate-400">
        Loading courses…
      </div>

      <!-- Empty -->
      <div v-else-if="!loading && !items.length" class="py-16 text-center text-sm text-slate-400">
        No courses yet. Create your first one.
      </div>

      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100">
          <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
            <tr>
              <th class="w-10 px-4 py-4"></th>
              <th class="px-6 py-4">Title</th>
              <th class="px-6 py-4">Category</th>
              <th class="px-6 py-4">Instructor</th>
              <th class="px-6 py-4">Lessons</th>
              <th class="px-6 py-4">Rating</th>
              <th class="px-6 py-4">Status</th>
              <th class="px-6 py-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 bg-white">
            <tr
              v-for="(course, index) in items"
              :key="course.id"
              draggable="true"
              @dragstart="onDragStart(index)"
              @dragover.prevent="onDragOver(index)"
              @drop="onDrop"
              @dragend="onDragEnd"
              class="text-sm text-slate-600 transition hover:bg-slate-50/50"
              :class="{
                'opacity-40': dragSourceIdx === index,
                'border-t-2 border-blue-400 bg-blue-50/40': dragOverIdx === index && dragSourceIdx !== index,
              }"
            >
              <!-- Drag handle -->
              <td class="cursor-grab px-4 py-4 text-slate-300 active:cursor-grabbing">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                  <circle cx="7" cy="4" r="1.5" /><circle cx="13" cy="4" r="1.5" />
                  <circle cx="7" cy="10" r="1.5" /><circle cx="13" cy="10" r="1.5" />
                  <circle cx="7" cy="16" r="1.5" /><circle cx="13" cy="16" r="1.5" />
                </svg>
              </td>

              <!-- Title -->
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div v-if="course.thumbnail" class="h-9 w-9 shrink-0 overflow-hidden rounded-xl bg-slate-100">
                    <img :src="course.thumbnail" :alt="course.title" class="h-full w-full object-cover" />
                  </div>
                  <div v-else class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-blue-50 text-xs font-bold uppercase text-blue-600">
                    {{ (course.title ?? '').slice(0, 2) }}
                  </div>
                  <div>
                    <p class="font-medium text-slate-900">{{ course.title }}</p>
                    <p v-if="course.description" class="mt-0.5 max-w-xs truncate text-xs text-slate-400">
                      {{ course.description }}
                    </p>
                    <div v-if="course.tags?.length" class="mt-1 flex flex-wrap gap-1">
                      <span
                        v-for="tag in course.tags"
                        :key="tag.id"
                        class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-500"
                      >
                        {{ tag.name }}
                      </span>
                    </div>
                  </div>
                </div>
              </td>

              <!-- Category -->
              <td class="px-6 py-4 text-slate-500">
                <span v-if="course.category" class="rounded-full bg-violet-50 px-2.5 py-1 text-xs font-medium text-violet-600">
                  {{ course.category.name }}
                </span>
                <span v-else class="text-slate-300">—</span>
              </td>

              <!-- Instructor -->
              <td class="px-6 py-4 text-slate-500">{{ course.instructor?.name ?? '—' }}</td>

              <!-- Lessons count -->
              <td class="px-6 py-4 text-slate-500">
                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold">
                  {{ course.lessons_count ?? 0 }}
                </span>
              </td>

              <!-- Rating -->
              <td class="px-6 py-4">
                <div v-if="course.reviews_count" class="flex items-center gap-1.5">
                  <span class="text-sm font-semibold text-slate-900">
                    {{ Number(course.reviews_avg_rating ?? 0).toFixed(1) }}
                  </span>
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.17c.969 0 1.371 1.24.588 1.81l-3.374 2.452a1 1 0 00-.364 1.118l1.287 3.966c.3.921-.755 1.688-1.54 1.118l-3.374-2.452a1 1 0 00-1.176 0l-3.374 2.452c-.784.57-1.838-.197-1.54-1.118l1.287-3.966a1 1 0 00-.364-1.118L2.055 9.394c-.783-.57-.38-1.81.588-1.81h4.17a1 1 0 00.95-.69L9.05 2.927z" />
                  </svg>
                  <span class="text-xs text-slate-400">({{ course.reviews_count }})</span>
                </div>
                <span v-else class="text-xs text-slate-300">No reviews</span>
              </td>

              <!-- Status -->
              <td class="px-6 py-4">
                <span
                  :class="course.is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-400'"
                  class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em]"
                >
                  {{ course.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>

              <!-- Actions -->
              <td class="px-6 py-4">
                <div class="flex items-center justify-end gap-2">
                  <RouterLink
                    :to="`/courses/${course.id}/lessons`"
                    class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-50"
                  >
                    Lessons
                  </RouterLink>
                  <button
                    @click="openReviewsModal(course)"
                    class="rounded-xl border border-amber-200 px-3 py-1.5 text-xs font-medium text-amber-600 transition hover:bg-amber-50"
                  >
                    Reviews
                  </button>
                  <button
                    @click="openEditModal(course)"
                    class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-50"
                  >
                    Edit
                  </button>
                  <button
                    @click="openDeleteConfirm(course)"
                    class="rounded-xl border border-rose-100 px-3 py-1.5 text-xs font-medium text-rose-500 transition hover:bg-rose-50"
                  >
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <!-- Pagination -->
      <div v-if="lastPage > 1" class="flex items-center justify-between border-t border-slate-100 px-6 py-4">
        <p class="text-sm text-slate-400">
          Page {{ currentPage }} of {{ lastPage }} &nbsp;·&nbsp; {{ total.toLocaleString() }} courses
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
        <div class="relative z-10 w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-[2rem] bg-white p-8 shadow-2xl">

          <h3 class="text-xl font-semibold text-slate-900">
            {{ editingCourse ? 'Edit Course' : 'Add Course' }}
          </h3>

          <form @submit.prevent="submitModal" class="mt-6 space-y-5">
            <!-- Title -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-slate-700">
                Title <span class="text-rose-500">*</span>
              </label>
              <input
                v-model="form.title"
                type="text"
                required
                placeholder="Course title"
                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
              />
            </div>

            <!-- Description -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-slate-700">Description</label>
              <textarea
                v-model="form.description"
                rows="3"
                placeholder="Short description (optional)"
                class="w-full resize-none rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
              ></textarea>
            </div>

            <!-- Thumbnail -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-slate-700">Thumbnail</label>
              <input
                type="file"
                accept="image/*"
                @change="onThumbnailSelected"
                class="w-full rounded-2xl border border-dashed border-slate-300 px-4 py-3 text-sm text-slate-700 file:mr-4 file:rounded-xl file:border-0 file:bg-slate-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white"
              />
              <p class="mt-2 text-xs text-slate-500">Upload a course cover image. Recommended size under 10 MB.</p>

              <div v-if="uploadingThumbnail" class="mt-3 overflow-hidden rounded-full bg-slate-100">
                <div class="h-2 bg-blue-600 transition-all" :style="{ width: `${thumbnailUploadProgress}%` }"></div>
              </div>
              <p v-if="uploadingThumbnail" class="mt-2 text-xs text-blue-600">Uploading thumbnail… {{ thumbnailUploadProgress }}%</p>

              <div v-if="thumbnailPreviewUrl" class="mt-3 overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                <img :src="thumbnailPreviewUrl" alt="Thumbnail preview" class="h-40 w-full object-cover" />
              </div>
            </div>

            <!-- Instructor -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-slate-700">
                Instructor <span class="text-rose-500">*</span>
              </label>
              <select
                v-model.number="form.user_id"
                required
                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
              >
                <option :value="0" disabled>Select instructor…</option>
                <option v-for="ins in instructors" :key="ins.id" :value="ins.id">
                  {{ ins.name }}
                </option>
              </select>
              <p v-if="!instructors.length" class="mt-1.5 text-xs text-slate-400">
                No instructors found. Assign the "Instructor" role to a user first.
              </p>
            </div>

            <!-- Category -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-slate-700">Category</label>
              <select
                v-model.number="form.category_id"
                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
              >
                <option :value="null">No category</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                  {{ cat.name }}
                </option>
              </select>
            </div>

            <!-- Tags -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-slate-700">Tags</label>
              <!-- selected chips -->
              <div v-if="form.tags.length" class="mb-2 flex flex-wrap gap-1.5">
                <span
                  v-for="tag in form.tags"
                  :key="tag"
                  class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-600"
                >
                  {{ tag }}
                  <button type="button" @click="removeTag(tag)" class="ml-0.5 rounded-full text-blue-400 hover:text-blue-700">&times;</button>
                </span>
              </div>
              <!-- type-to-add input -->
              <input
                v-model="tagInput"
                @keydown.enter.prevent="addTag"
                @keydown.,.prevent="addTag"
                type="text"
                placeholder="Type a tag and press Enter or comma"
                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
              />
              <!-- quick-select from existing tags -->
              <div v-if="allTags.length" class="mt-2 flex flex-wrap gap-1.5">
                <button
                  v-for="t in allTags"
                  :key="t.id"
                  type="button"
                  @click="toggleTag(t.name)"
                  :class="form.tags.includes(t.name)
                    ? 'bg-blue-500 text-white'
                    : 'bg-slate-100 text-slate-500 hover:bg-slate-200'"
                  class="rounded-full px-2.5 py-1 text-xs font-medium transition"
                >
                  {{ t.name }}
                </button>
              </div>
            </div>

            <!-- Status -->
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

            <div v-if="formError" class="rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-600">
              {{ formError }}
            </div>

            <div class="flex gap-3 pt-2">
              <button
                type="submit"
                :disabled="loading || uploadingThumbnail"
                class="flex-1 rounded-2xl bg-slate-900 py-3 text-sm font-semibold text-white transition hover:bg-slate-700 disabled:opacity-50"
              >
                {{ uploadingThumbnail ? 'Uploading thumbnail…' : (loading ? 'Saving…' : (editingCourse ? 'Save Changes' : 'Create')) }}
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

          <h3 class="mt-4 text-xl font-semibold text-slate-900">Delete Course</h3>
          <p class="mt-3 text-sm text-slate-500">
            Are you sure you want to delete
            <strong class="text-slate-900">{{ deletingCourse?.title }}</strong>?
          </p>
          <p class="mt-2 text-sm text-rose-500">⚠ All lessons in this course will also be deleted.</p>

          <div v-if="formError" class="mt-3 rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-600">
            {{ formError }}
          </div>

          <div class="mt-6 flex gap-3">
            <button
              @click="confirmDelete"
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

    <!-- Reviews Modal (read-only) -->
    <Teleport to="body">
      <div v-if="showReviewsModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-950/40 backdrop-blur-sm" @click="showReviewsModal = false"></div>
        <div class="relative z-10 flex w-full max-w-2xl flex-col rounded-[2rem] bg-white shadow-2xl" style="max-height: 85vh;">

          <!-- Header -->
          <div class="flex items-center justify-between border-b border-slate-100 px-8 py-6">
            <div>
              <h3 class="text-xl font-semibold text-slate-900">Reviews</h3>
              <p class="mt-1 text-sm text-slate-500">{{ reviewingCourse?.title }}</p>
            </div>
            <div v-if="reviews.length" class="flex items-center gap-2 rounded-2xl bg-amber-50 px-4 py-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.17c.969 0 1.371 1.24.588 1.81l-3.374 2.452a1 1 0 00-.364 1.118l1.287 3.966c.3.921-.755 1.688-1.54 1.118l-3.374-2.452a1 1 0 00-1.176 0l-3.374 2.452c-.784.57-1.838-.197-1.54-1.118l1.287-3.966a1 1 0 00-.364-1.118L2.055 9.394c-.783-.57-.38-1.81.588-1.81h4.17a1 1 0 00.95-.69L9.05 2.927z" />
              </svg>
              <span class="text-lg font-bold text-amber-600">{{ avgRating }}</span>
              <span class="text-sm text-slate-400">/ 5 &nbsp;·&nbsp; {{ reviews.length }} reviews</span>
            </div>
          </div>

          <!-- List -->
          <div class="flex-1 overflow-y-auto px-8 py-6">
            <div v-if="reviewsLoading" class="py-10 text-center text-sm text-slate-400">
              Loading reviews…
            </div>
            <div v-else-if="!reviews.length" class="py-10 text-center text-sm text-slate-400">
              No reviews yet for this course.
            </div>
            <ul v-else class="space-y-5">
              <li
                v-for="review in reviews"
                :key="review.id"
                class="rounded-2xl border border-slate-100 bg-slate-50/60 px-5 py-4"
              >
                <div class="flex items-start justify-between gap-4">
                  <div>
                    <p class="text-sm font-semibold text-slate-900">{{ review.user?.name ?? '—' }}</p>
                    <p class="text-xs text-slate-400">{{ review.user?.email }}</p>
                  </div>
                  <div class="flex shrink-0 items-center gap-1">
                    <template v-for="star in 5" :key="star">
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4"
                        :class="star <= review.rating ? 'text-amber-400' : 'text-slate-200'"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                      >
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.17c.969 0 1.371 1.24.588 1.81l-3.374 2.452a1 1 0 00-.364 1.118l1.287 3.966c.3.921-.755 1.688-1.54 1.118l-3.374-2.452a1 1 0 00-1.176 0l-3.374 2.452c-.784.57-1.838-.197-1.54-1.118l1.287-3.966a1 1 0 00-.364-1.118L2.055 9.394c-.783-.57-.38-1.81.588-1.81h4.17a1 1 0 00.95-.69L9.05 2.927z" />
                      </svg>
                    </template>
                    <span class="ml-1 text-xs font-semibold text-slate-600">{{ review.rating }}/5</span>
                  </div>
                </div>
                <p v-if="review.comment" class="mt-3 text-sm leading-relaxed text-slate-600">
                  {{ review.comment }}
                </p>
                <p class="mt-2 text-right text-xs text-slate-300">
                  {{ new Date(review.created_at).toLocaleDateString('vi-VN') }}
                </p>
              </li>
            </ul>
          </div>

          <!-- Footer -->
          <div class="border-t border-slate-100 px-8 py-5">
            <button
              @click="showReviewsModal = false"
              class="w-full rounded-2xl border border-slate-200 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
            >
              Close
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue';
import axios from 'axios';
import { useCourses, type Course } from '../composables/useCourses';

const { items, loading, currentPage, lastPage, total, fetch, create, update, remove, reorder, presignThumbnailUpload } = useCourses();

// ── Instructors ──────────────────────────────────────────────────────────────
type Instructor = { id: number; name: string; email: string };
const instructors = ref<Instructor[]>([]);

const fetchInstructors = async () => {
    try {
        const res = await axios.get('/admin/api/instructors');
        instructors.value = res.data;
    } catch {
        instructors.value = [];
    }
};

// ── Categories ───────────────────────────────────────────────────────────────
type CategoryOption = { id: number; name: string };
const categories = ref<CategoryOption[]>([]);

const fetchCategories = async () => {
    try {
        const res = await axios.get('/admin/api/categories');
        categories.value = res.data;
    } catch {
        categories.value = [];
    }
};

// ── Tags ─────────────────────────────────────────────────────────────────────
type TagOption = { id: number; name: string; slug: string };
const allTags    = ref<TagOption[]>([]);
const tagInput   = ref('');

const fetchAllTags = async () => {
    try {
        const res = await axios.get('/admin/api/tags');
        allTags.value = res.data;
    } catch {
        allTags.value = [];
    }
};

const addTag = () => {
    const name = tagInput.value.trim().replace(/,$/, '').trim();
    if (name && !form.tags.includes(name)) {
        form.tags.push(name);
    }
    tagInput.value = '';
};

const removeTag = (name: string) => {
    form.tags = form.tags.filter((t) => t !== name);
};

const toggleTag = (name: string) => {
    if (form.tags.includes(name)) {
        removeTag(name);
    } else {
        form.tags.push(name);
    }
};

onMounted(() => {
    fetch();
    fetchInstructors();
    fetchCategories();
    fetchAllTags();
});

// ── Drag & Drop ──────────────────────────────────────────────────────────────
const dragSourceIdx = ref<number | null>(null);
const dragOverIdx  = ref<number | null>(null);

const onDragStart = (index: number) => {
    dragSourceIdx.value = index;
};

const onDragOver = (index: number) => {
    dragOverIdx.value = index;
};

const onDragEnd = () => {
    dragSourceIdx.value = null;
    dragOverIdx.value   = null;
};

const onDrop = async () => {
    if (
        dragSourceIdx.value === null ||
        dragOverIdx.value === null ||
        dragSourceIdx.value === dragOverIdx.value
    ) {
        onDragEnd();
        return;
    }

    const arr = [...items.value];
    const [moved] = arr.splice(dragSourceIdx.value, 1);
    arr.splice(dragOverIdx.value, 0, moved);
    arr.forEach((item, idx) => { item.order = idx; });
    items.value = arr;

    onDragEnd();

    await reorder(arr.map((item, idx) => ({ id: item.id!, order: idx })));
};

// ── Create / Edit modal ──────────────────────────────────────────────────────
const showModal = ref(false);
const editingCourse = ref<Course | null>(null);
const formError = ref<string | null>(null);
const uploadingThumbnail = ref(false);
const thumbnailUploadProgress = ref(0);
const thumbnailPreviewUrl = ref<string>('');
const MAX_THUMBNAIL_SIZE = 10 * 1024 * 1024;

const form = reactive({
    title:       '',
    description: '',
    thumbnail:   '',
    user_id:     0 as number,
    category_id: null as number | null,
    tags:        [] as string[],
    is_active:   true as boolean,
});

const openCreateModal = () => {
    editingCourse.value = null;
    form.title = '';
    form.description = '';
    form.thumbnail = '';
    thumbnailPreviewUrl.value = '';
    form.user_id = instructors.value[0]?.id ?? 0;
    form.category_id = null;
    form.tags = [];
    form.is_active = true;
    tagInput.value = '';
    formError.value = null;
    uploadingThumbnail.value = false;
    thumbnailUploadProgress.value = 0;
    showModal.value = true;
};

const openEditModal = (course: Course) => {
    editingCourse.value = course;
    form.title = course.title;
    form.description = course.description ?? '';
    form.thumbnail = '';
    thumbnailPreviewUrl.value = course.thumbnail ?? '';
    form.user_id = course.instructor?.id ?? course.user_id;
    form.category_id = course.category?.id ?? course.category_id ?? null;
    form.tags = course.tags?.map((t) => t.name) ?? [];
    form.is_active = course.is_active ?? true;
    tagInput.value = '';
    formError.value = null;
    uploadingThumbnail.value = false;
    thumbnailUploadProgress.value = 0;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    uploadingThumbnail.value = false;
    thumbnailUploadProgress.value = 0;
    thumbnailPreviewUrl.value = '';
};

const onThumbnailSelected = async (event: Event) => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];

    if (!file) {
        return;
    }

    if (file.size > MAX_THUMBNAIL_SIZE) {
        formError.value = 'Thumbnail size must be 10 MB or smaller.';
        input.value = '';
        return;
    }

    formError.value = null;
    uploadingThumbnail.value = true;
    thumbnailUploadProgress.value = 0;

    try {
        const presigned = await presignThumbnailUpload(file);
        const safeHeaders = Object.fromEntries(
            Object.entries(presigned.headers ?? {}).filter(([key]) => !['host', 'content-length'].includes(key.toLowerCase()))
        );

        await axios.put(presigned.upload_url, file, {
            headers: {
                ...safeHeaders,
                'Content-Type': file.type || 'image/png',
            },
            onUploadProgress: (progressEvent) => {
                const total = progressEvent.total || file.size;
                if (total) {
                    thumbnailUploadProgress.value = Math.round((progressEvent.loaded / total) * 100);
                }
            },
        });

        thumbnailUploadProgress.value = 100;
        form.thumbnail = presigned.path;
        thumbnailPreviewUrl.value = presigned.thumbnail_url;
    } catch (err: any) {
        formError.value = err?.response?.data?.message ?? err?.message ?? 'Thumbnail upload failed.';
    } finally {
        uploadingThumbnail.value = false;
        input.value = '';
    }
};

const submitModal = async () => {
    formError.value = null;

    if (uploadingThumbnail.value) {
        formError.value = 'Please wait for the thumbnail upload to finish.';
        return;
    }

    try {
        const payload = {
            title:       form.title,
            description: form.description,
            thumbnail:   form.thumbnail || undefined,
            user_id:     form.user_id,
            category_id: form.category_id,
            tags:        form.tags,
            is_active:   form.is_active,
        };
        if (editingCourse.value?.id) {
            await update(editingCourse.value.id, payload);
        } else {
            await create(payload);
        }
        closeModal();
    } catch (err: any) {
        formError.value = err?.response?.data?.message ?? err?.message ?? 'Something went wrong.';
    }
};

// ── Delete modal ─────────────────────────────────────────────────────────────
const showDeleteModal  = ref(false);
const deletingCourse   = ref<Course | null>(null);

const openDeleteConfirm = (course: Course) => {
    deletingCourse.value = course;
    formError.value      = null;
    showDeleteModal.value = true;
};

const confirmDelete = async () => {
    if (!deletingCourse.value?.id) return;
    formError.value = null;
    try {
        await remove(deletingCourse.value.id);
        showDeleteModal.value = false;
    } catch (err: any) {
        formError.value = err?.response?.data?.message ?? err?.message ?? 'Delete failed.';
    }
};

// ── Reviews modal (read-only) ─────────────────────────────────────────────────
type Review = {
    id: number;
    rating: number;
    comment: string | null;
    created_at: string;
    user: { id: number; name: string; email: string } | null;
};

const showReviewsModal = ref(false);
const reviewingCourse  = ref<Course | null>(null);
const reviews          = ref<Review[]>([]);
const reviewsLoading   = ref(false);

const avgRating = computed(() => {
    if (!reviews.value.length) return '0.0';
    const sum = reviews.value.reduce((acc, r) => acc + r.rating, 0);
    return (sum / reviews.value.length).toFixed(1);
});

const openReviewsModal = async (course: Course) => {
    reviewingCourse.value  = course;
    reviews.value          = [];
    reviewsLoading.value   = true;
    showReviewsModal.value = true;

    try {
        const res = await axios.get(`/admin/api/courses/${course.id}/reviews`);
        reviews.value = res.data.reviews;
    } finally {
        reviewsLoading.value = false;
    }
};
</script>
