<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { getTickets } from '@/api/tickets'
import type { Ticket } from '@/types/ticket'
import { useAuthStore } from '@/stores/auth'

const tickets = ref<Ticket[]>([])
const loading = ref(true)
const error = ref('')
const auth = useAuthStore()

async function loadTickets() {
  try {
    const response = await getTickets()
    tickets.value = response.data.data
  } catch {
    error.value = 'Não foi possível carregar os chamados.'
  } finally {
    loading.value = false
  }
}

onMounted(loadTickets)
</script>

<template>
  <section>
    <h1>Chamados</h1>

    <RouterLink v-if="auth.user?.role === 'requester'" to="/tickets/create">
      Novo chamado
    </RouterLink>

    <p v-if="loading">Carregando...</p>

    <p v-else-if="error">
      {{ error }}
    </p>

    <p v-else-if="tickets.length === 0">
      Nenhum chamado encontrado.
    </p>

    <ul v-else>
      <li v-for="ticket in tickets" :key="ticket.id">
        <RouterLink :to="`/tickets/${ticket.id}`">

          <strong>#{{ ticket.id }} - {{ ticket.title }}</strong>

          <p>
            Categoria: {{ ticket.category.name }}
          </p>

          <p>
            Status: {{ ticket.status }}
          </p>

          <p>
            Prioridade: {{ ticket.priority }}
          </p>
        </RouterLink>
      </li>
    </ul>
  </section>
</template>