<template>
  <div id="app">
    <nav>
      <router-link to="/">Home</router-link>
      <button v-if="authStore.user" @click="logout">Logout</button>
      <router-link v-else to="/login">Login</router-link>
    </nav>
    <router-view />
  </div>
</template>

<script setup>
import { useAuthStore } from './stores/auth'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()

const logout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>