<script setup lang="ts">
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const router = useRouter()

const email = ref('')
const password = ref('')
const error = ref('')

async function handleLogin() {
  error.value = ''

  try {
    await auth.login({
      email: email.value,
      password: password.value,
    })

    await router.push('/')
  } catch {
    error.value = 'Não foi possível realizar o login.'
  }
}
</script>

<template>
  <main>
    <form @submit.prevent="handleLogin">
      <div>
        <label for="email">E-mail</label>
        <input id="email" v-model="email" type="email" />
      </div>

      <div>
        <label for="password">Senha</label>
        <input id="password" v-model="password" type="password" />
      </div>

      <p v-if="error">
        {{ error }}
      </p>

      <button type="submit">
        Entrar
      </button>
    </form>

    <div v-if="auth.user">
      <p>Usuário: {{ auth.user.name }}</p>
      <p>Perfil: {{ auth.user.role }}</p>
    </div>
  </main>
</template>