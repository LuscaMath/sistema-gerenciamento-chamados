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
        },
        {
          path: 'tickets',
          name: 'tickets',
          component: () => import('@/views/TicketsView.vue'),
        },
        {
          path: 'categories',
          name: 'categories',
          component: () => import('@/views/CategoriesView.vue'),
        },
        {
          path: 'tickets/create',
          name: 'ticket-create',
          component: () => import('@/views/CreateTicketView.vue'),
          meta: {
            role: 'requester',
          },
        },
        {
          path: 'tickets/:id',
          name: 'ticket-details',
          component: () => import('@/views/TicketDetailsView.vue'),
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