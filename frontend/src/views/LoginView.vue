<template>
  <div class="login">
    <div class="login-card">
      <h2>Login</h2>

      <form @submit.prevent="handleLogin">
        <input
          v-model="email"
          type="email"
          placeholder="Email"
          required
        />

        <input
          v-model="password"
          type="password"
          placeholder="Password"
          required
        />

        <button type="submit" :disabled="loading">
          <span v-if="loading" class="spinner"></span>
          {{ loading ? 'Logging in...' : 'Login' }}
        </button>
      </form>

      <p class="hint">
        Use <strong>demo@example.com</strong> /
        <strong>password</strong>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useRouter } from 'vue-router'
const email = ref('demo@example.com')
const password = ref('password')
const loading = ref(false)

const authStore = useAuthStore()
const router = useRouter()

const handleLogin = async () => {
  loading.value = true

  try {
    await authStore.login(email.value, password.value)
    router.push('/')
  } catch (err) {
    alert('Login failed')
  } finally {
    loading.value = false
  }
}
</script>
