
import '../css/app.css';
import AdminApp from './AdminApp.vue'; // or the correct path to your AdminApp component
import { createApp } from 'vue';
import { router } from './router';


createApp(AdminApp)
    .use(router)
    .mount('#admin'); // trùng với <div id="admin"> trong Blade
