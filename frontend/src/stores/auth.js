import { defineStore } from 'pinia'
import axios from '../axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null
  }),
  actions: {
    async login(email, password) {
      await axios.get('/sanctum/csrf-cookie')
      const response = await axios.post('/api/login', { email, password })
      this.user = response.data.user
    },
    async logout() {
      await axios.post('/api/logout')
      this.user = null
    },
    async fetchUser() {
      try {
        const response = await axios.get('/api/user')
        this.user = response.data
      } catch (error) {
        this.user = null
      }
    }
  }
})