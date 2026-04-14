import { ref } from 'vue';
import axios from 'axios';

export type Lesson = {
    id?: number;
    course_id?: number;
    title: string;
    slug?: string;
    content?: string;
    video_url?: string;
    order?: number;
    duration?: number;
    is_active?: boolean;
};

export type LessonCourse = {
    id: number;
    title: string;
    is_active: boolean;
};

const items   = ref<Lesson[]>([]);
const course  = ref<LessonCourse | null>(null);
const loading = ref(false);

const API_BASE    = '/admin/api/courses';
const LESSON_BASE = '/admin/api/lessons';

export const useLessons = () => {
    const fetch = async (courseId: number) => {
        loading.value = true;
        try {
            const res = await axios.get(`${API_BASE}/${courseId}/lessons`);
            items.value  = res.data.lessons;
            course.value = res.data.course;
        } finally {
            loading.value = false;
        }
    };

    const create = async (courseId: number, payload: Partial<Lesson>) => {
        loading.value = true;
        try {
            const res = await axios.post(`${API_BASE}/${courseId}/lessons`, payload);
            items.value.push(res.data);
            return res.data as Lesson;
        } finally {
            loading.value = false;
        }
    };

    const update = async (id: number, payload: Partial<Lesson>) => {
        loading.value = true;
        try {
            const res = await axios.put(`${LESSON_BASE}/${id}`, payload);
            const idx = items.value.findIndex((l) => l.id === id);
            if (idx !== -1) items.value[idx] = res.data;
            return res.data as Lesson;
        } finally {
            loading.value = false;
        }
    };

    const remove = async (id: number) => {
        loading.value = true;
        try {
            await axios.delete(`${LESSON_BASE}/${id}`);
            items.value = items.value.filter((l) => l.id !== id);
        } finally {
            loading.value = false;
        }
    };

    const reorder = async (courseId: number, orderedItems: { id: number; order: number }[]) => {
        await axios.post(`${API_BASE}/${courseId}/lessons/reorder`, { items: orderedItems });
    };

    return { items, course, loading, fetch, create, update, remove, reorder };
};
