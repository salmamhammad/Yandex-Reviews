import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../views/LoginView.vue'
import SettingsView from '../views/SettingsView.vue'
import OrganizationView from '../views/OrganizationView.vue'
import { useAuthStore } from '../stores/auth'

const routes = [
  { path: '/login', component: LoginView, meta: { guest: true } },
  { path: '/', component: SettingsView, meta: { requiresAuth: true } },
  { path: '/org/:id', component: OrganizationView, meta: { requiresAuth: true } }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  if (to.meta.requiresAuth && !authStore.user) {
    next('/login')
  } else if (to.meta.guest && authStore.user) {
    next('/')
  } else {
    next()
  }
})

export default router