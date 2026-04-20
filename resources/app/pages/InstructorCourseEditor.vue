<template>
    <div class="min-h-screen bg-gray-50 text-gray-900">
        <Navbar />

        <main class="mx-auto max-w-5xl px-6 py-10">
            <!-- Back -->
            <RouterLink to="/my-courses" class="mb-6 inline-flex items-center gap-2 text-sm text-gray-500 transition hover:text-gray-900">
                ← Back to My Courses
            </RouterLink>

            <div v-if="pageLoading" class="space-y-4">
                <div class="h-8 w-64 animate-pulse rounded-xl bg-gray-200" />
                <div class="h-4 w-40 animate-pulse rounded-xl bg-gray-200" />
            </div>

            <template v-else>
                <!-- Course header -->
                <div class="mb-8 flex items-start justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">{{ course?.title }}</h1>
                        <p class="mt-1 text-sm text-gray-500">
                            <span :class="course?.is_active ? 'text-emerald-600' : 'text-gray-400'">
                                {{ course?.is_active ? 'Published' : 'Draft' }}
                            </span>
                            <span class="mx-2 text-gray-300">·</span>
                            {{ lessons.length }} lessons
                        </p>
                    </div>
                    <button
                        @click="openAddLesson"
                        class="shrink-0 rounded-xl bg-orange-500 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-600"
                    >
                        + Add Lesson
                    </button>
                </div>

                <!-- Lessons list -->
                <div v-if="lessons.length === 0" class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 py-16 text-center text-gray-400">
                    No lessons yet. Click "+ Add Lesson" to get started.
                </div>

                <div v-else class="space-y-3">
                    <div
                        v-for="(lesson, idx) in lessons"
                        :key="lesson.id"
                        class="rounded-2xl border border-gray-200 bg-white p-5"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-medium text-gray-400">#{{ idx + 1 }}</span>
                                    <span
                                        class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                        :class="lesson.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500'"
                                    >
                                        {{ lesson.is_active ? 'Active' : 'Draft' }}
                                    </span>
                                    <span v-if="lesson.has_quiz" class="rounded-full bg-purple-50 px-2 py-0.5 text-[10px] font-semibold text-purple-700">
                                        Quiz
                                    </span>
                                </div>
                                <h3 class="mt-1 truncate text-sm font-semibold text-gray-900">{{ lesson.title }}</h3>
                                <p v-if="lesson.duration" class="text-xs text-gray-400">{{ lesson.duration }} min</p>
                            </div>
                            <div class="flex shrink-0 gap-2">
                                <button
                                    @click="openQuiz(lesson)"
                                    class="rounded-xl border border-gray-200 px-3 py-1.5 text-xs text-gray-600 transition hover:bg-gray-50"
                                >
                                    Quiz
                                </button>
                                <button
                                    @click="openEditLesson(lesson)"
                                    class="rounded-xl border border-gray-200 px-3 py-1.5 text-xs text-gray-600 transition hover:bg-gray-50"
                                >
                                    Edit
                                </button>
                                <button
                                    @click="confirmDeleteLesson(lesson)"
                                    class="rounded-xl border border-rose-500/30 px-3 py-1.5 text-xs text-rose-400 transition hover:bg-rose-500/10"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </main>

        <AppFooter />

        <!-- Lesson form modal -->
        <Teleport to="body">
            <div v-if="showLessonForm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
                <div class="w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl overflow-y-auto max-h-[90vh]">
                    <h2 class="mb-5 text-lg font-semibold text-gray-900">{{ editingLesson ? 'Edit Lesson' : 'New Lesson' }}</h2>

                    <form @submit.prevent="submitLesson" class="space-y-4">
                        <div>
                            <label class="mb-1 block text-xs text-gray-500">Title *</label>
                            <input
                                v-model="lessonForm.title"
                                type="text"
                                required
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 outline-none focus:border-orange-400"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs text-gray-500">Content</label>
                            <textarea
                                v-model="lessonForm.content"
                                rows="4"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 outline-none focus:border-orange-400"
                            ></textarea>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs text-gray-500">Duration (minutes)</label>
                            <input
                                v-model.number="lessonForm.duration"
                                type="number"
                                min="0"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 outline-none focus:border-orange-400"
                            />
                        </div>

                        <!-- Video upload -->
                        <div>
                            <label class="mb-1 block text-xs text-gray-500">Video</label>
                            <div v-if="lessonForm.video_url" class="mb-2 flex items-center gap-2">
                                <span class="truncate text-xs text-emerald-600">{{ lessonForm.video_url }}</span>
                                <button type="button" @click="lessonForm.video_url = ''" class="text-rose-500 hover:text-rose-700 text-xs">✕</button>
                            </div>
                            <input
                                v-else
                                type="file"
                                accept="video/*"
                                @change="handleVideoChange"
                                class="block w-full text-xs text-gray-400 file:mr-3 file:rounded-xl file:border-0 file:bg-orange-500/20 file:px-3 file:py-1.5 file:text-xs file:text-orange-600 file:cursor-pointer"
                            />
                            <div v-if="videoUploading" class="mt-2">
                                <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-200">
                                    <div class="h-full rounded-full bg-orange-500 transition-all duration-300" :style="`width:${videoProgress}%`"></div>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Uploading {{ videoProgress }}%…</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <input id="lesson_active" v-model="lessonForm.is_active" type="checkbox" class="rounded border-gray-300 bg-white accent-orange-500" />
                            <label for="lesson_active" class="text-sm text-gray-700">Active (visible to students)</label>
                        </div>

                        <p v-if="lessonFormError" class="text-xs text-rose-500">{{ lessonFormError }}</p>

                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="closeLessonForm" class="rounded-xl border border-gray-200 px-5 py-2.5 text-sm text-gray-500 hover:text-gray-900">Cancel</button>
                            <button
                                type="submit"
                                :disabled="lessonSubmitting || videoUploading"
                                class="rounded-xl bg-[#1a1a4e] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#0f2460] disabled:opacity-50"
                            >
                                {{ lessonSubmitting ? 'Saving…' : 'Save Lesson' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>

        <!-- Delete lesson confirm -->
        <Teleport to="body">
            <div v-if="deletingLesson" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
                <div class="w-full max-w-sm rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl">
                    <h2 class="mb-2 text-lg font-semibold text-gray-900">Delete Lesson?</h2>
                    <p class="text-sm text-gray-500">"<span class="text-gray-900">{{ deletingLesson.title }}</span>" and its quiz will be permanently deleted.</p>
                    <div class="mt-5 flex justify-end gap-3">
                        <button @click="deletingLesson = null" class="rounded-xl border border-gray-200 px-4 py-2 text-sm text-gray-500 hover:text-gray-900">Cancel</button>
                        <button @click="deleteLesson" :disabled="deleteLessonSubmitting" class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700 disabled:opacity-50">
                            {{ deleteLessonSubmitting ? 'Deleting…' : 'Delete' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Quiz panel -->
        <Teleport to="body">
            <div v-if="showQuizPanel" class="fixed inset-0 z-50 flex items-start justify-end bg-black/40 backdrop-blur-sm">
                <div class="h-full w-full max-w-xl overflow-y-auto border-l border-gray-200 bg-white p-6 shadow-2xl">
                    <div class="mb-5 flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Quiz</h2>
                            <p class="text-sm text-gray-500">{{ quizLesson?.title }}</p>
                        </div>
                        <button @click="showQuizPanel = false" class="text-gray-400 hover:text-gray-900">✕</button>
                    </div>

                    <div v-if="quizLoading" class="space-y-3">
                        <div v-for="n in 3" :key="n" class="h-24 animate-pulse rounded-xl bg-gray-200" />
                    </div>

                    <template v-else>
                        <!-- No quiz yet -->
                        <div v-if="!quiz" class="flex flex-col items-center gap-4 py-16 text-center">
                            <p class="text-gray-400">This lesson has no quiz yet.</p>
                            <button
                                @click="createQuiz"
                                :disabled="quizActionLoading"
                                class="rounded-xl bg-purple-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-purple-700 disabled:opacity-50"
                            >
                                {{ quizActionLoading ? 'Creating…' : 'Create Quiz' }}
                            </button>
                        </div>

                        <!-- Quiz exists -->
                        <template v-else>
                            <div class="mb-4 flex items-center justify-between">
                                <p class="text-sm font-semibold text-gray-900">{{ quiz.questions?.length ?? 0 }} questions</p>
                                <div class="flex gap-2">
                                    <button
                                        @click="addQuestion"
                                        class="rounded-xl bg-orange-500 px-4 py-2 text-xs font-semibold text-white hover:bg-orange-600"
                                    >
                                        + Question
                                    </button>
                                    <button
                                        @click="confirmDeleteQuiz"
                                        class="rounded-xl border border-rose-300 px-4 py-2 text-xs text-rose-500 hover:bg-rose-50"
                                    >
                                        Delete Quiz
                                    </button>
                                </div>
                            </div>

                            <div v-if="quiz.questions?.length === 0" class="py-8 text-center text-sm text-gray-400">
                                No questions yet.
                            </div>

                            <div v-else class="space-y-4">
                                <div
                                    v-for="(question, qIdx) in quiz.questions"
                                    :key="question.id"
                                    class="rounded-xl border border-gray-200 bg-gray-50 p-4"
                                >
                                    <!-- View mode -->
                                    <template v-if="editingQuestionId !== question.id">
                                        <div class="flex items-start justify-between gap-2">
                                            <p class="text-sm font-medium text-gray-900">{{ qIdx + 1 }}. {{ question.content }}</p>
                                            <div class="flex shrink-0 gap-2">
                                                <button @click="startEditQuestion(question)" class="text-xs text-orange-500 hover:underline">Edit</button>
                                                <button @click="deleteQuestion(question.id)" class="text-xs text-rose-400 hover:underline">Delete</button>
                                            </div>
                                        </div>
                                        <ul class="mt-2 space-y-1">
                                            <li
                                                v-for="opt in question.options"
                                                :key="opt.id"
                                                class="flex items-center gap-2 text-xs"
                                                :class="opt.is_correct ? 'text-emerald-700' : 'text-gray-500'"
                                            >
                                                <span class="font-semibold">{{ ['A','B','C','D'][opt.label - 1] }}.</span>
                                                {{ opt.content }}
                                                <span v-if="opt.is_correct" class="text-[10px] font-semibold text-emerald-600">✓</span>
                                            </li>
                                        </ul>
                                    </template>

                                    <!-- Edit mode -->
                                    <template v-else>
                                        <!-- Question Type -->
                                        <div class="mb-2 flex flex-wrap gap-2">
                                            <label v-for="qt in [{ val: 1, label: 'Text' }, { val: 2, label: 'Image' }, { val: 3, label: 'Audio' }, { val: 4, label: 'Video' }]" :key="qt.val"
                                                class="flex cursor-pointer items-center gap-1 rounded-lg border px-2.5 py-1 text-[11px] font-medium transition"
                                                :class="editForm.type === qt.val ? 'border-indigo-400 bg-indigo-50 text-indigo-700' : 'border-gray-200 text-gray-400'"
                                            >
                                                <input type="radio" :value="qt.val" v-model="editForm.type" class="hidden" />
                                                {{ qt.label }}
                                            </label>
                                        </div>
                                        <!-- Content -->
                                        <div v-if="editForm.type === 1" class="mb-2">
                                            <textarea
                                                v-model="editForm.content"
                                                rows="2"
                                                placeholder="Question text…"
                                                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 outline-none focus:border-orange-400"
                                            ></textarea>
                                        </div>
                                        <div v-else class="mb-2 space-y-1">
                                            <div v-if="editForm.content" class="truncate rounded-lg bg-slate-50 px-3 py-1.5 text-xs text-slate-600">{{ editForm.content }}</div>
                                            <label class="flex cursor-pointer items-center gap-2 rounded-xl border-2 border-dashed border-gray-200 px-3 py-2 text-xs text-gray-400 hover:border-orange-300 hover:text-orange-500 transition">
                                                {{ editMediaUploading ? `Uploading ${editMediaProgress}%…` : (editForm.content ? 'Replace file' : 'Upload file') }}
                                                <input type="file" class="hidden" :accept="editForm.type === 2 ? 'image/*' : editForm.type === 3 ? 'audio/*' : 'video/*'" @change="handleEditMediaChange" :disabled="editMediaUploading" />
                                            </label>
                                        </div>
                                        <!-- Answer Type -->
                                        <div class="mb-3 flex gap-2">
                                            <label class="flex cursor-pointer items-center gap-1 rounded-lg border px-2.5 py-1 text-[11px] font-medium transition"
                                                :class="editForm.answer_type === 1 ? 'border-orange-400 bg-orange-50 text-orange-600' : 'border-gray-200 text-gray-400'"
                                            >
                                                <input type="radio" :value="1" v-model="editForm.answer_type" class="hidden" /> Single
                                            </label>
                                            <label class="flex cursor-pointer items-center gap-1 rounded-lg border px-2.5 py-1 text-[11px] font-medium transition"
                                                :class="editForm.answer_type === 2 ? 'border-purple-400 bg-purple-50 text-purple-700' : 'border-gray-200 text-gray-400'"
                                            >
                                                <input type="radio" :value="2" v-model="editForm.answer_type" class="hidden" /> Multiple
                                            </label>
                                        </div>
                                        <!-- Options -->
                                        <div class="space-y-2">
                                            <div
                                                v-for="(opt, oIdx) in editForm.options"
                                                :key="opt.id"
                                                class="flex items-center gap-3"
                                            >
                                                <span class="w-4 shrink-0 text-xs font-semibold text-gray-400">{{ ['A','B','C','D'][oIdx] }}.</span>
                                                <input
                                                    v-model="opt.content"
                                                    type="text"
                                                    class="flex-1 rounded-xl border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs text-gray-900 outline-none focus:border-orange-400"
                                                />
                                                <label class="flex items-center gap-1 text-xs text-gray-500 cursor-pointer">
                                                    <input
                                                        v-if="editForm.answer_type === 2"
                                                        type="checkbox"
                                                        :checked="opt.is_correct"
                                                        @change="setCorrect(oIdx)"
                                                        class="accent-emerald-500"
                                                    />
                                                    <input
                                                        v-else
                                                        type="radio"
                                                        :name="`correct_${question.id}`"
                                                        :checked="opt.is_correct"
                                                        @change="setCorrect(oIdx)"
                                                        class="accent-emerald-500"
                                                    />
                                                    Correct
                                                </label>
                                            </div>
                                        </div>
                                        <div class="mt-3 flex justify-end gap-2">
                                            <button @click="cancelEdit" class="text-xs text-gray-500 hover:text-gray-900">Cancel</button>
                                            <button
                                                @click="saveQuestion(question.id)"
                                                :disabled="quizActionLoading"
                                                class="rounded-xl bg-[#1a1a4e] px-4 py-1.5 text-xs font-semibold text-white hover:bg-[#0f2460] disabled:opacity-50"
                                            >
                                                {{ quizActionLoading ? 'Saving…' : 'Save' }}
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </template>

                    <p v-if="quizFeedback" class="mt-4 text-xs" :class="quizFeedback.type === 'success' ? 'text-emerald-600' : 'text-rose-500'">
                        {{ quizFeedback.message }}
                    </p>
                </div>
            </div>
        </Teleport>

        <!-- Delete quiz confirm -->
        <Teleport to="body">
            <div v-if="showDeleteQuiz" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 p-4">
                <div class="w-full max-w-sm rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl">
                    <h2 class="mb-2 text-lg font-semibold text-gray-900">Delete Quiz?</h2>
                    <p class="text-sm text-gray-500">All questions will be deleted. This cannot be undone.</p>
                    <div class="mt-5 flex justify-end gap-3">
                        <button @click="showDeleteQuiz = false" class="rounded-xl border border-gray-200 px-4 py-2 text-sm text-gray-500 hover:text-gray-900">Cancel</button>
                        <button @click="deleteQuiz" :disabled="quizActionLoading" class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700 disabled:opacity-50">
                            {{ quizActionLoading ? 'Deleting…' : 'Delete' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Add question modal -->
        <Teleport to="body">
            <div v-if="showAddQuestionModal" class="fixed inset-0 z-[70] flex items-center justify-center bg-black/50 p-4">
                <div class="w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">New Question</h2>

                    <!-- Question Type -->
                    <div class="mb-3">
                        <label class="mb-1 block text-xs text-gray-500">Question Type</label>
                        <div class="flex flex-wrap gap-2">
                            <label v-for="qt in [{ val: 1, label: 'Text' }, { val: 2, label: 'Image' }, { val: 3, label: 'Audio' }, { val: 4, label: 'Video' }]" :key="qt.val"
                                class="flex cursor-pointer items-center gap-1.5 rounded-xl border px-3 py-2 text-xs font-semibold transition"
                                :class="newQuestionForm.type === qt.val ? 'border-orange-500 bg-orange-50 text-orange-600' : 'border-gray-200 text-gray-400'"
                            >
                                <input type="radio" v-model="newQuestionForm.type" :value="qt.val" class="hidden" />
                                {{ qt.label }}
                            </label>
                        </div>
                    </div>

                    <!-- Answer Type -->
                    <div class="mb-3">
                        <label class="mb-1 block text-xs text-gray-500">Answer Type</label>
                        <div class="flex gap-2">
                            <label class="flex cursor-pointer items-center gap-2 rounded-xl border px-3 py-2 text-xs font-semibold transition"
                                :class="newQuestionForm.answer_type === 1 ? 'border-orange-500 bg-orange-50 text-orange-600' : 'border-gray-200 text-gray-400'"
                            >
                                <input type="radio" v-model="newQuestionForm.answer_type" :value="1" class="hidden" />
                                Single Choice
                            </label>
                            <label class="flex cursor-pointer items-center gap-2 rounded-xl border px-3 py-2 text-xs font-semibold transition"
                                :class="newQuestionForm.answer_type === 2 ? 'border-purple-400 bg-purple-50 text-purple-700' : 'border-gray-200 text-gray-400'"
                            >
                                <input type="radio" v-model="newQuestionForm.answer_type" :value="2" class="hidden" />
                                Multiple Choice
                            </label>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="mb-4">
                        <label class="mb-1 block text-xs text-gray-500">{{ newQuestionForm.type === 1 ? 'Question Text' : 'Question Media' }}</label>
                        <textarea
                            v-if="newQuestionForm.type === 1"
                            v-model="newQuestionForm.content"
                            rows="3"
                            placeholder="Enter your question…"
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 outline-none focus:border-orange-400"
                        ></textarea>
                        <div v-else class="space-y-2">
                            <div v-if="newQuestionForm.content" class="truncate rounded-lg bg-slate-50 px-3 py-1.5 text-xs text-slate-600">{{ newQuestionForm.content }}</div>
                            <label class="flex cursor-pointer items-center gap-2 rounded-xl border-2 border-dashed border-gray-200 px-3 py-3 text-xs text-gray-400 hover:border-orange-300 hover:text-orange-500 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                                {{ newMediaUploading ? `Uploading ${newMediaProgress}%…` : (newQuestionForm.content ? 'Replace file' : 'Upload file') }}
                                <input type="file" class="hidden" :accept="newQuestionForm.type === 2 ? 'image/*' : newQuestionForm.type === 3 ? 'audio/*' : 'video/*'" @change="handleNewMediaChange" :disabled="newMediaUploading" />
                            </label>
                        </div>
                    </div>

                    <label class="mb-2 block text-xs text-gray-500">Answer Options</label>
                    <div class="space-y-2">
                        <div v-for="(opt, idx) in newQuestionForm.options" :key="idx" class="flex items-center gap-3">
                            <span class="w-5 shrink-0 text-xs font-semibold text-gray-400">{{ ['A','B','C','D'][idx] }}.</span>
                            <input
                                v-model="opt.content"
                                type="text"
                                :placeholder="`Option ${['A','B','C','D'][idx]}`"
                                class="flex-1 rounded-xl border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs text-gray-900 outline-none focus:border-orange-400"
                            />
                            <label class="flex cursor-pointer items-center gap-1 text-xs text-gray-500">
                                <input
                                    v-if="newQuestionForm.answer_type === 1"
                                    type="radio"
                                    name="new_correct"
                                    :checked="opt.is_correct"
                                    @change="setNewCorrect(idx)"
                                    class="accent-emerald-500"
                                />
                                <input
                                    v-else
                                    type="checkbox"
                                    :checked="opt.is_correct"
                                    @change="setNewCorrect(idx)"
                                    class="accent-emerald-500"
                                />
                                Correct
                            </label>
                        </div>
                    </div>

                    <div class="mt-5 flex justify-end gap-3">
                        <button @click="showAddQuestionModal = false" class="rounded-xl border border-gray-200 px-4 py-2 text-sm text-gray-500 hover:text-gray-900">Cancel</button>
                        <button
                            @click="submitNewQuestion"
                            :disabled="addQuestionLoading || (!newQuestionForm.content.trim() && newQuestionForm.type === 1)"
                            class="rounded-xl bg-orange-500 px-5 py-2 text-sm font-semibold text-white hover:bg-orange-600 disabled:opacity-50"
                        >
                            {{ addQuestionLoading ? 'Adding…' : 'Add Question' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import Navbar from '../components/Navbar.vue';
import AppFooter from '../components/Footer.vue';

interface Lesson {
    id: number;
    title: string;
    content: string | null;
    video_url: string | null;
    duration: number | null;
    order: number;
    is_active: boolean;
    has_quiz?: boolean;
}

interface QuestionOption {
    id: number;
    label: number;
    content: string;
    is_correct: boolean;
}

interface Question {
    id: number;
    content: string;
    type: number;
    answer_type: number;
    options: QuestionOption[];
}

interface Quiz {
    id: number;
    title: string;
    questions: Question[];
}

interface Course {
    id: number;
    title: string;
    is_active: boolean;
}

const route = useRoute();
const courseId = Number(route.params.courseId);

const BASE = `/api/instructor/courses/${courseId}`;

// ── Page state ────────────────────────────────────────────
const course      = ref<Course | null>(null);
const lessons     = ref<Lesson[]>([]);
const pageLoading = ref(true);

// ── Lesson form ───────────────────────────────────────────
const showLessonForm    = ref(false);
const editingLesson     = ref<Lesson | null>(null);
const lessonSubmitting  = ref(false);
const lessonFormError   = ref('');
const videoUploading    = ref(false);
const videoProgress     = ref(0);
const lessonForm = ref({
    title: '',
    content: '',
    video_url: '',
    duration: null as number | null,
    is_active: true,
});

const deletingLesson       = ref<Lesson | null>(null);
const deleteLessonSubmitting = ref(false);

// ── Quiz panel ────────────────────────────────────────────
const showQuizPanel        = ref(false);
const quizLesson           = ref<Lesson | null>(null);
const quiz                 = ref<Quiz | null>(null);
const currentQuizId        = ref<number | null>(null);
const quizLoading          = ref(false);
const quizActionLoading    = ref(false);
const quizFeedback         = ref<{ type: 'success' | 'error'; message: string } | null>(null);
const showDeleteQuiz       = ref(false);
const showAddQuestionModal = ref(false);
const newQuestionForm      = ref({
    content:     '',
    type:        1,
    answer_type: 1,
    options: [
        { label: 1, content: '', is_correct: true },
        { label: 2, content: '', is_correct: false },
        { label: 3, content: '', is_correct: false },
        { label: 4, content: '', is_correct: false },
    ],
});
const addQuestionLoading   = ref(false);
const newMediaUploading    = ref(false);
const newMediaProgress     = ref(0);

const editingQuestionId = ref<number | null>(null);
const editForm = ref<{ content: string; type: number; answer_type: number; options: QuestionOption[] }>({ content: '', type: 1, answer_type: 1, options: [] });
const editMediaUploading = ref(false);
const editMediaProgress  = ref(0);

// ── Init ──────────────────────────────────────────────────
onMounted(async () => {
    try {
        const { data } = await axios.get(BASE + '/lessons');
        course.value  = data.course;
        lessons.value = data.lessons.map((l: Lesson) => ({ ...l, has_quiz: false }));

        // Check which lessons have quizzes
        await Promise.all(
            lessons.value.map(async (lesson) => {
                try {
                    const { data: q } = await axios.get(`${BASE}/lessons/${lesson.id}/quiz`);
                    lesson.has_quiz = !!q;
                } catch {}
            })
        );
    } finally {
        pageLoading.value = false;
    }
});

// ── Lesson CRUD ───────────────────────────────────────────
const openAddLesson = () => {
    editingLesson.value = null;
    lessonForm.value = { title: '', content: '', video_url: '', duration: null, is_active: true };
    lessonFormError.value = '';
    showLessonForm.value = true;
};

const openEditLesson = (lesson: Lesson) => {
    editingLesson.value = lesson;
    lessonForm.value = {
        title: lesson.title,
        content: lesson.content ?? '',
        video_url: lesson.video_url ?? '',
        duration: lesson.duration,
        is_active: lesson.is_active,
    };
    lessonFormError.value = '';
    showLessonForm.value = true;
};

const closeLessonForm = () => {
    showLessonForm.value = false;
    editingLesson.value = null;
};

const handleVideoChange = async (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (!file) return;

    videoUploading.value = true;
    videoProgress.value  = 0;

    try {
        const { data: presign } = await axios.post(`${BASE}/lessons/presign-video`, {
            file_name: file.name,
            content_type: file.type,
            file_size: file.size,
        });

        await axios.put(presign.upload_url, file, {
            adapter: 'xhr',
            headers: { 'Content-Type': file.type },
            withCredentials: false,
            onUploadProgress: (e) => {
                videoProgress.value = e.total ? Math.round((e.loaded / e.total) * 100) : 0;
            },
        });

        lessonForm.value.video_url = presign.video_url;
    } catch {
        lessonFormError.value = 'Video upload failed.';
    } finally {
        videoUploading.value = false;
    }
};

const submitLesson = async () => {
    lessonSubmitting.value = true;
    lessonFormError.value  = '';

    try {
        if (editingLesson.value) {
            const { data } = await axios.put(`${BASE}/lessons/${editingLesson.value.id}`, lessonForm.value);
            const idx = lessons.value.findIndex((l) => l.id === data.id);
            if (idx !== -1) lessons.value[idx] = { ...data, has_quiz: lessons.value[idx].has_quiz };
        } else {
            const { data } = await axios.post(`${BASE}/lessons`, lessonForm.value);
            lessons.value.push({ ...data, has_quiz: false });
        }
        closeLessonForm();
    } catch (e: any) {
        const errors = e?.response?.data?.errors;
        lessonFormError.value = errors ? Object.values(errors).flat().join(' ') : (e?.response?.data?.message ?? 'Error saving lesson.');
    } finally {
        lessonSubmitting.value = false;
    }
};

const confirmDeleteLesson = (lesson: Lesson) => {
    deletingLesson.value = lesson;
};

const deleteLesson = async () => {
    if (!deletingLesson.value) return;
    deleteLessonSubmitting.value = true;
    try {
        await axios.delete(`${BASE}/lessons/${deletingLesson.value.id}`);
        lessons.value = lessons.value.filter((l) => l.id !== deletingLesson.value?.id);
        deletingLesson.value = null;
    } catch (e: any) {
        alert(e?.response?.data?.message ?? 'Failed to delete lesson.');
    } finally {
        deleteLessonSubmitting.value = false;
    }
};

// ── Quiz ──────────────────────────────────────────────────
const openQuiz = async (lesson: Lesson) => {
    quizLesson.value    = lesson;
    quiz.value          = null;
    quizFeedback.value  = null;
    editingQuestionId.value = null;
    showQuizPanel.value = true;
    quizLoading.value   = true;

    try {
        const { data } = await axios.get(`${BASE}/lessons/${lesson.id}/quiz`);
        quiz.value          = data;
        currentQuizId.value = data?.id ?? null;
    } catch {
        quiz.value          = null;
        currentQuizId.value = null;
    } finally {
        quizLoading.value = false;
    }
};

const createQuiz = async () => {
    if (!quizLesson.value) return;
    quizActionLoading.value = true;
    quizFeedback.value = null;
    try {
        const { data } = await axios.post(`${BASE}/lessons/${quizLesson.value.id}/quiz`);
        quiz.value          = data;
        currentQuizId.value = data?.id ?? null;
        // Mark lesson as having quiz
        const l = lessons.value.find((x) => x.id === quizLesson.value?.id);
        if (l) l.has_quiz = true;
    } catch (e: any) {
        quizFeedback.value = { type: 'error', message: e?.response?.data?.message ?? 'Failed to create quiz.' };
    } finally {
        quizActionLoading.value = false;
    }
};

const confirmDeleteQuiz = () => { showDeleteQuiz.value = true; };

const deleteQuiz = async () => {
    if (!quiz.value) return;
    quizActionLoading.value = true;
    try {
        await axios.delete(`${BASE}/quizzes/${currentQuizId.value}`);
        quiz.value          = null;
        currentQuizId.value = null;
        showDeleteQuiz.value = false;
        const l = lessons.value.find((x) => x.id === quizLesson.value?.id);
        if (l) l.has_quiz = false;
        quizFeedback.value = { type: 'success', message: 'Quiz deleted.' };
    } catch (e: any) {
        quizFeedback.value = { type: 'error', message: e?.response?.data?.message ?? 'Failed to delete quiz.' };
    } finally {
        quizActionLoading.value = false;
    }
};

const addQuestion = () => {
    if (!quiz.value) return;
    newQuestionForm.value = {
        content:     '',
        type:        1,
        answer_type: 1,
        options: [
            { label: 1, content: '', is_correct: true },
            { label: 2, content: '', is_correct: false },
            { label: 3, content: '', is_correct: false },
            { label: 4, content: '', is_correct: false },
        ],
    };
    showAddQuestionModal.value = true;
};

const submitNewQuestion = async () => {
    if (!currentQuizId.value) return;
    addQuestionLoading.value = true;
    quizFeedback.value       = null;
    try {
        const { data } = await axios.post(`${BASE}/quizzes/${currentQuizId.value}/questions`, newQuestionForm.value);
        quiz.value!.questions.push(data);
        showAddQuestionModal.value = false;
    } catch (e: any) {
        quizFeedback.value = { type: 'error', message: e?.response?.data?.message ?? 'Failed to add question.' };
    } finally {
        addQuestionLoading.value = false;
    }
};

const setNewCorrect = (idx: number) => {
    if (newQuestionForm.value.answer_type === 1) {
        newQuestionForm.value.options.forEach((o, i) => { o.is_correct = i === idx; });
    } else {
        newQuestionForm.value.options[idx].is_correct = !newQuestionForm.value.options[idx].is_correct;
    }
};

const handleNewMediaChange = async (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (!file) return;
    newMediaUploading.value = true;
    newMediaProgress.value  = 0;
    try {
        const { data: presign } = await axios.post(`${BASE}/questions/presign-media`, {
            file_name:    file.name,
            content_type: file.type,
        });
        await axios.put(presign.upload_url, file, {
            adapter: 'xhr',
            headers: { 'Content-Type': file.type },
            withCredentials: false,
            onUploadProgress: (e) => {
                newMediaProgress.value = e.total ? Math.round((e.loaded / e.total) * 100) : 0;
            },
        });
        newQuestionForm.value.content = presign.media_url;
    } catch {
        quizFeedback.value = { type: 'error', message: 'Media upload failed.' };
    } finally {
        newMediaUploading.value = false;
    }
};

const deleteQuestion = async (questionId: number) => {
    if (!quiz.value) return;
    quizActionLoading.value = true;
    quizFeedback.value = null;
    try {
        await axios.delete(`${BASE}/questions/${questionId}`);
        quiz.value.questions = quiz.value.questions.filter((q) => q.id !== questionId);
    } catch (e: any) {
        quizFeedback.value = { type: 'error', message: e?.response?.data?.message ?? 'Failed to delete question.' };
    } finally {
        quizActionLoading.value = false;
    }
};

const startEditQuestion = (question: Question) => {
    editingQuestionId.value = question.id;
    editForm.value = {
        content:     question.content,
        type:        question.type ?? 1,
        answer_type: question.answer_type ?? 1,
        options:     question.options.map((o) => ({ ...o })),
    };
};

const cancelEdit = () => {
    editingQuestionId.value = null;
};

const setCorrect = (index: number) => {
    if (editForm.value.answer_type === 2) {
        editForm.value.options[index].is_correct = !editForm.value.options[index].is_correct;
    } else {
        editForm.value.options.forEach((o, i) => {
            o.is_correct = i === index;
        });
    }
};

const handleEditMediaChange = async (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (!file) return;
    editMediaUploading.value = true;
    editMediaProgress.value  = 0;
    try {
        const { data: presign } = await axios.post(`${BASE}/questions/presign-media`, {
            file_name:    file.name,
            content_type: file.type,
        });
        await axios.put(presign.upload_url, file, {
            adapter: 'xhr',
            headers: { 'Content-Type': file.type },
            withCredentials: false,
            onUploadProgress: (e) => {
                editMediaProgress.value = e.total ? Math.round((e.loaded / e.total) * 100) : 0;
            },
        });
        editForm.value.content = presign.media_url;
    } catch {
        quizFeedback.value = { type: 'error', message: 'Media upload failed.' };
    } finally {
        editMediaUploading.value = false;
    }
};

const saveQuestion = async (questionId: number) => {
    quizActionLoading.value = true;
    quizFeedback.value = null;
    try {
        const { data } = await axios.put(`${BASE}/questions/${questionId}`, {
            content:     editForm.value.content,
            type:        editForm.value.type,
            answer_type: editForm.value.answer_type,
            options:     editForm.value.options,
        });
        if (quiz.value) {
            const idx = quiz.value.questions.findIndex((q) => q.id === questionId);
            if (idx !== -1) quiz.value.questions[idx] = data;
        }
        editingQuestionId.value = null;
    } catch (e: any) {
        quizFeedback.value = { type: 'error', message: e?.response?.data?.message ?? 'Failed to save question.' };
    } finally {
        quizActionLoading.value = false;
    }
};
</script>
