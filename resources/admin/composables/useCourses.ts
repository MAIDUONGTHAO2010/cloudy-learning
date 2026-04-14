import { ref } from 'vue';
import axios from 'axios';

export type Course = {
    id?: number;
    user_id: number;
    title: string;
    slug?: string;
    description?: string;
    thumbnail?: string;
    order?: number;
    is_active?: boolean;
    lessons_count?: number;
    reviews_count?: number;
    reviews_avg_rating?: number | null;
    instructor?: { id: number; name: string };
};

const items   = ref<Course[]>([]);
const loading = ref(false);

const API_BASE = '/admin/api/courses';

export const useCourses = () => {
    const fetch = async () => {
        loading.value = true;
        try {
            const res = await axios.get(API_BASE);
            items.value = res.data;
        } finally {
            loading.value = false;
        }
    };

    const create = async (payload: Partial<Course>) => {
        loading.value = true;
        try {
            const res = await axios.post(API_BASE, payload);
            items.value.push(res.data);
            return res.data as Course;
        } finally {
            loading.value = false;
        }
    };

    const update = async (id: number, payload: Partial<Course>) => {
        loading.value = true;
        try {
            const res = await axios.put(`${API_BASE}/${id}`, payload);
            const idx = items.value.findIndex((c) => c.id === id);
            if (idx !== -1) items.value[idx] = res.data;
            return res.data as Course;
        } finally {
            loading.value = false;
        }
    };

    const remove = async (id: number) => {
        loading.value = true;
        try {
            await axios.delete(`${API_BASE}/${id}`);
            items.value = items.value.filter((c) => c.id !== id);
        } finally {
            loading.value = false;
        }
    };

    const reorder = async (orderedItems: { id: number; order: number }[]) => {
        await axios.post(`${API_BASE}/reorder`, { items: orderedItems });
    };

    return { items, loading, fetch, create, update, remove, reorder };
};
