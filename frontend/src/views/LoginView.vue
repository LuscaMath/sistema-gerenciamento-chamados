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
  <main class="login-page">
    <section class="login-card">
      <div class="login-brand-mark">
        <span class="material-symbols-outlined">support_agent</span>
      </div>
      <div class="login-heading">
        <h1>YTickets</h1>
        <p>Acesse para acompanhar e gerenciar seus chamados.</p>
      </div>

      <form class="login-form" @submit.prevent="handleLogin">
        <div>
          <label class="field-label" for="email">E-mail corporativo</label>
          <div class="login-input">
            <span class="material-symbols-outlined">mail</span>
            <input
              id="email"
              v-model="email"
              autocomplete="email"
              placeholder="nome@empresa.com"
              required
              type="email"
            />
          </div>
        </div>

        <div>
          <label class="field-label" for="password">Senha</label>
          <div class="login-input">
            <span class="material-symbols-outlined">lock</span>
            <input
              id="password"
              v-model="password"
              autocomplete="current-password"
              placeholder="Digite sua senha"
              required
              type="password"
            />
          </div>
        </div>

        <p v-if="error" class="error-message">{{ error }}</p>

        <button class="primary-button login-button" type="submit">
          <span class="material-symbols-outlined">login</span>
          Entrar
        </button>
      </form>

      <p class="login-help">Use as credenciais fornecidas pelo administrador do sistema.</p>
    </section>
  </main>
</template>

<style scoped>
.login-page {
  display: grid;
  min-height: 100vh;
  place-items: center;
  padding: 24px;
  background: linear-gradient(135deg, var(--surface) 0%, #eef0ff 100%);
}
.login-card {
  width: min(100%, 440px);
  padding: 40px;
  border: 1px solid var(--outline);
  border-radius: 16px;
  background: var(--surface-white);
  box-shadow: 0 16px 48px rgb(25 28 29 / 8%);
}
.login-brand-mark {
  display: grid;
  width: 52px;
  height: 52px;
  place-items: center;
  margin: 0 auto 20px;
  border-radius: 12px;
  background: var(--primary);
  color: #fff;
}
.login-brand-mark .material-symbols-outlined {
  font-size: 28px;
}
.login-heading {
  text-align: center;
}
.login-heading h1 {
  margin: 0;
  font-size: 28px;
  letter-spacing: -0.02em;
}
.login-heading p {
  margin: 8px 0 32px;
  color: var(--text-muted);
  font-size: 14px;
}
.login-form {
  display: grid;
  gap: 20px;
}
.login-input {
  display: flex;
  align-items: center;
  gap: 10px;
  min-height: 48px;
  padding: 0 14px;
  border: 1px solid var(--outline);
  border-radius: 8px;
  transition:
    border-color 160ms ease,
    box-shadow 160ms ease;
}
.login-input:focus-within {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgb(36 56 156 / 10%);
}
.login-input span {
  color: var(--text-muted);
}
.login-input input {
  width: 100%;
  border: 0;
  outline: 0;
  background: transparent;
}
.login-button {
  width: 100%;
  margin-top: 4px;
}
.login-help {
  margin: 24px 0 0;
  color: var(--text-muted);
  font-size: 12px;
  line-height: 18px;
  text-align: center;
}
@media (max-width: 480px) {
  .login-page {
    padding: 16px;
  }
  .login-card {
    padding: 32px 24px;
  }
}
</style>
