import { ref } from 'vue';
import axios from 'axios';

export type Category = {
    id?: number;
    title: string;
    description?: string;
};

const items = ref<Category[]>([]);
const loading = ref(false);
const error = ref<string | null>(null);

const API_BASE = '/admin/api/categories';

export const useCategories = () => {
    const fetch = async () => {
        loading.value = true;
        error.value = null;
        try {
            const res = await axios.get(API_BASE);
            items.value = res.data;
        } catch (err: any) {
            error.value = err?.message ?? 'Fetch failed';
        } finally {
            loading.value = false;
        }
    };

    const create = async (payload: Partial<Category>) => {
        loading.value = true;
        try {
            const res = await axios.post(API_BASE, payload);
            items.value.unshift(res.data);
            return res.data;
        } finally {
            loading.value = false;
        }
    };

    const update = async (id: number, payload: Partial<Category>) => {
        loading.value = true;
        try {
            const res = await axios.put(`${API_BASE}/${id}`, payload);
            items.value = items.value.map((c) => (c.id === id ? res.data : c));
            return res.data;
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

    return {
        items,
        loading,
        error,
        fetch,
        create,
        update,
        remove,
    };
};
