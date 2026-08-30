<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { getCategories } from '@/api/categories'
import { getTickets } from '@/api/tickets'
import { useAuthStore } from '@/stores/auth'
import type { Category } from '@/types/category'
import type { Ticket, TicketPriority, TicketStatus } from '@/types/ticket'

const auth = useAuthStore()
const tickets = ref<Ticket[]>([])
const categories = ref<Category[]>([])
const loading = ref(true)
const error = ref('')
const search = ref('')
const status = ref<TicketStatus | ''>('')
const priority = ref<TicketPriority | ''>('')
const categoryId = ref<number | ''>('')

const visibleTickets = computed(() => {
  const term = search.value.trim().toLocaleLowerCase()

  if (!term) return tickets.value

  return tickets.value.filter((ticket) =>
    [ticket.id.toString(), ticket.title, ticket.requester.name, ticket.category.name]
      .join(' ')
      .toLocaleLowerCase()
      .includes(term),
  )
})

function statusLabel(ticketStatus: TicketStatus) {
  return {
    open: 'Aberto',
    in_progress: 'Em atendimento',
    resolved: 'Resolvido',
    closed: 'Fechado',
  }[ticketStatus]
}

function priorityLabel(ticketPriority: TicketPriority) {
  return {
    low: 'Baixa',
    medium: 'Média',
    high: 'Alta',
  }[ticketPriority]
}

function initials(name: string) {
  return name
    .split(' ')
    .map((part) => part[0])
    .join('')
    .slice(0, 2)
    .toUpperCase()
}

async function loadTickets() {
  loading.value = true
  error.value = ''

  try {
    const response = await getTickets({
      ...(status.value ? { status: status.value } : {}),
      ...(priority.value ? { priority: priority.value } : {}),
      ...(categoryId.value ? { category_id: categoryId.value } : {}),
    })
    tickets.value = response.data.data
  } catch {
    error.value = 'Não foi possível carregar os chamados.'
  } finally {
    loading.value = false
  }
}

async function loadCategories() {
  try {
    const response = await getCategories()
    categories.value = response.data.data
  } catch {
    error.value = 'Não foi possível carregar as categorias para filtragem.'
  }
}

function clearFilters() {
  search.value = ''
  status.value = ''
  priority.value = ''
  categoryId.value = ''
  loadTickets()
}

onMounted(async () => {
  await Promise.all([loadTickets(), loadCategories()])
})
</script>

<template>
  <div class="page">
    <div class="page-heading tickets-heading">
      <div>
        <h1>Lista de chamados</h1>
        <p>Consulte, filtre e acompanhe todas as solicitações disponíveis para seu perfil.</p>
      </div>
      <RouterLink
        v-if="auth.user?.role === 'requester'"
        class="primary-button"
        to="/tickets/create"
      >
        <span class="material-symbols-outlined">add</span>
        Novo chamado
      </RouterLink>
    </div>

    <form class="filter-card card" @submit.prevent="loadTickets">
      <div class="search-field">
        <label class="field-label" for="ticket-search">Buscar</label>
        <div class="search-control">
          <span class="material-symbols-outlined">search</span>
          <input
            id="ticket-search"
            v-model="search"
            placeholder="ID, título, solicitante ou categoria"
            type="search"
          />
        </div>
      </div>

      <div class="filter-fields">
        <div>
          <label class="field-label" for="ticket-status">Status</label>
          <select id="ticket-status" v-model="status" class="field-control">
            <option value="">Todos</option>
            <option value="open">Aberto</option>
            <option value="in_progress">Em atendimento</option>
            <option value="resolved">Resolvido</option>
            <option value="closed">Fechado</option>
          </select>
        </div>
        <div>
          <label class="field-label" for="ticket-priority">Prioridade</label>
          <select id="ticket-priority" v-model="priority" class="field-control">
            <option value="">Todas</option>
            <option value="high">Alta</option>
            <option value="medium">Média</option>
            <option value="low">Baixa</option>
          </select>
        </div>
        <div>
          <label class="field-label" for="ticket-category">Categoria</label>
          <select id="ticket-category" v-model="categoryId" class="field-control">
            <option value="">Todas</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.name }}
            </option>
          </select>
        </div>
      </div>

      <div class="filter-actions">
        <button class="primary-button" type="submit">Aplicar filtros</button>
        <button class="text-button" type="button" @click="clearFilters">Limpar</button>
      </div>
    </form>

    <p v-if="loading" class="loading-state">Carregando chamados...</p>
    <p v-else-if="error" class="error-message">{{ error }}</p>
    <p v-else-if="visibleTickets.length === 0" class="empty-state">
      Nenhum chamado encontrado com esses filtros.
    </p>

    <div v-else class="ticket-list">
      <RouterLink
        v-for="ticket in visibleTickets"
        :key="ticket.id"
        class="ticket-card"
        :class="`priority-${ticket.priority}`"
        :to="`/tickets/${ticket.id}`"
      >
        <div class="ticket-card-main">
          <div class="ticket-topline">
            <span class="ticket-id">#{{ ticket.id }}</span>
            <span class="priority-badge" :class="`priority-${ticket.priority}`">{{
              priorityLabel(ticket.priority)
            }}</span>
            <span class="status-badge" :class="`status-${ticket.status}`">{{
              statusLabel(ticket.status)
            }}</span>
          </div>
          <h2>{{ ticket.title }}</h2>
          <p>{{ ticket.description }}</p>
        </div>
        <div class="ticket-card-footer">
          <span class="requester"
            ><span class="requester-avatar">{{ initials(ticket.requester.name) }}</span
            >{{ ticket.requester.name }}</span
          >
          <span
            ><span class="material-symbols-outlined">category</span>{{ ticket.category.name }}</span
          >
          <span v-if="ticket.technician"
            ><span class="material-symbols-outlined">engineering</span
            >{{ ticket.technician.name }}</span
          >
        </div>
      </RouterLink>
    </div>
  </div>
</template>

<style scoped>
.tickets-heading {
  align-items: center;
}
.filter-card {
  display: grid;
  grid-template-columns: minmax(240px, 1.3fr) 2fr auto;
  gap: 20px;
  align-items: end;
  margin-bottom: 32px;
  padding: 20px;
}
.search-control {
  display: flex;
  align-items: center;
  gap: 8px;
  min-height: 44px;
  padding: 0 12px;
  border: 1px solid var(--outline);
  border-radius: 8px;
  background: var(--surface-low);
}
.search-control:focus-within {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgb(36 56 156 / 10%);
}
.search-control span {
  color: var(--text-muted);
}
.search-control input {
  width: 100%;
  border: 0;
  outline: 0;
  background: transparent;
  color: var(--text);
}
.filter-fields {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 12px;
}
.filter-actions {
  display: flex;
  gap: 4px;
}
.ticket-list {
  display: grid;
  gap: 16px;
}
.ticket-card {
  position: relative;
  overflow: hidden;
  border: 1px solid var(--outline);
  border-radius: 12px;
  background: var(--surface-white);
  padding: 20px 24px;
  transition:
    background 160ms ease,
    box-shadow 160ms ease;
}
.ticket-card::before {
  position: absolute;
  inset: 0 auto 0 0;
  width: 4px;
  background: var(--primary);
  content: '';
}
.ticket-card.priority-medium::before {
  background: var(--warning);
}
.ticket-card.priority-high::before {
  background: var(--danger);
}
.ticket-card:hover {
  background: var(--surface-low);
  box-shadow: 0 4px 12px rgb(0 0 0 / 5%);
}
.ticket-topline,
.ticket-card-footer {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 8px;
}
.ticket-id {
  margin-right: 4px;
  color: var(--text-muted);
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.06em;
}
.priority-badge,
.status-badge {
  display: inline-flex;
  min-height: 26px;
  align-items: center;
  padding: 4px 10px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 600;
}
.priority-badge.priority-high {
  background: var(--danger-soft);
  color: var(--danger);
}
.priority-badge.priority-medium {
  background: var(--warning-soft);
  color: var(--warning);
}
.priority-badge.priority-low {
  background: var(--closed-soft);
  color: var(--closed);
}
.status-badge.status-open {
  background: var(--primary-soft);
  color: var(--primary);
}
.status-badge.status-in_progress {
  background: var(--warning-soft);
  color: var(--warning);
}
.status-badge.status-resolved {
  background: var(--success-soft);
  color: var(--success);
}
.status-badge.status-closed {
  background: var(--closed-soft);
  color: var(--closed);
}
.ticket-card h2 {
  margin: 12px 0 6px;
  font-size: 20px;
  line-height: 28px;
}
.ticket-card-main > p {
  overflow: hidden;
  max-width: 940px;
  margin: 0;
  color: var(--text-muted);
  text-overflow: ellipsis;
  white-space: nowrap;
}
.ticket-card-footer {
  margin-top: 18px;
  padding-top: 14px;
  border-top: 1px solid var(--outline);
  color: var(--text-muted);
  font-size: 13px;
}
.ticket-card-footer > span {
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
.ticket-card-footer .material-symbols-outlined {
  font-size: 17px;
}
.requester {
  margin-right: 8px;
  color: var(--text);
}
.requester-avatar {
  display: grid;
  width: 26px;
  height: 26px;
  place-items: center;
  border-radius: 50%;
  background: var(--surface-container);
  color: var(--text-muted);
  font-size: 10px;
  font-weight: 700;
}
@media (max-width: 1000px) {
  .filter-card {
    grid-template-columns: 1fr;
  }
  .filter-actions {
    justify-content: flex-start;
  }
}
@media (max-width: 767px) {
  .filter-fields {
    grid-template-columns: 1fr;
  }
  .ticket-card {
    padding: 18px 18px 18px 22px;
  }
  .ticket-card h2 {
    font-size: 19px;
  }
  .ticket-card-main > p {
    white-space: normal;
  }
}
</style>
