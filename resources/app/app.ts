import '../css/app.css';
import { createApp } from 'vue';
import { router } from './router';
import AppRoot from './AppRoot.vue';
import axios from 'axios';
import { useAuth } from './composables/useAuth';
import { i18n } from '../i18n';

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Accept'] = 'application/json';
axios.defaults.withCredentials = true;

const { fetchUser } = useAuth();

fetchUser().then(() => {
    createApp(AppRoot)
        .use(router)
        .use(i18n)
        .mount('#app');
});
