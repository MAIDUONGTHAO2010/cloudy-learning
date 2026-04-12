import { ref, reactive } from 'vue';
import axios from 'axios';

// Ensure headers are set even if this composable is used before admin.ts runs
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Accept'] = 'application/json';
axios.defaults.withCredentials = true;

export type Category = {
    id?: number;
    name: string;
    slug?: string;
    description?: string;
    image?: string;
    order?: number;
    is_active?: boolean;
    parent_id?: number | null;
    children_count?: number;
};

const items = ref<Category[]>([]);
const loading = ref(false);
const error = ref<string | null>(null);
const childrenMap = reactive<Record<number, Category[]>>({});
const loadingChildren = reactive<Record<number, boolean>>({});

const API_BASE = '/admin/api/categories';

export const useCategories = () => {
    const fetch = async () => {
        loading.value = true;
        error.value = null;
        try {
            const res = await axios.get(API_BASE);
            console.log(res.data);

            items.value = res.data;
        } catch (err: any) {
            error.value = err?.message ?? 'Fetch failed';
        } finally {
            loading.value = false;
        }
    };

    const fetchChildren = async (parentId: number) => {
        loadingChildren[parentId] = true;
        try {
            const res = await axios.get(`${API_BASE}/${parentId}/children`);
            childrenMap[parentId] = res.data;
        } finally {
            loadingChildren[parentId] = false;
        }
    };

    const create = async (payload: Partial<Category>) => {
        loading.value = true;
        try {
            const res = await axios.post(API_BASE, payload);
            if (!payload.parent_id) {
                items.value.unshift(res.data);
            } else {
                if (childrenMap[payload.parent_id as number]) {
                    childrenMap[payload.parent_id as number].unshift(res.data);
                }
                const parent = items.value.find((c) => c.id === payload.parent_id);
                if (parent) {
                    parent.children_count = (parent.children_count ?? 0) + 1;
                }
            }
            return res.data;
        } finally {
            loading.value = false;
        }
    };

    const update = async (id: number, payload: Partial<Category>) => {
        loading.value = true;
        try {
            const res = await axios.put(`${API_BASE}/${id}`, payload);
            const idx = items.value.findIndex((c) => c.id === id);
            if (idx !== -1) {
                items.value[idx] = res.data;
            }
            for (const parentId in childrenMap) {
                const childIdx = childrenMap[+parentId].findIndex((c) => c.id === id);
                if (childIdx !== -1) {
                    childrenMap[+parentId][childIdx] = res.data;
                }
            }
            return res.data;
        } finally {
            loading.value = false;
        }
    };

    const remove = async (id: number, parentId?: number | null) => {
        loading.value = true;
        try {
            await axios.delete(`${API_BASE}/${id}`);
            if (!parentId) {
                items.value = items.value.filter((c) => c.id !== id);
                delete childrenMap[id];
            } else {
                if (childrenMap[parentId]) {
                    childrenMap[parentId] = childrenMap[parentId].filter((c) => c.id !== id);
                }
                const parent = items.value.find((c) => c.id === parentId);
                if (parent && parent.children_count) {
                    parent.children_count = Math.max(0, parent.children_count - 1);
                }
            }
        } finally {
            loading.value = false;
        }
    };

    return {
        items,
        loading,
        error,
        childrenMap,
        loadingChildren,
        fetch,
        fetchChildren,
        create,
        update,
        remove,
    };
};
