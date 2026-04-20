<template>
  <div class="space-y-8">
    <!-- Page header -->
    <section class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
      <div>
        <RouterLink
          to="/courses"
          class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-[0.3em] text-slate-400 transition hover:text-slate-600"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
          </svg>
          Back to Courses
        </RouterLink>
        <h1 class="mt-2 text-3xl font-semibold text-slate-900">
          {{ course?.title ?? 'Lessons' }}
        </h1>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-500">
          Manage lessons for this course. Drag rows to reorder.
        </p>
      </div>
      <button
        @click="openCreateModal"
        class="inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
        </svg>
        Add Lesson
      </button>
    </section>

    <!-- Table section -->
    <section class="overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white shadow-[0_20px_60px_-40px_rgba(15,23,42,0.45)]">
      <div class="border-b border-slate-100 px-6 py-5">
        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Directory</p>
        <h2 class="mt-2 text-xl font-semibold text-slate-900">All lessons</h2>
      </div>

      <!-- Loading -->
      <div v-if="loading && !items.length" class="flex items-center justify-center py-16 text-sm text-slate-400">
        Loading lessons…
      </div>

      <!-- Empty -->
      <div v-else-if="!loading && !items.length" class="py-16 text-center text-sm text-slate-400">
        No lessons yet. Create the first one.
      </div>

      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100">
          <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
            <tr>
              <th class="w-10 px-4 py-4"></th>
              <th class="px-6 py-4">Title</th>
              <th class="px-6 py-4">Duration</th>
              <th class="px-6 py-4">Quiz</th>
              <th class="px-6 py-4">Status</th>
              <th class="px-6 py-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 bg-white">
            <tr
              v-for="(lesson, index) in items"
              :key="lesson.id"
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
                  <div class="grid h-8 w-8 shrink-0 place-items-center rounded-xl bg-indigo-50 text-xs font-bold text-indigo-500">
                    {{ (index + 1).toString().padStart(2, '0') }}
                  </div>
                  <p class="font-medium text-slate-900">{{ lesson.title }}</p>
                </div>
              </td>

              <!-- Duration -->
              <td class="px-6 py-4 text-slate-500">
                <span v-if="lesson.duration">{{ lesson.duration }} min</span>
                <span v-else class="text-slate-300">—</span>
              </td>

              <!-- Quiz -->
              <td class="px-6 py-4">
                <button
                  @click="openQuizPanel(lesson)"
                  class="inline-flex items-center gap-1.5 rounded-xl border border-indigo-200 px-3 py-1.5 text-xs font-medium text-indigo-600 transition hover:bg-indigo-50"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                  </svg>
                  Quiz
                </button>
              </td>

              <!-- Status -->
              <td class="px-6 py-4">
                <span
                  :class="lesson.is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-400'"
                  class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em]"
                >
                  {{ lesson.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>

              <!-- Actions -->
              <td class="px-6 py-4">
                <div class="flex items-center justify-end gap-2">
                  <button
                    @click="openEditModal(lesson)"
                    class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-50"
                  >
                    Edit
                  </button>
                  <button
                    @click="openDeleteConfirm(lesson)"
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
    </section>

    <!-- Create / Edit Modal -->
    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-950/40 backdrop-blur-sm" @click="closeModal"></div>
        <div class="relative z-10 w-full max-w-lg rounded-[2rem] bg-white p-8 shadow-2xl">

          <h3 class="text-xl font-semibold text-slate-900">
            {{ editingLesson ? 'Edit Lesson' : 'Add Lesson' }}
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
                placeholder="Lesson title"
                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
              />
            </div>

            <!-- Video upload -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-slate-700">Lesson Video</label>
              <input
                type="file"
                accept="video/*"
                @change="onVideoSelected"
                class="w-full rounded-2xl border border-dashed border-slate-300 px-4 py-3 text-sm text-slate-700 file:mr-4 file:rounded-xl file:border-0 file:bg-slate-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white"
              />
              <p class="mt-2 text-xs text-slate-500">Upload directly to MinIO with a presigned URL. Max file size: 1.5 GB.</p>

              <div v-if="uploadingVideo" class="mt-3 overflow-hidden rounded-full bg-slate-100">
                <div class="h-2 bg-blue-600 transition-all" :style="{ width: `${uploadProgress}%` }"></div>
              </div>
              <p v-if="uploadingVideo" class="mt-2 text-xs text-blue-600">Uploading video… {{ uploadProgress }}%</p>
              <p v-else-if="form.video_url" class="mt-2 text-xs text-emerald-600">Video uploaded and ready.</p>
            </div>

            <!-- Video URL -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-slate-700">Video URL</label>
              <input
                v-model="form.video_url"
                type="url"
                placeholder="Auto-filled after upload or paste an external URL"
                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
              />
            </div>

            <!-- Content -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-slate-700">Content</label>
              <textarea
                v-model="form.content"
                rows="4"
                placeholder="Lesson content (optional)"
                class="w-full resize-none rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
              ></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <!-- Duration -->
              <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Duration (min)</label>
                <input
                  v-model.number="form.duration"
                  type="number"
                  min="0"
                  placeholder="30"
                  class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                />
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
                {{ loading ? 'Saving…' : (editingLesson ? 'Save Changes' : 'Create') }}
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

    <!-- Quiz Panel Modal -->
    <Teleport to="body">
      <div v-if="showQuizPanel" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-950/40 backdrop-blur-sm" @click="closeQuizPanel"></div>
        <div class="relative z-10 flex w-full max-w-3xl flex-col rounded-[2rem] bg-white shadow-2xl" style="max-height: 90vh;">

          <!-- Header -->
          <div class="flex items-center justify-between border-b border-slate-100 px-8 py-6">
            <div>
              <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Quiz</p>
              <h3 class="mt-1 text-xl font-semibold text-slate-900">{{ quizLesson?.title }}</h3>
              <p v-if="quiz" class="mt-0.5 text-sm text-slate-500">{{ quiz.title }}</p>
            </div>
            <button @click="closeQuizPanel" class="rounded-xl p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>

          <!-- Body -->
          <div class="flex-1 overflow-y-auto px-8 py-6">
            <div v-if="quizLoading" class="py-10 text-center text-sm text-slate-400">Loading quiz…</div>
            <div v-else-if="!quiz" class="flex flex-col items-center gap-3 py-14">
              <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <p class="text-sm text-slate-500">This lesson has no quiz yet.</p>
            </div>
            <div v-else class="space-y-6">
              <!-- Question list -->
              <div
                v-for="(question, qi) in quiz.questions"
                :key="question.id"
                class="rounded-2xl border border-slate-200 bg-slate-50/40 p-5"
              >
                <!-- View mode -->
                <template v-if="editingQuestionId !== question.id">
                  <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                      <div class="flex items-center gap-2">
                        <span class="grid h-6 w-6 shrink-0 place-items-center rounded-lg bg-indigo-100 text-xs font-bold text-indigo-600">
                          {{ qi + 1 }}
                        </span>
                        <span
                          :class="question.type === 2 ? 'bg-violet-50 text-violet-600' : 'bg-sky-50 text-sky-600'"
                          class="rounded-full px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider"
                        >
                          {{ question.type === 2 ? 'Multiple' : 'Single' }}
                        </span>
                      </div>
                      <p class="mt-2 text-sm font-medium text-slate-900">{{ question.content }}</p>
                    </div>
                    <div class="flex shrink-0 items-center gap-2">
                      <button
                        @click="startEditQuestion(question)"
                        class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-white"
                      >
                        Edit
                      </button>
                      <button
                        @click="deleteQuestion(question.id)"
                        :disabled="quizActionLoading"
                        class="rounded-xl border border-rose-100 px-3 py-1.5 text-xs font-medium text-rose-500 transition hover:bg-rose-50 disabled:opacity-40"
                      >
                        Delete
                      </button>
                    </div>
                  </div>

                  <!-- Options -->
                  <ul class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
                    <li
                      v-for="opt in question.options"
                      :key="opt.id"
                      class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm"
                      :class="opt.is_correct ? 'bg-emerald-50 text-emerald-700' : 'bg-white border border-slate-100 text-slate-600'"
                    >
                      <span
                        class="grid h-5 w-5 shrink-0 place-items-center rounded-md text-[10px] font-bold"
                        :class="opt.is_correct ? 'bg-emerald-200 text-emerald-800' : 'bg-slate-100 text-slate-500'"
                      >
                        {{ ['A','B','C','D'][opt.label - 1] }}
                      </span>
                      {{ opt.content }}
                      <svg v-if="opt.is_correct" xmlns="http://www.w3.org/2000/svg" class="ml-auto h-4 w-4 shrink-0 text-emerald-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                      </svg>
                    </li>
                  </ul>
                </template>

                <!-- Edit mode -->
                <template v-else>
                  <div class="space-y-4">
                    <div class="flex items-center gap-2">
                      <span class="grid h-6 w-6 shrink-0 place-items-center rounded-lg bg-indigo-100 text-xs font-bold text-indigo-600">
                        {{ qi + 1 }}
                      </span>
                      <span class="text-sm font-semibold text-slate-700">Editing question</span>
                    </div>

                    <!-- Question content -->
                    <div>
                      <label class="mb-1 block text-xs font-medium text-slate-600">Question</label>
                      <!-- text type: textarea -->
                      <textarea
                        v-if="editForm.type === 1"
                        v-model="editForm.content"
                        rows="2"
                        class="w-full resize-none rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                      ></textarea>
                      <!-- media type: file upload -->
                      <div v-else class="space-y-2">
                        <div v-if="editForm.content" class="flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-2 text-xs text-slate-600">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-indigo-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" /></svg>
                          <span class="truncate">{{ editForm.content }}</span>
                        </div>
                        <label class="flex cursor-pointer items-center gap-2 rounded-2xl border-2 border-dashed border-slate-200 px-4 py-3 text-xs text-slate-500 hover:border-indigo-300 hover:text-indigo-500 transition">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                          {{ editMediaUploading ? `Uploading ${editMediaProgress}%…` : (editForm.content ? 'Replace file' : 'Upload file') }}
                          <input
                            type="file"
                            class="sr-only"
                            :accept="editForm.type === 2 ? 'image/*' : editForm.type === 3 ? 'audio/*' : 'video/*'"
                            @change="handleEditMediaChange"
                            :disabled="editMediaUploading"
                          />
                        </label>
                      </div>
                    </div>

                    <!-- Question Type -->
                    <div>
                      <label class="mb-1 block text-xs font-medium text-slate-600">Question Type</label>
                      <div class="flex flex-wrap gap-2">
                        <label v-for="qt in [{ val: 1, label: 'Text' }, { val: 2, label: 'Image' }, { val: 3, label: 'Audio' }, { val: 4, label: 'Video' }]" :key="qt.val"
                          class="flex cursor-pointer items-center gap-1.5 rounded-xl border px-3 py-2 text-xs font-medium transition"
                          :class="editForm.type === qt.val ? 'border-indigo-400 bg-indigo-50 text-indigo-700' : 'border-slate-200 text-slate-500 hover:bg-slate-50'"
                        >
                          <input type="radio" :value="qt.val" v-model="editForm.type" class="sr-only" />
                          {{ qt.label }}
                        </label>
                      </div>
                    </div>

                    <!-- Answer Type -->
                    <div>
                      <label class="mb-1 block text-xs font-medium text-slate-600">Answer Type</label>
                      <div class="flex gap-3">
                        <label class="flex cursor-pointer items-center gap-2 rounded-2xl border px-4 py-2.5 text-sm transition"
                          :class="editForm.answer_type === 1 ? 'border-blue-400 bg-blue-50 text-blue-700' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                          <input type="radio" :value="1" v-model="editForm.answer_type" class="sr-only" />
                          Single choice (1 đáp án đúng)
                        </label>
                        <label class="flex cursor-pointer items-center gap-2 rounded-2xl border px-4 py-2.5 text-sm transition"
                          :class="editForm.answer_type === 2 ? 'border-violet-400 bg-violet-50 text-violet-700' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                          <input type="radio" :value="2" v-model="editForm.answer_type" class="sr-only" />
                          Multiple choice (nhiều đáp án đúng)
                        </label>
                      </div>
                    </div>

                    <!-- Options A-D -->
                    <div class="space-y-2">
                      <label class="block text-xs font-medium text-slate-600">Options</label>
                      <div
                        v-for="(opt, oi) in editForm.options"
                        :key="opt.id"
                        class="flex items-center gap-3 rounded-2xl border px-3 py-2.5 transition"
                        :class="opt.is_correct ? 'border-emerald-300 bg-emerald-50' : 'border-slate-200 bg-white'"
                      >
                        <span class="grid h-6 w-6 shrink-0 place-items-center rounded-lg text-xs font-bold"
                          :class="opt.is_correct ? 'bg-emerald-200 text-emerald-800' : 'bg-slate-100 text-slate-600'"
                        >
                          {{ ['A','B','C','D'][oi] }}
                        </span>
                        <input
                          v-model="opt.content"
                          type="text"
                          class="min-w-0 flex-1 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-900 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                          :placeholder="`Nhập nội dung đáp án ${['A','B','C','D'][oi]}…`"
                        />
                        <label class="flex shrink-0 cursor-pointer items-center gap-1.5 text-xs font-medium"
                          :class="opt.is_correct ? 'text-emerald-600' : 'text-slate-400'">
                          <input
                            v-if="editForm.answer_type === 2"
                            type="checkbox"
                            v-model="opt.is_correct"
                            class="h-4 w-4 rounded accent-emerald-500"
                          />
                          <input
                            v-else
                            type="radio"
                            :value="oi"
                            :checked="opt.is_correct"
                            @change="setSingleCorrect(oi)"
                            class="h-4 w-4 accent-emerald-500"
                          />
                          Correct
                        </label>
                      </div>
                    </div>

                    <div v-if="editQuestionError" class="rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-600">
                      {{ editQuestionError }}
                    </div>

                    <div class="flex gap-3 pt-1">
                      <button
                        @click="saveQuestion(question.id)"
                        :disabled="quizSaving"
                        class="flex-1 rounded-2xl bg-slate-900 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700 disabled:opacity-50"
                      >
                        {{ quizSaving ? 'Saving…' : 'Save' }}
                      </button>
                      <button
                        @click="cancelEditQuestion"
                        class="flex-1 rounded-2xl border border-slate-200 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                      >
                        Cancel
                      </button>
                    </div>
                  </div>
                </template>
              </div>

              <!-- Add Question button -->
              <button
                @click="addQuestion"
                :disabled="quizActionLoading"
                class="flex w-full items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-slate-200 py-3.5 text-sm font-medium text-slate-400 transition hover:border-indigo-300 hover:text-indigo-500 disabled:opacity-40"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                {{ quizActionLoading ? 'Adding…' : 'Add Question' }}
              </button>
            </div>
          </div>

          <!-- Footer -->
          <div class="flex gap-3 border-t border-slate-100 px-8 py-5">
            <!-- No quiz: Add Quiz -->
            <template v-if="!quiz && !quizLoading">
              <button
                @click="addQuiz"
                :disabled="quizActionLoading"
                class="flex-1 inline-flex items-center justify-center gap-2 rounded-2xl bg-indigo-600 py-3 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-50"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                {{ quizActionLoading ? 'Creating…' : 'Add Quiz' }}
              </button>
              <button
                @click="closeQuizPanel"
                class="flex-1 rounded-2xl border border-slate-200 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
              >
                Close
              </button>
            </template>
            <!-- Has quiz -->
            <template v-if="quiz">
              <template v-if="!confirmDeleteQuiz">
                <button
                  @click="confirmDeleteQuiz = true"
                  :disabled="quizActionLoading"
                  class="flex-1 rounded-2xl border border-rose-200 py-3 text-sm font-semibold text-rose-500 transition hover:bg-rose-50 disabled:opacity-50"
                >
                  Delete Quiz
                </button>
                <button
                  @click="closeQuizPanel"
                  class="flex-1 rounded-2xl border border-slate-200 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                >
                  Close
                </button>
              </template>
              <template v-else>
                <button
                  @click="deleteQuiz"
                  :disabled="quizActionLoading"
                  class="flex-1 rounded-2xl bg-rose-600 py-3 text-sm font-semibold text-white transition hover:bg-rose-700 disabled:opacity-50"
                >
                  {{ quizActionLoading ? 'Deleting…' : 'Confirm Delete' }}
                </button>
                <button
                  @click="confirmDeleteQuiz = false"
                  class="flex-1 rounded-2xl border border-slate-200 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                >
                  Cancel
                </button>
              </template>
            </template>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Delete Confirm Modal -->
    <Teleport to="body">
      <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">        <div class="absolute inset-0 bg-slate-950/40 backdrop-blur-sm" @click="showDeleteModal = false"></div>
        <div class="relative z-10 w-full max-w-md rounded-[2rem] bg-white p-8 shadow-2xl">

          <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-50 text-rose-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
          </div>

          <h3 class="mt-4 text-xl font-semibold text-slate-900">Delete Lesson</h3>
          <p class="mt-3 text-sm text-slate-500">
            Are you sure you want to delete
            <strong class="text-slate-900">{{ deletingLesson?.title }}</strong>?
          </p>
          <p class="mt-2 text-sm text-rose-500">⚠ The quiz attached to this lesson will also be deleted.</p>

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
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue';
import axios from 'axios';
import { useRoute } from 'vue-router';
import { useLessons, type Lesson } from '../composables/useLessons';

const { items, course, loading, fetch, create, update, remove, reorder, presignVideoUpload } = useLessons();

const route    = useRoute();
const courseId = computed(() => Number(route.params.courseId));

onMounted(() => fetch(courseId.value));

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

    await reorder(courseId.value, arr.map((item, idx) => ({ id: item.id!, order: idx })));
};

// ── Create / Edit modal ──────────────────────────────────────────────────────
const showModal     = ref(false);
const editingLesson = ref<Lesson | null>(null);
const formError     = ref<string | null>(null);
const uploadingVideo = ref(false);
const uploadProgress = ref(0);
const MAX_VIDEO_SIZE = 1610612736;

const form = reactive({
    title:     '',
    content:   '',
    video_url: '',
    duration:  null as number | null,
    is_active: true as boolean,
});

const openCreateModal = () => {
    editingLesson.value = null;
    form.title          = '';
    form.content        = '';
    form.video_url      = '';
    form.duration       = null;
    form.is_active      = true;
    formError.value     = null;
    showModal.value     = true;
};

const openEditModal = (lesson: Lesson) => {
    editingLesson.value = lesson;
    form.title          = lesson.title;
    form.content        = lesson.content ?? '';
    form.video_url      = lesson.video_url ?? '';
    form.duration       = lesson.duration ?? null;
    form.is_active      = lesson.is_active ?? true;
    formError.value     = null;
    showModal.value     = true;
};

const closeModal = () => {
    showModal.value = false;
    uploadingVideo.value = false;
    uploadProgress.value = 0;
};

const onVideoSelected = async (event: Event) => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];

    if (!file) {
        return;
    }

    if (file.size > MAX_VIDEO_SIZE) {
        formError.value = 'Video size must be 1.5 GB or smaller.';
        input.value = '';
        return;
    }

    formError.value = null;
    uploadingVideo.value = true;
    uploadProgress.value = 0;

    try {
        const presigned = await presignVideoUpload(file);
        const safeHeaders = Object.fromEntries(
            Object.entries(presigned.headers ?? {}).filter(([key]) => !['host', 'content-length'].includes(key.toLowerCase()))
        );

        await axios.put(presigned.upload_url, file, {
            headers: {
                ...safeHeaders,
                'Content-Type': file.type || 'video/mp4',
            },
            onUploadProgress: (progressEvent) => {
                const total = progressEvent.total || file.size;
                if (total) {
                    uploadProgress.value = Math.round((progressEvent.loaded / total) * 100);
                }
            },
        });

        uploadProgress.value = 100;
        form.video_url = presigned.video_url;
    } catch (err: any) {
        formError.value = err?.response?.data?.message ?? err?.message ?? 'Video upload failed.';
    } finally {
        uploadingVideo.value = false;
        input.value = '';
    }
};

const submitModal = async () => {
    formError.value = null;

    if (uploadingVideo.value) {
        formError.value = 'Please wait for the video upload to finish.';
        return;
    }

    try {
        const payload: Partial<Lesson> = {
            title:     form.title,
            content:   form.content || undefined,
            video_url: form.video_url || undefined,
            duration:  form.duration ?? undefined,
            is_active: form.is_active,
        };
        if (editingLesson.value?.id) {
            await update(editingLesson.value.id, payload);
        } else {
            await create(courseId.value, payload);
        }
        closeModal();
    } catch (err: any) {
        formError.value = err?.response?.data?.message ?? err?.message ?? 'Something went wrong.';
    }
};

// ── Delete modal ─────────────────────────────────────────────────────────────
const showDeleteModal = ref(false);
const deletingLesson  = ref<Lesson | null>(null);

const openDeleteConfirm = (lesson: Lesson) => {
    deletingLesson.value  = lesson;
    formError.value       = null;
    showDeleteModal.value = true;
};

const confirmDelete = async () => {
    if (!deletingLesson.value?.id) return;
    formError.value = null;
    try {
        await remove(deletingLesson.value.id);
        showDeleteModal.value = false;
    } catch (err: any) {
        formError.value = err?.response?.data?.message ?? err?.message ?? 'Delete failed.';
    }
};

// ── Quiz Panel ────────────────────────────────────────────────────────────────
type QuestionOption = { id: number; label: number; content: string; is_correct: boolean };
type Question       = { id: number; content: string; type: number; answer_type: number; order: number; options: QuestionOption[] };
type Quiz           = { id: number; title: string; questions: Question[] };

const showQuizPanel     = ref(false);
const quizLesson        = ref<Lesson | null>(null);
const quiz              = ref<Quiz | null>(null);
const quizLoading       = ref(false);
const quizActionLoading = ref(false);
const confirmDeleteQuiz = ref(false);
const editingQuestionId = ref<number | null>(null);
const quizSaving        = ref(false);
const editQuestionError = ref<string | null>(null);

const editForm = reactive({
    content:     '',
    type:        1 as number,
    answer_type: 1 as number,
    options: [] as { id: number; content: string; is_correct: boolean }[],
});

const editMediaUploading = ref(false);
const editMediaProgress  = ref(0);

const openQuizPanel = async (lesson: Lesson) => {
    quizLesson.value        = lesson;
    quiz.value              = null;
    editingQuestionId.value = null;
    quizLoading.value       = true;
    showQuizPanel.value     = true;

    try {
        const res = await axios.get(`/admin/api/lessons/${lesson.id}/quiz`);
        quiz.value = res.data;
    } finally {
        quizLoading.value = false;
    }
};

const closeQuizPanel = () => {
    showQuizPanel.value     = false;
    editingQuestionId.value = null;
    confirmDeleteQuiz.value = false;
};

const addQuiz = async () => {
    if (!quizLesson.value?.id) return;
    quizActionLoading.value = true;
    try {
        const res = await axios.post(`/admin/api/lessons/${quizLesson.value.id}/quiz`);
        quiz.value = res.data;
    } finally {
        quizActionLoading.value = false;
    }
};

const deleteQuiz = async () => {
    if (!quiz.value?.id) return;
    quizActionLoading.value = true;
    try {
        await axios.delete(`/admin/api/quizzes/${quiz.value.id}`);
        quiz.value              = null;
        confirmDeleteQuiz.value = false;
        editingQuestionId.value = null;
    } finally {
        quizActionLoading.value = false;
    }
};

const startEditQuestion = (question: Question) => {
    editingQuestionId.value = question.id;
    editForm.content        = question.content;
    editForm.type           = question.type ?? 1;
    editForm.answer_type    = question.answer_type ?? 1;
    editForm.options        = question.options.map((o) => ({
        id:         o.id,
        content:    o.content,
        is_correct: o.is_correct,
    }));
    editQuestionError.value = null;
};

const cancelEditQuestion = () => {
    editingQuestionId.value = null;
    editQuestionError.value = null;
};

// For single-choice: when a radio changes, force only that index to be correct
const setSingleCorrect = (index: number) => {
    editForm.options.forEach((o, i) => {
        o.is_correct = i === index;
    });
};

const handleEditMediaChange = async (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (!file) return;
    editMediaUploading.value = true;
    editMediaProgress.value  = 0;
    try {
        const { data: presign } = await axios.post('/admin/api/questions/presign-media', {
            file_name:    file.name,
            content_type: file.type,
        });
        await axios.put(presign.upload_url, file, {
            headers: { 'Content-Type': file.type },
            withCredentials: false,
            onUploadProgress: (e) => {
                const total = e.total || file.size;
                editMediaProgress.value = total ? Math.round((e.loaded / total) * 100) : 0;
            },
        });
        editMediaProgress.value = 100;
        editForm.content = presign.media_url;
    } catch {
        editQuestionError.value = 'Media upload failed.';
    } finally {
        editMediaUploading.value = false;
    }
};

const saveQuestion = async (questionId: number) => {
    editQuestionError.value = null;
    quizSaving.value        = true;
    try {
        const res = await axios.put(`/admin/api/questions/${questionId}`, {
            content:     editForm.content,
            type:        editForm.type,
            answer_type: editForm.answer_type,
            options:     editForm.options,
        });

        // Update question in local quiz state
        if (quiz.value) {
            const idx = quiz.value.questions.findIndex((q) => q.id === questionId);
            if (idx !== -1) {
                quiz.value.questions[idx] = { ...quiz.value.questions[idx], ...res.data };
            }
        }

        editingQuestionId.value = null;
    } catch (err: any) {
        editQuestionError.value = err?.response?.data?.message ?? err?.message ?? 'Save failed.';
    } finally {
        quizSaving.value = false;
    }
};
</script>
