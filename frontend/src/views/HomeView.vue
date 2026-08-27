<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const router = useRouter()

async function handleLogout() {
  await auth.logout()
  await router.push('/login')
}
</script>

<template>
  <main>
    <h1>Área interna</h1>

    <p v-if="auth.user">
      Olá, {{ auth.user.name }}
    </p>

    <button @click="handleLogout">
      Sair
    </button>
  </main>
</template>