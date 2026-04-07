import { ref } from 'vue';

export type AdminUser = {
    name: string;
    email: string;
};

const STORAGE_KEY = 'cloudy-learning.admin-user';
const adminUser = ref<AdminUser | null>(null);

const isBrowser = typeof window !== 'undefined';

const readStoredAdminUser = (): AdminUser | null => {
    if (!isBrowser) {
        return null;
    }

    const value = window.localStorage.getItem(STORAGE_KEY);

    if (!value) {
        return null;
    }

    try {
        return JSON.parse(value) as AdminUser;
    } catch {
        window.localStorage.removeItem(STORAGE_KEY);
        return null;
    }
};

export const initializeAdminUser = () => {
    if (!adminUser.value) {
        adminUser.value = readStoredAdminUser();
    }
};

export const useAdminUser = () => {
    initializeAdminUser();

    const setAdminUser = (user: AdminUser) => {
        adminUser.value = user;

        if (isBrowser) {
            window.localStorage.setItem(STORAGE_KEY, JSON.stringify(user));
        }
    };

    const clearAdminUser = () => {
        adminUser.value = null;

        if (isBrowser) {
            window.localStorage.removeItem(STORAGE_KEY);
        }
    };

    return {
        adminUser,
        setAdminUser,
        clearAdminUser,
    };
};
