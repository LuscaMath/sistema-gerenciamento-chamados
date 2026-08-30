import { defineStore } from 'pinia'
import { api } from '@/api/client'
import type { User } from '@/types/user'

interface LoginPayload {
  email: string
  password: string
}

interface LoginResponse {
  user: User
}

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null as User | null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.user,
  },

  actions: {
    async login(payload: LoginPayload) {
      await api.get('/sanctum/csrf-cookie')

      const response = await api.post<LoginResponse>('/api/v1/login', payload)

      this.user = response.data.user
    },

    async fetchUser() {
      try {
        const response = await api.get<User>('/api/v1/me')
        this.user = response.data
      } catch {
        this.user = null
      }
    },

    async logout() {
      await api.post('/api/v1/logout')
      this.user = null
    },
  },
})
