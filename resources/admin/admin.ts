
import '../css/app.css';
import AdminApp from './AdminApp.vue';
import { createApp } from 'vue';
import { router } from './router/index.js';
import axios from 'axios';

// Set default headers so Laravel middleware returns JSON errors
// and CSRF token is sent automatically via the XSRF-TOKEN cookie
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Accept'] = 'application/json';
axios.defaults.withCredentials = true;

// Redirect to login on 401 (session expired or not authenticated)
axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error?.response?.status === 401) {
            window.location.href = '/admin/login';
        }
        return Promise.reject(error);
    },
);

createApp(AdminApp)
    .use(router)
    .mount('#admin');
