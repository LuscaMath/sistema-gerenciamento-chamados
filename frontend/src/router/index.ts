import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '@/views/LoginView.vue'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),

  routes: [
    {
      path: '/login',
      name: 'login',
      component: LoginView,
      meta: {
        title: 'Entrar',
      },
    },

    {
      path: '/',
      component: () => import('@/layouts/AppLayout.vue'),
      meta: {
        requiresAuth: true,
      },
      children: [
        {
          path: '',
          name: 'home',
          component: () => import('@/views/HomeView.vue'),
          meta: {
            title: 'Início',
          },
        },
        {
          path: 'tickets',
          name: 'tickets',
          component: () => import('@/views/TicketsView.vue'),
          meta: {
            title: 'Chamados',
          },
        },
        {
          path: 'categories',
          name: 'categories',
          component: () => import('@/views/CategoriesView.vue'),
          meta: {
            role: 'admin',
            title: 'Categorias',
          },
        },
        {
          path: 'users',
          name: 'users',
          component: () => import('@/views/UsersView.vue'),
          meta: {
            role: 'admin',
            title: 'Usuários',
          },
        },
        {
          path: 'tickets/create',
          name: 'ticket-create',
          component: () => import('@/views/CreateTicketView.vue'),
          meta: {
            role: 'requester',
            title: 'Novo chamado',
          },
        },
        {
          path: 'tickets/:id',
          name: 'ticket-details',
          component: () => import('@/views/TicketDetailsView.vue'),
          meta: {
            title: 'Detalhes do chamado',
          },
        },
      ],
    },
  ],
})

router.beforeEach((to) => {
  const auth = useAuthStore()

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return {
      name: 'login',
    }
  }

  if (to.name === 'login' && auth.isAuthenticated) {
    return {
      name: 'home',
    }
  }

  if (to.meta.role && auth.user?.role !== to.meta.role) {
    return {
      name: 'home',
    }
  }
})

export default router
