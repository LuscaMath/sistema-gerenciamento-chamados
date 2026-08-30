<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import {
  assignTechnician,
  assignTicket,
  closeTicket,
  getTechnicians,
  getTicket,
  resolveTicket,
} from '@/api/tickets'
import { useAuthStore } from '@/stores/auth'
import type { Ticket, TicketPriority, TicketStatus } from '@/types/ticket'
import type { Technician } from '@/types/technician'
import { getTicketComments, createTicketComment } from '@/api/ticket-comments'
import type { TicketComment } from '@/types/ticket-comment'

const route = useRoute()
const auth = useAuthStore()
const actionError = ref('')
const actionLoading = ref(false)

const ticket = ref<Ticket | null>(null)
const solution = ref('')
const loading = ref(true)
const error = ref('')
const comments = ref<TicketComment[]>([])
const commentContent = ref('')
const commentLoading = ref(false)
const technicians = ref<Technician[]>([])
const selectedTechnicianId = ref<number | null>(null)

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

function formatDate(date: string | null) {
  if (!date) return 'Não informado'

  return new Intl.DateTimeFormat('pt-BR', {
    dateStyle: 'medium',
    timeStyle: 'short',
  }).format(new Date(date))
}

async function loadTicket() {
  try {
    const id = Number(route.params.id)

    const response = await getTicket(id)

    ticket.value = response.data.data
    await loadComments()
  } catch {
    error.value = 'Não foi possível carregar o chamado.'
  } finally {
    loading.value = false
  }
}

async function handleAssign() {
  if (!ticket.value) return

  actionLoading.value = true
  actionError.value = ''

  try {
    const response = await assignTicket(ticket.value.id)
    ticket.value = response.data.data
  } catch {
    actionError.value = 'Não foi possível assumir o chamado.'
  } finally {
    actionLoading.value = false
  }
}

async function loadTechnicians() {
  try {
    const response = await getTechnicians()
    technicians.value = response.data.data
  } catch {
    actionError.value = 'Não foi possível carregar os técnicos disponíveis.'
  }
}

async function handleAdminAssign() {
  if (!ticket.value || !selectedTechnicianId.value) return

  actionLoading.value = true
  actionError.value = ''

  try {
    const response = await assignTechnician(ticket.value.id, {
      technician_id: selectedTechnicianId.value,
    })

    ticket.value = response.data.data
    selectedTechnicianId.value = null
  } catch {
    actionError.value = 'Não foi possível atribuir o técnico ao chamado.'
  } finally {
    actionLoading.value = false
  }
}

async function handleResolve() {
  if (!ticket.value || !solution.value.trim()) return

  actionLoading.value = true
  actionError.value = ''

  try {
    const response = await resolveTicket(ticket.value.id, {
      solution: solution.value,
    })

    ticket.value = response.data.data
    solution.value = ''
  } catch {
    actionError.value = 'Não foi possível resolver o chamado.'
  } finally {
    actionLoading.value = false
  }
}

async function handleClose() {
  if (!ticket.value) return

  actionLoading.value = true
  actionError.value = ''

  try {
    const response = await closeTicket(ticket.value.id)
    ticket.value = response.data.data
  } catch {
    actionError.value = 'Não foi possível fechar o chamado.'
  } finally {
    actionLoading.value = false
  }
}

async function loadComments() {
  if (!ticket.value) return

  const response = await getTicketComments(ticket.value.id)
  comments.value = response.data.data
}

async function handleComment() {
  if (!ticket.value || !commentContent.value.trim()) return

  commentLoading.value = true

  try {
    const response = await createTicketComment(ticket.value.id, commentContent.value)

    comments.value.push(response.data.data)
    commentContent.value = ''
  } finally {
    commentLoading.value = false
  }
}

onMounted(async () => {
  await loadTicket()

  if (auth.user?.role === 'admin' && ticket.value?.status === 'open') {
    await loadTechnicians()
  }
})
</script>

<template>
  <div class="page details-page">
    <RouterLink class="back-link" to="/tickets">
      <span class="material-symbols-outlined">arrow_back</span>
      Voltar para chamados
    </RouterLink>

    <p v-if="loading" class="loading-state">Carregando chamado...</p>
    <p v-else-if="error" class="error-message">{{ error }}</p>

    <template v-else-if="ticket">
      <section class="ticket-hero card" :class="`priority-${ticket.priority}`">
        <div class="hero-topline">
          <div class="ticket-badges">
            <span class="ticket-id">#{{ ticket.id }}</span>
            <span class="status-badge" :class="`status-${ticket.status}`">{{
              statusLabel(ticket.status)
            }}</span>
            <span class="priority-badge" :class="`priority-${ticket.priority}`">{{
              priorityLabel(ticket.priority)
            }}</span>
          </div>
          <span class="ticket-category"
            ><span class="material-symbols-outlined">category</span>{{ ticket.category.name }}</span
          >
        </div>

        <h1>{{ ticket.title }}</h1>

        <div class="hero-meta">
          <span
            ><i>{{ ticket.requester.name.slice(0, 2).toUpperCase() }}</i
            >{{ ticket.requester.name }}</span
          >
          <span
            ><span class="material-symbols-outlined">calendar_today</span
            >{{ formatDate(ticket.created_at) }}</span
          >
          <span
            ><span class="material-symbols-outlined">engineering</span
            >{{ ticket.technician?.name ?? 'Sem técnico atribuído' }}</span
          >
        </div>

        <div class="hero-actions">
          <button
            v-if="auth.user?.role === 'technician' && ticket.status === 'open'"
            class="primary-button"
            :disabled="actionLoading"
            @click="handleAssign"
          >
            <span class="material-symbols-outlined">assignment_ind</span>
            {{ actionLoading ? 'Assumindo...' : 'Assumir chamado' }}
          </button>
          <button
            v-if="
              auth.user?.role === 'requester' &&
              ticket.status === 'resolved' &&
              ticket.requester.id === auth.user.id
            "
            class="primary-button"
            :disabled="actionLoading"
            @click="handleClose"
          >
            <span class="material-symbols-outlined">task_alt</span>
            {{ actionLoading ? 'Fechando...' : 'Confirmar e fechar chamado' }}
          </button>
        </div>
      </section>

      <div class="details-grid">
        <main class="details-content">
          <section v-if="ticket.solution" class="solution-card card">
            <h2><span class="material-symbols-outlined">verified</span>Solução apresentada</h2>
            <p>{{ ticket.solution }}</p>
            <small>Resolvido em {{ formatDate(ticket.resolved_at) }}</small>
          </section>

          <section class="description-card card">
            <h2>Descrição</h2>
            <p>{{ ticket.description }}</p>
          </section>

          <section class="activity-card card">
            <h2>Atividade</h2>
            <div class="timeline">
              <div class="timeline-item system">
                <span></span>
                <div>
                  <strong>Chamado aberto</strong>
                  <p>Solicitação criada por {{ ticket.requester.name }}.</p>
                </div>
                <time>{{ formatDate(ticket.created_at) }}</time>
              </div>
              <div v-if="ticket.technician" class="timeline-item">
                <span></span>
                <div>
                  <strong>Técnico atribuído</strong>
                  <p>{{ ticket.technician.name }} assumiu o atendimento.</p>
                </div>
              </div>
              <div v-for="comment in comments" :key="comment.id" class="timeline-item">
                <span></span>
                <div>
                  <strong>{{ comment.user.name }}</strong>
                  <p>{{ comment.content }}</p>
                </div>
                <time>{{ formatDate(comment.created_at) }}</time>
              </div>
            </div>

            <p v-if="comments.length === 0" class="muted-message">
              Ainda não há comentários neste chamado.
            </p>

            <form
              v-if="ticket.status !== 'closed'"
              class="comment-form"
              @submit.prevent="handleComment"
            >
              <label class="field-label" for="comment">Adicionar comentário</label>
              <textarea
                id="comment"
                v-model="commentContent"
                class="field-control"
                placeholder="Descreva sua análise ou uma atualização..."
                required
              />
              <div class="comment-action">
                <button
                  class="primary-button"
                  type="submit"
                  :disabled="commentLoading || !commentContent.trim()"
                >
                  <span class="material-symbols-outlined">send</span>
                  {{ commentLoading ? 'Enviando...' : 'Comentar' }}
                </button>
              </div>
            </form>
          </section>
        </main>

        <aside class="details-sidebar">
          <section class="summary-card card">
            <h2>Detalhes do chamado</h2>
            <dl>
              <div>
                <dt>Categoria</dt>
                <dd>{{ ticket.category.name }}</dd>
              </div>
              <div>
                <dt>Prioridade</dt>
                <dd>{{ priorityLabel(ticket.priority) }}</dd>
              </div>
              <div>
                <dt>Status</dt>
                <dd>{{ statusLabel(ticket.status) }}</dd>
              </div>
              <div>
                <dt>Técnico</dt>
                <dd>{{ ticket.technician?.name ?? 'Não atribuído' }}</dd>
              </div>
              <div v-if="ticket.closed_at">
                <dt>Fechado em</dt>
                <dd>{{ formatDate(ticket.closed_at) }}</dd>
              </div>
            </dl>
          </section>

          <section
            v-if="auth.user?.role === 'admin' && ticket.status === 'open'"
            class="assignment-card card"
          >
            <h2>Atribuir técnico</h2>
            <p>Selecione um técnico disponível para iniciar o atendimento.</p>
            <label class="field-label" for="technician">Técnico responsável</label>
            <select id="technician" v-model="selectedTechnicianId" class="field-control">
              <option :value="null" disabled>Selecione um técnico</option>
              <option v-for="technician in technicians" :key="technician.id" :value="technician.id">
                {{ technician.name }}
              </option>
            </select>
            <button
              class="primary-button"
              :disabled="actionLoading || !selectedTechnicianId"
              @click="handleAdminAssign"
            >
              {{ actionLoading ? 'Atribuindo...' : 'Atribuir técnico' }}
            </button>
          </section>

          <section
            v-if="
              auth.user?.role === 'technician' &&
              ticket.status === 'in_progress' &&
              ticket.technician?.id === auth.user.id
            "
            class="resolve-card card"
          >
            <h2>Registrar solução</h2>
            <label class="field-label" for="solution">Solução aplicada</label>
            <textarea
              id="solution"
              v-model="solution"
              class="field-control"
              placeholder="Descreva a solução aplicada."
              required
            />
            <button
              class="primary-button"
              :disabled="actionLoading || !solution.trim()"
              @click="handleResolve"
            >
              {{ actionLoading ? 'Resolvendo...' : 'Marcar como resolvido' }}
            </button>
          </section>

          <p v-if="actionError" class="error-message">{{ actionError }}</p>
        </aside>
      </div>
    </template>
  </div>
</template>

<style scoped>
.details-page {
  max-width: 1180px;
}
.back-link {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 20px;
  color: var(--text-muted);
  font-size: 13px;
  font-weight: 600;
}
.back-link:hover {
  color: var(--primary);
}
.back-link .material-symbols-outlined {
  font-size: 19px;
}
.ticket-hero {
  position: relative;
  overflow: hidden;
  padding: 28px 32px;
}
.ticket-hero::before {
  position: absolute;
  inset: 0 auto 0 0;
  width: 5px;
  background: var(--primary);
  content: '';
}
.ticket-hero.priority-medium::before {
  background: var(--warning);
}
.ticket-hero.priority-high::before {
  background: var(--danger);
}
.hero-topline,
.hero-meta,
.ticket-badges {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 8px;
}
.hero-topline {
  justify-content: space-between;
}
.ticket-id {
  margin-right: 4px;
  color: var(--text-muted);
  font-size: 14px;
  font-weight: 700;
  letter-spacing: 0.06em;
}
.status-badge,
.priority-badge {
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
.ticket-category {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: var(--text-muted);
  font-size: 13px;
}
.ticket-category .material-symbols-outlined {
  font-size: 18px;
}
.ticket-hero h1 {
  max-width: 850px;
  margin: 20px 0;
  font-size: 30px;
  line-height: 38px;
  letter-spacing: -0.02em;
}
.hero-meta {
  color: var(--text-muted);
  font-size: 13px;
}
.hero-meta > span {
  display: inline-flex;
  align-items: center;
  gap: 7px;
}
.hero-meta i {
  display: grid;
  width: 28px;
  height: 28px;
  place-items: center;
  border-radius: 50%;
  background: var(--primary);
  color: #fff;
  font-size: 10px;
  font-style: normal;
  font-weight: 700;
}
.hero-meta .material-symbols-outlined {
  font-size: 18px;
}
.hero-actions {
  margin-top: 22px;
}
.details-grid {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 300px;
  gap: 24px;
  margin-top: 24px;
  align-items: start;
}
.details-content,
.details-sidebar {
  display: grid;
  gap: 20px;
}
.description-card,
.activity-card,
.solution-card,
.summary-card,
.assignment-card,
.resolve-card {
  padding: 24px;
}
.description-card h2,
.activity-card h2,
.solution-card h2,
.summary-card h2,
.assignment-card h2,
.resolve-card h2 {
  margin: 0;
  font-size: 20px;
}
.description-card > p,
.solution-card > p {
  margin: 16px 0 0;
  line-height: 24px;
  white-space: pre-wrap;
}
.solution-card {
  border-color: #9ce8c4;
  background: var(--success-soft);
}
.solution-card h2 {
  display: flex;
  align-items: center;
  gap: 8px;
  color: var(--success);
}
.solution-card small {
  display: block;
  margin-top: 16px;
  color: var(--success);
}
.timeline {
  display: grid;
  gap: 18px;
  margin-top: 20px;
}
.timeline-item {
  position: relative;
  display: grid;
  grid-template-columns: 12px minmax(0, 1fr) auto;
  gap: 10px;
}
.timeline-item > span {
  width: 9px;
  height: 9px;
  margin-top: 5px;
  border-radius: 50%;
  background: var(--primary);
}
.timeline-item.system > span {
  background: var(--closed);
}
.timeline-item:not(:last-child)::before {
  position: absolute;
  top: 17px;
  bottom: -20px;
  left: 4px;
  width: 1px;
  background: var(--outline);
  content: '';
}
.timeline-item strong {
  font-size: 13px;
}
.timeline-item p {
  margin: 4px 0 0;
  color: var(--text-muted);
  font-size: 13px;
  line-height: 19px;
  white-space: pre-wrap;
}
.timeline-item time {
  color: var(--text-muted);
  font-size: 11px;
}
.muted-message {
  color: var(--text-muted);
  font-size: 13px;
}
.comment-form {
  margin-top: 24px;
  padding-top: 20px;
  border-top: 1px solid var(--outline);
}
.comment-action {
  display: flex;
  justify-content: flex-end;
  margin-top: 12px;
}
.summary-card dl {
  display: grid;
  gap: 12px;
  margin: 20px 0 0;
}
.summary-card dl div {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  padding-bottom: 10px;
  border-bottom: 1px solid var(--outline);
  font-size: 12px;
}
.summary-card dl div:last-child {
  border-bottom: 0;
  padding-bottom: 0;
}
.summary-card dt {
  color: var(--text-muted);
}
.summary-card dd {
  margin: 0;
  text-align: right;
  font-weight: 600;
}
.assignment-card,
.resolve-card {
  display: grid;
  gap: 12px;
}
.assignment-card {
  background: var(--surface-low);
}
.assignment-card p {
  margin: 0;
  color: var(--text-muted);
  font-size: 13px;
  line-height: 19px;
}
@media (max-width: 900px) {
  .details-grid {
    grid-template-columns: 1fr;
  }
  .details-sidebar {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
  .summary-card {
    grid-row: span 2;
  }
}
@media (max-width: 767px) {
  .ticket-hero {
    padding: 22px 20px 22px 24px;
  }
  .ticket-hero h1 {
    font-size: 25px;
    line-height: 32px;
  }
  .hero-topline {
    align-items: flex-start;
    gap: 12px;
  }
  .ticket-category {
    width: 100%;
  }
  .details-grid {
    margin-top: 16px;
  }
  .details-sidebar {
    grid-template-columns: 1fr;
  }
  .timeline-item {
    grid-template-columns: 12px minmax(0, 1fr);
  }
  .timeline-item time {
    grid-column: 2;
  }
  .description-card,
  .activity-card,
  .solution-card,
  .summary-card,
  .assignment-card,
  .resolve-card {
    padding: 20px;
  }
}
</style>
