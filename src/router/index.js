import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  {
    path: '/',
    redirect: '/dashboard'
  },
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/views/LoginView.vue'),
    meta: { requiresGuest: true }
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: () => import('@/views/DashboardView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/etudiants',
    name: 'Etudiants',
    component: () => import('@/views/EtudiantsView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/salles',
    name: 'Salles',
    component: () => import('@/views/SallesView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/acces',
    name: 'Acces',
    component: () => import('@/views/AccesView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/historique',
    name: 'Historique',
    component: () => import('@/views/HistoriqueView.vue'),
    meta: { requiresAuth: true }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Guards de navigation
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  } else if (to.meta.requiresGuest && authStore.isAuthenticated) {
    next('/dashboard')
  } else {
    next()
  }
})

export default router