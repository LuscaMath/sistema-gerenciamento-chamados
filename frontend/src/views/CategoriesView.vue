<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import {
  activateCategory,
  createCategory,
  deactivateCategory,
  getCategories,
  updateCategory,
} from '@/api/categories'
import type { Category } from '@/types/category'

const categories = ref<Category[]>([])
const loading = ref(true)
const error = ref('')
const formError = ref('')
const formLoading = ref(false)
const actionLoadingId = ref<number | null>(null)
const editingCategoryId = ref<number | null>(null)
const formOpen = ref(false)
const name = ref('')
const description = ref('')

const isEditing = computed(() => editingCategoryId.value !== null)

async function loadCategories() {
  loading.value = true
  error.value = ''

  try {
    const response = await getCategories()
    categories.value = response.data.data
  } catch {
    error.value = 'Não foi possível carregar as categorias.'
  } finally {
    loading.value = false
  }
}

function resetForm() {
  editingCategoryId.value = null
  name.value = ''
  description.value = ''
  formError.value = ''
  formOpen.value = false
}

function openCreateForm() {
  editingCategoryId.value = null
  name.value = ''
  description.value = ''
  formError.value = ''
  formOpen.value = true
}

function editCategory(category: Category) {
  editingCategoryId.value = category.id
  name.value = category.name
  description.value = category.description ?? ''
  formError.value = ''
  formOpen.value = true
}

async function submitForm() {
  const categoryName = name.value.trim()

  if (!categoryName) {
    formError.value = 'Informe o nome da categoria.'
    return
  }

  formLoading.value = true
  formError.value = ''

  const payload = {
    name: categoryName,
    description: description.value.trim() || null,
  }

  try {
    if (editingCategoryId.value) {
      await updateCategory(editingCategoryId.value, payload)
    } else {
      await createCategory(payload)
    }

    resetForm()
    await loadCategories()
  } catch {
    formError.value = 'Não foi possível salvar a categoria.'
  } finally {
    formLoading.value = false
  }
}

async function toggleCategory(category: Category) {
  actionLoadingId.value = category.id
  error.value = ''

  try {
    if (category.is_active) {
      await deactivateCategory(category.id)
    } else {
      await activateCategory(category.id)
    }

    await loadCategories()
  } catch {
    error.value = 'Não foi possível atualizar o status da categoria.'
  } finally {
    actionLoadingId.value = null
  }
}

onMounted(loadCategories)
</script>

<template>
  <div class="page">
    <div class="page-heading categories-heading">
      <div>
        <h1>Gerenciar categorias</h1>
        <p>Organize as categorias de suporte disponíveis para abertura de chamados.</p>
      </div>
      <button id="new-category" class="primary-button" @click="openCreateForm">
        <span class="material-symbols-outlined">add</span>
        Nova categoria
      </button>
    </div>

    <p v-if="loading" class="loading-state">Carregando categorias...</p>
    <p v-else-if="error" class="error-message">{{ error }}</p>
    <p v-else-if="categories.length === 0" class="empty-state">Nenhuma categoria encontrada.</p>

    <div v-else class="category-grid">
      <article
        v-for="category in categories"
        :key="category.id"
        class="category-card card"
        :class="{ inactive: !category.is_active }"
      >
        <div class="category-header">
          <h2>{{ category.name }}</h2>
          <span class="category-status" :class="{ inactive: !category.is_active }">
            <i></i>{{ category.is_active ? 'Ativa' : 'Inativa' }}
          </span>
        </div>
        <p>{{ category.description ?? 'Sem descrição cadastrada.' }}</p>
        <div class="category-actions">
          <button
            class="icon-button"
            type="button"
            aria-label="Editar categoria"
            :disabled="actionLoadingId === category.id"
            @click="editCategory(category)"
          >
            <span class="material-symbols-outlined">edit</span>
          </button>
          <button
            class="icon-button"
            type="button"
            :aria-label="category.is_active ? 'Desativar categoria' : 'Ativar categoria'"
            :disabled="actionLoadingId === category.id"
            @click="toggleCategory(category)"
          >
            <span class="material-symbols-outlined">{{
              category.is_active ? 'block' : 'check_circle'
            }}</span>
          </button>
        </div>
      </article>
    </div>

    <div v-if="formOpen" class="modal-backdrop" @click.self="resetForm">
      <section
        class="category-modal"
        role="dialog"
        aria-modal="true"
        :aria-label="isEditing ? 'Editar categoria' : 'Nova categoria'"
      >
        <div class="modal-header">
          <h2>{{ isEditing ? 'Editar categoria' : 'Nova categoria' }}</h2>
          <button
            class="icon-button"
            type="button"
            aria-label="Fechar formulário"
            :disabled="formLoading"
            @click="resetForm"
          >
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>

        <form @submit.prevent="submitForm">
          <div>
            <label class="field-label" for="category-name">Nome da categoria *</label>
            <input
              id="category-name"
              v-model="name"
              class="field-control"
              placeholder="Ex.: Softwares de gestão"
              required
              type="text"
            />
          </div>
          <div>
            <label class="field-label" for="category-description">Descrição</label>
            <textarea
              id="category-description"
              v-model="description"
              class="field-control"
              placeholder="Breve descrição sobre os tipos de chamados desta categoria."
            />
          </div>

          <p v-if="formError" class="error-message">{{ formError }}</p>

          <div class="modal-actions">
            <button
              class="secondary-button"
              type="button"
              :disabled="formLoading"
              @click="resetForm"
            >
              Cancelar
            </button>
            <button class="primary-button" type="submit" :disabled="formLoading">
              {{ formLoading ? 'Salvando...' : 'Salvar categoria' }}
            </button>
          </div>
        </form>
      </section>
    </div>
  </div>
</template>

<style scoped>
.categories-heading {
  align-items: center;
}
.category-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 20px;
}
.category-card {
  position: relative;
  overflow: hidden;
  padding: 24px;
}
.category-card::before {
  position: absolute;
  inset: 0 auto 0 0;
  width: 4px;
  background: var(--primary);
  content: '';
}
.category-card.inactive {
  background: var(--surface-low);
  color: var(--text-muted);
}
.category-card.inactive::before {
  background: var(--closed);
}
.category-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
}
.category-header h2 {
  margin: 0;
  font-size: 22px;
}
.category-status {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 6px 10px;
  border-radius: 999px;
  background: var(--success-soft);
  color: var(--success);
  font-size: 11px;
  font-weight: 700;
}
.category-status.inactive {
  background: var(--closed-soft);
  color: var(--closed);
}
.category-status i {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: currentcolor;
}
.category-card > p {
  min-height: 48px;
  margin: 16px 0 20px;
  color: var(--text-muted);
  line-height: 22px;
}
.category-actions {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  padding-top: 14px;
  border-top: 1px solid var(--outline);
}
.icon-button {
  display: inline-grid;
  width: 38px;
  height: 38px;
  place-items: center;
  border: 1px solid transparent;
  border-radius: 8px;
  background: transparent;
  color: var(--text-muted);
}
.icon-button:hover:not(:disabled) {
  border-color: var(--outline);
  background: var(--surface-high);
  color: var(--primary);
}
.modal-backdrop {
  position: fixed;
  inset: 0;
  z-index: 30;
  display: grid;
  place-items: center;
  padding: 16px;
  background: rgb(25 28 29 / 45%);
}
.category-modal {
  width: min(100%, 560px);
  overflow: hidden;
  border: 1px solid var(--outline);
  border-radius: 16px;
  background: #fff;
  box-shadow: 0 20px 60px rgb(0 0 0 / 18%);
}
.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 24px;
  border-bottom: 1px solid var(--outline);
}
.modal-header h2 {
  margin: 0;
  font-size: 24px;
}
.category-modal form {
  display: grid;
  gap: 20px;
  padding: 24px;
}
.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  padding-top: 16px;
  border-top: 1px solid var(--outline);
}
@media (max-width: 767px) {
  .category-grid {
    grid-template-columns: 1fr;
  }
  .categories-heading .primary-button {
    width: 100%;
    margin-top: 16px;
  }
  .modal-actions {
    flex-direction: column-reverse;
  }
  .modal-actions > * {
    width: 100%;
  }
}
</style>
