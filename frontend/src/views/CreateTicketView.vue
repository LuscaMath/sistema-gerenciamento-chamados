<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { getCategories } from '@/api/categories'
import { createTicket } from '@/api/tickets'
import type { Category } from '@/types/category'
import type { TicketPriority } from '@/types/ticket'

const router = useRouter()
const categories = ref<Category[]>([])
const categoryId = ref<number | null>(null)
const title = ref('')
const description = ref('')
const priority = ref<TicketPriority>('medium')
const loading = ref(false)
const error = ref('')

async function loadCategories() {
  try {
    const response = await getCategories()
    categories.value = response.data.data.filter((category) => category.is_active)
  } catch {
    error.value = 'Não foi possível carregar as categorias disponíveis.'
  }
}

async function handleSubmit() {
  if (!categoryId.value) {
    error.value = 'Selecione uma categoria.'
    return
  }

  loading.value = true
  error.value = ''

  try {
    await createTicket({
      category_id: categoryId.value,
      title: title.value,
      description: description.value,
      priority: priority.value,
    })

    await router.push('/tickets')
  } catch {
    error.value = 'Não foi possível criar o chamado.'
  } finally {
    loading.value = false
  }
}

onMounted(loadCategories)
</script>

<template>
  <div class="page">
    <div class="page-heading">
      <div>
        <h1>Abertura de chamado</h1>
        <p>
          Forneça os detalhes necessários para que nossa equipe possa ajudar a resolver seu
          problema.
        </p>
      </div>
    </div>

    <div class="create-grid">
      <form class="ticket-form card" @submit.prevent="handleSubmit">
        <div class="form-heading">
          <span class="material-symbols-outlined">edit_note</span>
          <div>
            <h2>Detalhes da solicitação</h2>
            <p>Os campos marcados com * são obrigatórios.</p>
          </div>
        </div>

        <div>
          <label class="field-label" for="title">Título do chamado *</label>
          <input
            id="title"
            v-model="title"
            class="field-control"
            placeholder="Ex.: Erro ao acessar o sistema ERP"
            required
            type="text"
          />
        </div>

        <div class="form-row">
          <div>
            <label class="field-label" for="category">Categoria *</label>
            <select id="category" v-model="categoryId" class="field-control" required>
              <option :value="null" disabled>Selecione uma categoria</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
          </div>
          <div>
            <label class="field-label" for="priority">Prioridade estimada</label>
            <select id="priority" v-model="priority" class="field-control">
              <option value="low">Baixa — não afeta o trabalho principal</option>
              <option value="medium">Média — dificulta as atividades</option>
              <option value="high">Alta — trabalho paralisado</option>
            </select>
          </div>
        </div>

        <div>
          <label class="field-label" for="description">Descrição detalhada *</label>
          <textarea
            id="description"
            v-model="description"
            class="field-control"
            placeholder="Descreva o problema, o contexto e eventuais mensagens exibidas."
            required
          />
          <p class="field-hint">Quanto mais detalhes, mais rápido será o atendimento.</p>
        </div>

        <p v-if="error" class="error-message">{{ error }}</p>

        <div class="form-actions">
          <button class="primary-button" type="submit" :disabled="loading">
            <span class="material-symbols-outlined">send</span>
            {{ loading ? 'Criando...' : 'Criar chamado' }}
          </button>
          <RouterLink class="text-button" to="/tickets">Cancelar</RouterLink>
        </div>
      </form>

      <aside class="tips-column">
        <section class="tips-card card">
          <h2><span class="material-symbols-outlined">lightbulb</span>Dicas úteis</h2>
          <ul>
            <li>
              <span class="material-symbols-outlined">check_circle</span
              ><span><strong>Seja específico:</strong> inclua mensagens de erro exatas.</span>
            </li>
            <li>
              <span class="material-symbols-outlined">check_circle</span
              ><span><strong>Contexto:</strong> informe o que estava tentando fazer.</span>
            </li>
            <li>
              <span class="material-symbols-outlined">check_circle</span
              ><span><strong>Impacto:</strong> explique quem ou o que foi afetado.</span>
            </li>
          </ul>
        </section>

        <section class="sla-card card">
          <h2><span class="material-symbols-outlined">timer</span>Tempo de resposta</h2>
          <p>O tempo inicial de atendimento varia de acordo com a prioridade selecionada.</p>
          <dl>
            <div>
              <dt><i class="high"></i>Alta</dt>
              <dd>Até 4 horas</dd>
            </div>
            <div>
              <dt><i class="medium"></i>Média</dt>
              <dd>Até 24 horas</dd>
            </div>
            <div>
              <dt><i class="low"></i>Baixa</dt>
              <dd>Até 48 horas</dd>
            </div>
          </dl>
        </section>
      </aside>
    </div>
  </div>
</template>

<style scoped>
.create-grid {
  display: grid;
  grid-template-columns: minmax(0, 2fr) minmax(260px, 0.85fr);
  gap: 24px;
  align-items: start;
}
.ticket-form {
  display: grid;
  gap: 24px;
  padding: 32px;
}
.form-heading {
  display: flex;
  align-items: center;
  gap: 12px;
  padding-bottom: 20px;
  border-bottom: 1px solid var(--outline);
}
.form-heading > .material-symbols-outlined {
  padding: 10px;
  border-radius: 10px;
  background: var(--primary-soft);
  color: var(--primary);
}
.form-heading h2,
.tips-card h2,
.sla-card h2 {
  margin: 0;
  font-size: 20px;
}
.form-heading p {
  margin: 4px 0 0;
  color: var(--text-muted);
  font-size: 13px;
}
.form-row {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
}
.field-hint {
  margin: 8px 0 0;
  color: var(--text-muted);
  font-size: 12px;
}
.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  padding-top: 20px;
  border-top: 1px solid var(--outline);
}
.tips-column {
  display: grid;
  gap: 16px;
}
.tips-card,
.sla-card {
  padding: 24px;
}
.tips-card h2,
.sla-card h2 {
  display: flex;
  align-items: center;
  gap: 8px;
}
.tips-card h2 .material-symbols-outlined {
  color: var(--warning);
}
.sla-card h2 .material-symbols-outlined {
  color: var(--primary);
}
.tips-card ul {
  display: grid;
  gap: 14px;
  margin: 20px 0 0;
  padding: 0;
  list-style: none;
  color: var(--text-muted);
  font-size: 14px;
  line-height: 20px;
}
.tips-card li {
  display: flex;
  gap: 8px;
}
.tips-card li .material-symbols-outlined {
  flex: 0 0 auto;
  color: var(--primary);
  font-size: 18px;
}
.tips-card strong {
  color: var(--text);
}
.sla-card {
  background: var(--surface-low);
}
.sla-card > p {
  color: var(--text-muted);
  font-size: 14px;
  line-height: 20px;
}
.sla-card dl {
  display: grid;
  gap: 10px;
  margin: 20px 0 0;
  font-size: 13px;
}
.sla-card dl div,
.sla-card dt {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}
.sla-card dt {
  justify-content: flex-start;
}
.sla-card dd {
  margin: 0;
  font-weight: 700;
}
.sla-card i {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--primary);
}
.sla-card i.high {
  background: var(--danger);
}
.sla-card i.medium {
  background: var(--warning);
}
.sla-card i.low {
  background: var(--primary);
}
@media (max-width: 900px) {
  .create-grid {
    grid-template-columns: 1fr;
  }
}
@media (max-width: 767px) {
  .ticket-form {
    padding: 24px 20px;
  }
  .form-row {
    grid-template-columns: 1fr;
  }
  .form-actions {
    flex-direction: column;
  }
  .form-actions > * {
    width: 100%;
  }
}
</style>
