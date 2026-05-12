import axios from 'axios';
import { router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { computed, ref } from 'vue';

const STORAGE_USER = 'auth_user';
const STORAGE_TOKEN = 'api_token';

/** Пользователь с API (Sanctum), синхронизируется с localStorage. */
const apiUser = ref(null);

export function loadAuthUserFromStorage() {
    try {
        const raw = localStorage.getItem(STORAGE_USER);
        apiUser.value = raw ? JSON.parse(raw) : null;
    } catch {
        apiUser.value = null;
    }
}

/**
 * @param {Record<string, unknown>|null} user
 */
export function setAuthApiUser(user) {
    if (user && typeof user === 'object') {
        localStorage.setItem(STORAGE_USER, JSON.stringify(user));
        apiUser.value = user;
    } else {
        localStorage.removeItem(STORAGE_USER);
        apiUser.value = null;
    }
}

export function clearApiAuth() {
    localStorage.removeItem(STORAGE_TOKEN);
    localStorage.removeItem(STORAGE_USER);
    delete window.axios.defaults.headers.common.Authorization;
    apiUser.value = null;
}

/**
 * Профиль пользователя из Sanctum API (токен + localStorage).
 */
export function useAuth() {
    const user = computed(() => apiUser.value ?? null);

    const isLoggedIn = computed(() => user.value != null);

    const primaryLabel = computed(() => {
        const u = user.value;
        if (!u) {
            return '';
        }
        const name = typeof u.name === 'string' ? u.name.trim() : '';
        const email = typeof u.email === 'string' ? u.email.trim() : '';
        if (name !== '') {
            return name;
        }

        return email;
    });

    const emailLine = computed(() => {
        const u = user.value;
        if (!u) {
            return '';
        }
        const name = typeof u.name === 'string' ? u.name.trim() : '';
        const email = typeof u.email === 'string' ? u.email.trim() : '';
        if (name !== '' && email !== '') {
            return email;
        }

        return '';
    });

    async function ensureRemoteUser() {
        loadAuthUserFromStorage();
        const hasToken = !!localStorage.getItem(STORAGE_TOKEN);
        if (!hasToken || apiUser.value) {
            return;
        }
        try {
            const { data } = await axios.get(route('api.user'));
            setAuthApiUser(data);
        } catch {
            clearApiAuth();
        }
    }

    async function logout() {
        try {
            await axios.post(route('api.logout'));
        } catch {
            /* сеть / 401 — всё равно чистим клиент */
        }
        clearApiAuth();
        router.visit('/');
    }

    return {
        apiUser,
        user,
        isLoggedIn,
        primaryLabel,
        emailLine,
        ensureRemoteUser,
        logout,
    };
}
