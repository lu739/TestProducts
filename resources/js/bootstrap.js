import axios from 'axios';
import { loadAuthUserFromStorage } from './composables/useAuth';

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['Accept'] = 'application/json';
window.axios.defaults.withCredentials = true;

const csrf = document.querySelector('meta[name="csrf-token"]');
if (csrf) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] =
        csrf.getAttribute('content');
}

window.axios.interceptors.request.use((config) => {
    const t = localStorage.getItem('api_token');
    if (t) {
        config.headers.Authorization = `Bearer ${t}`;
    }

    return config;
});

loadAuthUserFromStorage();
