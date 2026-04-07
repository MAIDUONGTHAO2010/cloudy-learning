import { createRouter, createWebHistory } from 'vue-router';

const routes = [
    { path: '/login', component: () => import('../Login.vue'), meta: { guestLayout: true } },
    { path: '/', redirect: '/dashboard' },
    { path: '/dashboard', component: () => import('../Dashboard.vue') },
    { path: '/users', component: () => import('../Users.vue') },
    { path: '/categories', component: () => import('../Users.vue') },
];

export const router = createRouter({
    history: createWebHistory('/admin/'), // prefix admin
    routes,
});
