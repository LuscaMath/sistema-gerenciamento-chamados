<script setup lang="ts">
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()

const initials = computed(
  () =>
    auth.user?.name
      .split(' ')
      .map((part) => part[0])
      .join('')
      .slice(0, 2)
      .toUpperCase() ?? 'US',
)

const roleLabel = computed(() => {
  const labels = {
    requester: 'Solicitante',
    technician: 'Técnico de suporte',
    admin: 'Administrador',
  }

  return auth.user ? labels[auth.user.role] : ''
})

const pageTitle = computed(() => String(route.meta.title ?? 'Chamados'))

async function handleLogout() {
  await auth.logout()
  await router.push('/login')
}
</script>

<template>
  <div class="app-shell">
    <aside class="app-sidebar">
      <div class="brand">
        <span class="brand-mark material-symbols-outlined">support_agent</span>
        <span>YTickets</span>
      </div>

      <div class="user-summary">
        <span class="avatar">{{ initials }}</span>
        <div>
          <strong>{{ auth.user?.name }}</strong>
          <span>{{ roleLabel }}</span>
        </div>
      </div>

      <nav class="side-nav" aria-label="Navegação principal">
        <RouterLink class="nav-link" to="/">
          <span class="material-symbols-outlined">dashboard</span>
          Início
        </RouterLink>
        <RouterLink class="nav-link" to="/tickets">
          <span class="material-symbols-outlined">confirmation_number</span>
          Chamados
        </RouterLink>
        <RouterLink v-if="auth.user?.role === 'requester'" class="nav-link" to="/tickets/create">
          <span class="material-symbols-outlined">add_circle</span>
          Novo chamado
        </RouterLink>
        <RouterLink v-if="auth.user?.role === 'admin'" class="nav-link" to="/categories">
          <span class="material-symbols-outlined">category</span>
          Categorias
        </RouterLink>
        <RouterLink v-if="auth.user?.role === 'admin'" class="nav-link" to="/users">
          <span class="material-symbols-outlined">group</span>
          Usuários
        </RouterLink>
      </nav>

      <button class="nav-link logout-button" @click="handleLogout">
        <span class="material-symbols-outlined">logout</span>
        Sair
      </button>
    </aside>

    <main class="app-main">
      <header class="mobile-header">
        <span class="material-symbols-outlined">menu</span>
        <h1>{{ pageTitle }}</h1>
        <span class="avatar">{{ initials }}</span>
      </header>

      <RouterView />
    </main>

    <nav class="mobile-nav" aria-label="Navegação móvel">
      <RouterLink class="nav-link" to="/">
        <span class="material-symbols-outlined">home</span>
        Início
      </RouterLink>
      <RouterLink class="nav-link" to="/tickets">
        <span class="material-symbols-outlined">list_alt</span>
        Chamados
      </RouterLink>
      <RouterLink v-if="auth.user?.role === 'requester'" class="nav-link" to="/tickets/create">
        <span class="material-symbols-outlined">add</span>
        Novo
      </RouterLink>
      <RouterLink v-if="auth.user?.role === 'admin'" class="nav-link" to="/categories">
        <span class="material-symbols-outlined">category</span>
        Categorias
      </RouterLink>
      <RouterLink v-if="auth.user?.role === 'admin'" class="nav-link" to="/users">
        <span class="material-symbols-outlined">group</span>
        Usuários
      </RouterLink>
    </nav>
  </div>
</template>
