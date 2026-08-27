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
    const response = await getCategories()

    categories.value = response.data.data.filter(
        (category) => category.is_active,
    )
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
    <section>
        <h1>Novo chamado</h1>

        <form @submit.prevent="handleSubmit">
            <div>
                <label for="category">Categoria</label>

                <select id="category" v-model="categoryId" required>
                    <option :value="null" disabled>
                        Selecione
                    </option>

                    <option v-for="category in categories" :key="category.id" :value="category.id">
                        {{ category.name }}
                    </option>
                </select>
            </div>

            <div>
                <label for="title">Título</label>
                <input id="title" v-model="title" type="text" required />
            </div>

            <div>
                <label for="description">Descrição</label>
                <textarea id="description" v-model="description" required />
            </div>

            <div>
                <label for="priority">Prioridade</label>

                <select id="priority" v-model="priority">
                    <option value="low">Baixa</option>
                    <option value="medium">Média</option>
                    <option value="high">Alta</option>
                </select>
            </div>

            <p v-if="error">
                {{ error }}
            </p>

            <button type="submit" :disabled="loading">
                {{ loading ? 'Salvando...' : 'Criar chamado' }}
            </button>
        </form>
    </section>
</template>