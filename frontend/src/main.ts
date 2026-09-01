import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'
import { useAuthStore } from '@/stores/auth'
import './styles.css'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)

const auth = useAuthStore()

async function bootstrap() {
  await auth.fetchUser()

  app.use(router)
  await router.isReady()

  app.mount('#app')
}

bootstrap()
