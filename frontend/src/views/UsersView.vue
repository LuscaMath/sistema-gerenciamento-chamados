<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { createUser, getUsers, updateUser } from '@/api/users'
import type { User, UserRole } from '@/types/user'

const users = ref<User[]>([])
const loading = ref(true)
const error = ref('')
const formError = ref('')
const formLoading = ref(false)
const formOpen = ref(false)
const editingUserId = ref<number | null>(null)
const name = ref('')
const email = ref('')
const password = ref('')
const role = ref<UserRole>('requester')

const isEditing = computed(() => editingUserId.value !== null)

function roleLabel(userRole: UserRole) {
  return {
    requester: 'Solicitante',
    technician: 'Técnico',
    admin: 'Administrador',
  }[userRole]
}

function initials(userName: string) {
  return userName
    .split(' ')
    .map((part) => part[0])
    .join('')
    .slice(0, 2)
    .toUpperCase()
}

async function loadUsers() {
  loading.value = true
  error.value = ''

  try {
    const response = await getUsers()
    users.value = response.data.data
  } catch {
    error.value = 'Não foi possível carregar os usuários.'
  } finally {
    loading.value = false
  }
}

function closeForm() {
  formOpen.value = false
  editingUserId.value = null
  name.value = ''
  email.value = ''
  password.value = ''
  role.value = 'requester'
  formError.value = ''
}

function openCreateForm() {
  closeForm()
  formOpen.value = true
}

function editUser(user: User) {
  editingUserId.value = user.id
  name.value = user.name
  email.value = user.email
  password.value = ''
  role.value = user.role
  formError.value = ''
  formOpen.value = true
}

async function submitForm() {
  const userName = name.value.trim()
  const userEmail = email.value.trim()

  if (!userName || !userEmail) {
    formError.value = 'Informe nome e e-mail do usuário.'
    return
  }

  if (!isEditing.value && password.value.length < 8) {
    formError.value = 'A senha deve possuir pelo menos 8 caracteres.'
    return
  }

  formLoading.value = true
  formError.value = ''

  const payload = {
    name: userName,
    email: userEmail,
    role: role.value,
  }

  try {
    if (editingUserId.value) {
      await updateUser(editingUserId.value, {
        ...payload,
        ...(password.value ? { password: password.value } : {}),
      })
    } else {
      await createUser({
        ...payload,
        password: password.value,
      })
    }

    closeForm()
    await loadUsers()
  } catch {
    formError.value = 'Não foi possível salvar o usuário.'
  } finally {
    formLoading.value = false
  }
}

onMounted(loadUsers)
</script>

<template>
  <div class="page">
    <div class="page-heading users-heading">
      <div>
        <h1>Gerenciar usuários</h1>
        <p>Cadastre e mantenha os perfis que podem acessar o sistema de chamados.</p>
      </div>
      <button id="new-user" class="primary-button" @click="openCreateForm">
        <span class="material-symbols-outlined">person_add</span>
        Novo usuário
      </button>
    </div>

    <p v-if="loading" class="loading-state">Carregando usuários...</p>
    <p v-else-if="error" class="error-message">{{ error }}</p>
    <p v-else-if="users.length === 0" class="empty-state">Nenhum usuário cadastrado.</p>

    <div v-else class="users-card card">
      <div class="users-table-header">
        <span>Usuário</span>
        <span>Perfil</span>
        <span>Ações</span>
      </div>
      <article v-for="user in users" :key="user.id" class="user-row">
        <div class="user-identity">
          <span class="user-avatar">{{ initials(user.name) }}</span>
          <span
            ><strong>{{ user.name }}</strong
            ><small>{{ user.email }}</small></span
          >
        </div>
        <span class="role-badge" :class="`role-${user.role}`">{{ roleLabel(user.role) }}</span>
        <button
          class="icon-button"
          type="button"
          aria-label="Editar usuário"
          @click="editUser(user)"
        >
          <span class="material-symbols-outlined">edit</span>
        </button>
      </article>
    </div>

    <div v-if="formOpen" class="modal-backdrop" @click.self="closeForm">
      <section
        class="user-modal"
        role="dialog"
        aria-modal="true"
        :aria-label="isEditing ? 'Editar usuário' : 'Novo usuário'"
      >
        <div class="modal-header">
          <div>
            <h2>{{ isEditing ? 'Editar usuário' : 'Novo usuário' }}</h2>
            <p>
              {{
                isEditing
                  ? 'Atualize os dados e o perfil de acesso.'
                  : 'Defina os dados de acesso e o perfil do usuário.'
              }}
            </p>
          </div>
          <button
            class="icon-button"
            type="button"
            aria-label="Fechar formulário"
            :disabled="formLoading"
            @click="closeForm"
          >
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>

        <form @submit.prevent="submitForm">
          <div>
            <label class="field-label" for="user-name">Nome completo *</label>
            <input
              id="user-name"
              v-model="name"
              class="field-control"
              placeholder="Ex.: Ana Souza"
              required
              type="text"
            />
          </div>
          <div>
            <label class="field-label" for="user-email">E-mail *</label>
            <input
              id="user-email"
              v-model="email"
              class="field-control"
              placeholder="ana@empresa.com"
              required
              type="email"
            />
          </div>
          <div>
            <label class="field-label" for="user-role">Perfil *</label>
            <select id="user-role" v-model="role" class="field-control">
              <option value="requester">Solicitante</option>
              <option value="technician">Técnico</option>
              <option value="admin">Administrador</option>
            </select>
          </div>
          <div>
            <label class="field-label" for="user-password">{{
              isEditing ? 'Nova senha' : 'Senha *'
            }}</label>
            <input
              id="user-password"
              v-model="password"
              class="field-control"
              :placeholder="
                isEditing ? 'Deixe em branco para manter a senha atual' : 'Mínimo de 8 caracteres'
              "
              :required="!isEditing"
              minlength="8"
              type="password"
            />
          </div>

          <p v-if="formError" class="error-message">{{ formError }}</p>

          <div class="modal-actions">
            <button
              class="secondary-button"
              type="button"
              :disabled="formLoading"
              @click="closeForm"
            >
              Cancelar
            </button>
            <button class="primary-button" type="submit" :disabled="formLoading">
              {{ formLoading ? 'Salvando...' : 'Salvar usuário' }}
            </button>
          </div>
        </form>
      </section>
    </div>
  </div>
</template>

<style scoped>
.users-heading {
  align-items: center;
}
.users-card {
  overflow: hidden;
}
.users-table-header,
.user-row {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 170px 72px;
  align-items: center;
  gap: 16px;
  padding: 16px 24px;
}
.users-table-header {
  border-bottom: 1px solid var(--outline);
  color: var(--text-muted);
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.06em;
  text-transform: uppercase;
}
.user-row {
  border-bottom: 1px solid var(--outline);
}
.user-row:last-child {
  border-bottom: 0;
}
.user-row:hover {
  background: var(--surface-low);
}
.user-identity {
  display: flex;
  align-items: center;
  gap: 12px;
  min-width: 0;
}
.user-avatar {
  display: grid;
  width: 38px;
  height: 38px;
  flex: 0 0 auto;
  place-items: center;
  border-radius: 50%;
  background: var(--primary-soft);
  color: var(--primary);
  font-size: 12px;
  font-weight: 700;
}
.user-identity strong,
.user-identity small {
  display: block;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.user-identity small {
  margin-top: 3px;
  color: var(--text-muted);
  font-size: 12px;
}
.role-badge {
  display: inline-flex;
  width: fit-content;
  min-height: 26px;
  align-items: center;
  padding: 4px 10px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 700;
}
.role-requester {
  background: var(--primary-soft);
  color: var(--primary);
}
.role-technician {
  background: var(--warning-soft);
  color: var(--warning);
}
.role-admin {
  background: var(--success-soft);
  color: var(--success);
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
.user-modal {
  width: min(100%, 580px);
  overflow: hidden;
  border: 1px solid var(--outline);
  border-radius: 16px;
  background: #fff;
  box-shadow: 0 20px 60px rgb(0 0 0 / 18%);
}
.modal-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  padding: 24px;
  border-bottom: 1px solid var(--outline);
}
.modal-header h2 {
  margin: 0;
  font-size: 24px;
}
.modal-header p {
  margin: 6px 0 0;
  color: var(--text-muted);
  font-size: 13px;
}
.user-modal form {
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
  .users-heading .primary-button {
    width: 100%;
    margin-top: 16px;
  }
  .users-table-header {
    display: none;
  }
  .user-row {
    grid-template-columns: minmax(0, 1fr) auto;
    padding: 16px;
  }
  .role-badge {
    grid-column: 1;
  }
  .user-row .icon-button {
    grid-column: 2;
    grid-row: 1 / span 2;
  }
  .modal-actions {
    flex-direction: column-reverse;
  }
  .modal-actions > * {
    width: 100%;
  }
}
</style>
