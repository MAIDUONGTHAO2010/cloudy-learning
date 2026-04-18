import { ref } from 'vue';
import axios from 'axios';

export type Course = {
    id?: number;
    user_id: number;
    category_id?: number | null;
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
    category?: { id: number; name: string } | null;
    tags?: { id: number; name: string; slug: string }[];
};

const items       = ref<Course[]>([]);
const loading     = ref(false);
const currentPage = ref(1);
const lastPage    = ref(1);
const total       = ref(0);

const API_BASE = '/admin/api/courses';

export const useCourses = () => {
    const fetch = async (page = 1) => {
        loading.value = true;
        try {
            const res = await axios.get(API_BASE, { params: { page } });
            items.value       = res.data.data;
            currentPage.value = res.data.current_page;
            lastPage.value    = res.data.last_page;
            total.value       = res.data.total;
        } finally {
            loading.value = false;
        }
    };

    const create = async (payload: Partial<Course>) => {
        loading.value = true;
        try {
            const res = await axios.post(API_BASE, payload);
            await fetch(currentPage.value);
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
            const page = items.value.length === 1 && currentPage.value > 1
                ? currentPage.value - 1
                : currentPage.value;
            await fetch(page);
        } finally {
            loading.value = false;
        }
    };

    const reorder = async (orderedItems: { id: number; order: number }[]) => {
        await axios.post(`${API_BASE}/reorder`, { items: orderedItems });
    };

    return { items, loading, currentPage, lastPage, total, fetch, create, update, remove, reorder };
};
