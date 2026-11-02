import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/login',
            name: 'login',
            component: () => import('@/views/Auth/Login.vue'),
            meta: { guest: true },
        },
        {
            path: '/register',
            name: 'register',
            component: () => import('@/views/Auth/Register.vue'),
            meta: { guest: true },
        },
        {
            path: '/',
            name: 'dashboard',
            component: () => import('@/views/Dashboard.vue'),
            meta: { requiresAuth: true },
        },
        {
            path: '/projects',
            name: 'projects.index',
            component: () => import('@/views/Projects/Index.vue'),
            meta: { requiresAuth: true },
        },
        {
            path: '/projects/:id',
            name: 'projects.show',
            component: () => import('@/views/Projects/Show.vue'),
            meta: { requiresAuth: true },
        },
        {
            path: '/projects/:projectId/notes',
            name: 'projects.notes.index',
            component: () => import('@/views/Projects/Notes/Index.vue'),
            meta: { requiresAuth: true },
        },
        {
            path: '/projects/:projectId/notes/:noteId',
            name: 'projects.notes.show',
            component: () => import('@/views/Projects/Notes/Show.vue'),
            meta: { requiresAuth: true },
        },
        {
            path: '/projects/:projectId/notes/:noteId/edit',
            name: 'projects.notes.edit',
            component: () => import('@/views/Projects/Notes/Edit.vue'),
            meta: { requiresAuth: true },
        },
        {
            path: '/search',
            name: 'search',
            component: () => import('@/views/Search/Index.vue'),
            meta: { requiresAuth: true },
        },
        {
            path: '/settings/profile',
            name: 'settings.profile',
            component: () => import('@/views/Settings/Profile.vue'),
            meta: { requiresAuth: true },
        },
        {
            path: '/settings/password',
            name: 'settings.password',
            component: () => import('@/views/Settings/Password.vue'),
            meta: { requiresAuth: true },
        },
        {
            path: '/settings/tokens',
            name: 'settings.tokens',
            component: () => import('@/views/Settings/Tokens.vue'),
            meta: { requiresAuth: true },
        },
        {
            path: '/admin/invites',
            name: 'admin.invites',
            component: () => import('@/views/Admin/Invites.vue'),
            meta: { requiresAuth: true },
        },
    ],
});

router.beforeEach((to, from, next) => {
    const auth = useAuthStore();

    if (to.meta.requiresAuth && !auth.isAuthenticated) {
        next('/login');
        return;
    }

    if (to.meta.guest && auth.isAuthenticated) {
        next('/');
        return;
    }

    next();
});

export default router;
