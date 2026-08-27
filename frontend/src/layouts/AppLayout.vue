<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const router = useRouter()

async function handleLogout() {
    await auth.logout()
    await router.push('/login')
}
</script>

<template>
    <div class="app-layout">
        <aside>
            <h2>Chamados</h2>

            <nav>
                <RouterLink to="/">Início</RouterLink>
                <RouterLink to="/tickets">Chamados</RouterLink>

                <RouterLink v-if="auth.user?.role === 'admin'" to="/categories">
                    Categorias
                </RouterLink>
            </nav>

            <button @click="handleLogout">
                Sair
            </button>
        </aside>

        <main>
            <RouterView />
        </main>
    </div>
</template>