import { beforeEach, describe, expect, it, vi } from 'vitest'
import { flushPromises, mount } from '@vue/test-utils'
import CreateTicketView from '@/views/CreateTicketView.vue'
import { getCategories } from '@/api/categories'
import { createTicket } from '@/api/tickets'

const push = vi.fn<(to: string) => void>()

vi.mock('vue-router', () => ({
  useRouter: () => ({ push }),
}))

vi.mock('@/api/categories', () => ({
  getCategories: vi.fn<() => void>(),
}))

vi.mock('@/api/tickets', () => ({
  createTicket: vi.fn<() => void>(),
}))

const categories = [
  {
    id: 1,
    name: 'Hardware',
    description: 'Problemas físicos.',
    is_active: true,
  },
  {
    id: 2,
    name: 'Legado',
    description: 'Categoria desativada.',
    is_active: false,
  },
]

function mountView() {
  return mount(CreateTicketView, {
    global: {
      stubs: {
        RouterLink: { template: '<a><slot /></a>' },
      },
    },
  })
}

describe('CreateTicketView', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    vi.mocked(getCategories).mockResolvedValue({ data: { data: categories } } as never)
  })

  it('exibe somente categorias ativas', async () => {
    const wrapper = mountView()

    await flushPromises()

    expect(wrapper.text()).toContain('Hardware')
    expect(wrapper.text()).not.toContain('Legado')
  })

  it('cria o chamado e retorna à listagem', async () => {
    vi.mocked(createTicket).mockResolvedValue({ data: { data: {} } } as never)

    const wrapper = mountView()

    await flushPromises()
    await wrapper.find('#title').setValue('Acesso ao ERP indisponível')
    await wrapper.find('#category').setValue('1')
    await wrapper.find('#priority').setValue('high')
    await wrapper.find('#description').setValue('Não consigo acessar o sistema desde esta manhã.')
    await wrapper.find('form').trigger('submit')
    await flushPromises()

    expect(createTicket).toHaveBeenCalledWith({
      category_id: 1,
      title: 'Acesso ao ERP indisponível',
      description: 'Não consigo acessar o sistema desde esta manhã.',
      priority: 'high',
    })
    expect(push).toHaveBeenCalledWith('/tickets')
  })
})
