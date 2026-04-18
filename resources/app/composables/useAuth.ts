import { ref, readonly } from 'vue';
import axios from 'axios';

export interface AuthUser {
    id: number;
    name: string;
    email: string;
    role: number;
}

const user = ref<AuthUser | null>(null);
const resolved = ref(false);

export function useAuth() {
    const fetchUser = async (): Promise<void> => {
        try {
            const { data } = await axios.get<AuthUser | null>('/auth/me');
            // Server returns JSON null when unauthenticated; guard against empty objects
            user.value = (data && data.id) ? data : null;
        } catch {
            user.value = null;
        } finally {
            resolved.value = true;
        }
    };

    const setUser = (u: AuthUser | null) => {
        user.value = u;
    };

    const logout = async (): Promise<void> => {
        try {
            await axios.post('/auth/logout');
        } finally {
            user.value = null;
        }
    };

    return {
        user: readonly(user),
        resolved: readonly(resolved),
        fetchUser,
        setUser,
        logout,
    };
}
