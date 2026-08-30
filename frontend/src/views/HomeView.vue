<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { getTickets } from '@/api/tickets'
import { useAuthStore } from '@/stores/auth'
import type { Ticket, TicketPriority, TicketStatus } from '@/types/ticket'

const auth = useAuthStore()
const tickets = ref<Ticket[]>([])
const loading = ref(true)
const error = ref('')

const openTickets = computed(
  () => tickets.value.filter((ticket) => ticket.status === 'open').length,
)
const assignedTickets = computed(() =>
  auth.user?.role === 'requester'
    ? tickets.value.length
    : tickets.value.filter((ticket) => ticket.technician?.id === auth.user?.id).length,
)
const assignedLabel = computed(() =>
  auth.user?.role === 'requester' ? 'Meus chamados' : 'Sob minha responsabilidade',
)
const resolvedTickets = computed(
  () => tickets.value.filter((ticket) => ticket.status === 'resolved').length,
)
const recentTickets = computed(() => tickets.value.slice(0, 4))

function statusLabel(status: TicketStatus) {
  return {
    open: 'Aberto',
    in_progress: 'Em atendimento',
    resolved: 'Resolvido',
    closed: 'Fechado',
  }[status]
}

function priorityLabel(priority: TicketPriority) {
  return {
    low: 'Baixa',
    medium: 'Média',
    high: 'Alta',
  }[priority]
}

async function loadDashboard() {
  try {
    const response = await getTickets()
    tickets.value = response.data.data
  } catch {
    error.value = 'Não foi possível carregar o resumo dos chamados.'
  } finally {
    loading.value = false
  }
}

onMounted(loadDashboard)
</script>

<template>
  <div class="page">
    <div class="page-heading dashboard-heading">
      <div>
        <h1>Visão geral</h1>
        <p>Acompanhe o status e a fila de chamados disponíveis para você.</p>
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

    <p v-if="loading" class="loading-state">Carregando painel...</p>
    <p v-else-if="error" class="error-message">{{ error }}</p>

    <template v-else>
      <section class="stats-grid" aria-label="Resumo dos chamados">
        <article class="stat-card total">
          <div>
            <p>Total de chamados</p>
            <strong>{{ tickets.length }}</strong>
          </div>
          <span class="material-symbols-outlined">confirmation_number</span>
        </article>
        <article class="stat-card open">
          <div>
            <p>Disponíveis na fila</p>
            <strong>{{ openTickets }}</strong>
          </div>
          <span class="material-symbols-outlined">inbox</span>
        </article>
        <article class="stat-card assigned">
          <div>
            <p>{{ assignedLabel }}</p>
            <strong>{{ assignedTickets }}</strong>
          </div>
          <span class="material-symbols-outlined">assignment_ind</span>
        </article>
        <article class="stat-card resolved">
          <div>
            <p>Resolvidos</p>
            <strong>{{ resolvedTickets }}</strong>
          </div>
          <span class="material-symbols-outlined">check_circle</span>
        </article>
      </section>

      <section class="recent-section">
        <div class="section-title">
          <h2>Chamados recentes</h2>
          <RouterLink to="/tickets"
            >Ver todos <span class="material-symbols-outlined">arrow_forward</span></RouterLink
          >
        </div>

        <p v-if="recentTickets.length === 0" class="empty-state">Nenhum chamado encontrado.</p>

        <div v-else class="recent-list">
          <RouterLink
            v-for="ticket in recentTickets"
            :key="ticket.id"
            class="recent-ticket"
            :class="`priority-${ticket.priority}`"
            :to="`/tickets/${ticket.id}`"
          >
            <div class="recent-content">
              <div class="ticket-line">
                <span>#{{ ticket.id }}</span>
                <h3>{{ ticket.title }}</h3>
              </div>
              <p>{{ ticket.description }}</p>
            </div>
            <div class="ticket-badges">
              <span class="priority-badge" :class="`priority-${ticket.priority}`">{{
                priorityLabel(ticket.priority)
              }}</span>
              <span class="status-badge" :class="`status-${ticket.status}`">{{
                statusLabel(ticket.status)
              }}</span>
            </div>
          </RouterLink>
        </div>
      </section>
    </template>
  </div>
</template>

<style scoped>
.dashboard-heading {
  align-items: center;
}
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 24px;
}
.stat-card {
  position: relative;
  display: flex;
  min-height: 144px;
  align-items: flex-start;
  justify-content: space-between;
  overflow: hidden;
  padding: 24px;
  border: 1px solid var(--outline);
  border-radius: 16px;
  background: var(--surface-white);
  box-shadow: 0 4px 12px rgb(0 0 0 / 5%);
}
.stat-card::before {
  position: absolute;
  inset: 0 auto 0 0;
  width: 4px;
  background: var(--primary);
  content: '';
}
.stat-card.assigned::before {
  background: var(--warning);
}
.stat-card.resolved::before {
  background: var(--success);
}
.stat-card p {
  margin: 0 0 8px;
  color: var(--text-muted);
  font-size: 12px;
  font-weight: 600;
  letter-spacing: 0.04em;
}
.stat-card strong {
  font-size: 32px;
  line-height: 40px;
}
.stat-card > .material-symbols-outlined {
  padding: 10px;
  border-radius: 10px;
  background: var(--primary-soft);
  color: var(--primary);
}
.stat-card.assigned > .material-symbols-outlined {
  background: var(--warning-soft);
  color: var(--warning);
}
.stat-card.resolved > .material-symbols-outlined {
  background: var(--success-soft);
  color: var(--success);
}
.recent-section {
  margin-top: 40px;
}
.section-title {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 16px;
}
.section-title h2 {
  margin: 0;
  font-size: 24px;
}
.section-title a {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  color: var(--primary);
  font-size: 13px;
  font-weight: 600;
}
.section-title .material-symbols-outlined {
  font-size: 18px;
}
.recent-list {
  display: grid;
  gap: 12px;
}
.recent-ticket {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
  overflow: hidden;
  padding: 16px 20px 16px 24px;
  border: 1px solid var(--outline);
  border-radius: 12px;
  background: var(--surface-white);
  transition: background 160ms ease;
}
.recent-ticket::before {
  position: absolute;
  inset: 0 auto 0 0;
  width: 4px;
  background: var(--primary);
  content: '';
}
.recent-ticket.priority-medium::before {
  background: var(--warning);
}
.recent-ticket.priority-high::before {
  background: var(--danger);
}
.recent-ticket:hover {
  background: var(--surface-low);
}
.ticket-line {
  display: flex;
  align-items: baseline;
  gap: 12px;
}
.ticket-line span {
  color: var(--text-muted);
  font-size: 12px;
  font-weight: 600;
}
.ticket-line h3 {
  margin: 0;
  font-size: 16px;
}
.recent-content p {
  overflow: hidden;
  margin: 6px 0 0;
  color: var(--text-muted);
  font-size: 14px;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.ticket-badges {
  display: flex;
  flex: 0 0 auto;
  flex-wrap: wrap;
  gap: 8px;
}
.priority-badge,
.status-badge {
  display: inline-flex;
  align-items: center;
  min-height: 26px;
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
@media (max-width: 900px) {
  .stats-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}
@media (max-width: 767px) {
  .stats-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  .stat-card {
    min-height: 132px;
  }
  .recent-ticket {
    align-items: flex-start;
    flex-direction: column;
    gap: 12px;
  }
  .ticket-badges {
    padding-left: 0;
  }
}
</style>
