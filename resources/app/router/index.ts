import { createRouter, createWebHistory } from 'vue-router';
import { useAuth } from '../composables/useAuth';

const INSTRUCTOR_ROLE = 2;

const routes = [
    { path: '/login', component: () => import('../pages/Login.vue'), meta: { guest: true } },
    { path: '/register', component: () => import('../pages/Register.vue'), meta: { guest: true } },
    { path: '/', component: () => import('../pages/Home.vue') },
    { path: '/about', component: () => import('../pages/About.vue') },
    { path: '/policy', component: () => import('../pages/Policy.vue') },
    { path: '/contact', component: () => import('../pages/Contact.vue') },
    { path: '/courses', component: () => import('../pages/Courses.vue') },
    { path: '/courses/:slug', component: () => import('../pages/CourseDetail.vue') },
    { path: '/learn/:slug', component: () => import('../pages/LearnCourse.vue') },
    { path: '/dashboard', component: () => import('../pages/Dashboard.vue'), meta: { requiresAuth: true } },
    { path: '/my-courses', component: () => import('../pages/InstructorCourses.vue'), meta: { requiresAuth: true, instructorOnly: true } },
    { path: '/my-courses/create', component: () => import('../pages/InstructorCreateCourse.vue'), meta: { requiresAuth: true, instructorOnly: true } },
    { path: '/my-courses/:courseId/edit', component: () => import('../pages/InstructorCourseEditor.vue'), meta: { requiresAuth: true, instructorOnly: true } },
    { path: '/profile', component: () => import('../pages/Profile.vue'), meta: { requiresAuth: true } },
    { path: '/:pathMatch(.*)*', redirect: '/' },
];

export const router = createRouter({
    history: createWebHistory('/'),
    routes,
});

router.beforeEach((to) => {
    const { user } = useAuth();

    if (to.meta.requiresAuth && !user.value) {
        return '/login';
    }

    if (to.meta.instructorOnly && user.value?.role !== INSTRUCTOR_ROLE) {
        return '/dashboard';
    }

    if (to.meta.guest && user.value) {
        return user.value.role === INSTRUCTOR_ROLE ? '/my-courses' : '/dashboard';
    }
});

