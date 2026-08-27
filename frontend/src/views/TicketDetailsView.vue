<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { getTicket } from '@/api/tickets'
import { useAuthStore } from '@/stores/auth'
import { assignTicket } from '@/api/tickets'
import { resolveTicket } from '@/api/tickets'
import { closeTicket } from '@/api/tickets'
import type { Ticket } from '@/types/ticket'
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

async function handleResolve() {
    if (!ticket.value || !solution.value.trim()) return

    actionLoading.value = true
    actionError.value = ''

    try {
        const response = await resolveTicket(
            ticket.value.id,
            {
                solution: solution.value,
            },
        )

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
        const response = await createTicketComment(
            ticket.value.id,
            commentContent.value,
        )

        comments.value.push(response.data.data)
        commentContent.value = ''
    } finally {
        commentLoading.value = false
    }
}

onMounted(loadTicket)
</script>

<template>
    <section>
        <p v-if="loading">
            Carregando...
        </p>

        <p v-else-if="error">
            {{ error }}
        </p>

        <div v-else-if="ticket">
            <h1>#{{ ticket.id }} - {{ ticket.title }}</h1>

            <p>
                <strong>Status:</strong>
                {{ ticket.status }}
            </p>

            <p>
                <strong>Prioridade:</strong>
                {{ ticket.priority }}
            </p>

            <p>
                <strong>Categoria:</strong>
                {{ ticket.category.name }}
            </p>

            <p>
                <strong>Solicitante:</strong>
                {{ ticket.requester.name }}
            </p>

            <p>
                <strong>Técnico:</strong>
                {{ ticket.technician?.name ?? 'Não atribuído' }}
            </p>

            <p>
                <strong>Descrição:</strong>
                {{ ticket.description }}
            </p>

            <div v-if="ticket.solution">
                <strong>Solução:</strong>
                <p>{{ ticket.solution }}</p>
            </div>
            <button v-if="
                auth.user?.role === 'technician' &&
                ticket.status === 'open'
            " :disabled="actionLoading" @click="handleAssign">
                {{ actionLoading ? 'Assumindo...' : 'Assumir chamado' }}
            </button>
            <button v-if="
                auth.user?.role === 'requester' &&
                ticket.status === 'resolved' &&
                ticket.requester.id === auth.user.id
            " :disabled="actionLoading" @click="handleClose">
                {{ actionLoading ? 'Fechando...' : 'Fechar chamado' }}
            </button>
            <div v-if="
                auth.user?.role === 'technician' &&
                ticket.status === 'in_progress' &&
                ticket.technician?.id === auth.user.id
            ">
                <label for="solution">Solução</label>

                <textarea id="solution" v-model="solution" required></textarea>

                <button :disabled="actionLoading || !solution.trim()" @click="handleResolve">
                    {{ actionLoading ? 'Resolvendo...' : 'Resolver chamado' }}
                </button>
            </div>

            <p v-if="actionError">
                {{ actionError }}
            </p>
        </div>
    </section>
    <section v-if="ticket">
        <h2>Comentários</h2>

        <p v-if="comments.length === 0">
            Nenhum comentário.
        </p>

        <div v-for="comment in comments" :key="comment.id">
            <strong>{{ comment.user.name }}</strong>
            <p>{{ comment.content }}</p>
        </div>

        <form v-if="ticket.status !== 'closed'" @submit.prevent="handleComment">
            <textarea v-model="commentContent" placeholder="Adicionar comentário..." required />

            <button type="submit" :disabled="commentLoading || !commentContent.trim()">
                {{ commentLoading ? 'Enviando...' : 'Comentar' }}
            </button>
        </form>
    </section>
</template>