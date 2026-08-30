import { beforeEach, describe, expect, it, vi } from 'vitest'
import { flushPromises, mount } from '@vue/test-utils'
import TicketDetailsView from '@/views/TicketDetailsView.vue'
import {
  assignTechnician,
  assignTicket,
  closeTicket,
  getTechnicians,
  getTicket,
  resolveTicket,
} from '@/api/tickets'
import { createTicketComment, getTicketComments } from '@/api/ticket-comments'
import type { Ticket, TicketStatus } from '@/types/ticket'

const route = { params: { id: '1' } }
const auth = {
  user: null as {
    id: number
    name: string
    role: 'requester' | 'technician' | 'admin'
  } | null,
}

vi.mock('vue-router', () => ({
  useRoute: () => route,
}))

vi.mock('@/stores/auth', () => ({
  useAuthStore: () => auth,
}))

vi.mock('@/api/tickets', () => ({
  assignTechnician: vi.fn<() => void>(),
  assignTicket: vi.fn<() => void>(),
  closeTicket: vi.fn<() => void>(),
  getTechnicians: vi.fn<() => void>(),
  getTicket: vi.fn<() => void>(),
  resolveTicket: vi.fn<() => void>(),
}))

vi.mock('@/api/ticket-comments', () => ({
  createTicketComment: vi.fn<() => void>(),
  getTicketComments: vi.fn<() => void>(),
}))

const requester = { id: 10, name: 'Rita Solicitante', role: 'requester' as const }
const technician = { id: 20, name: 'Téo Técnico', role: 'technician' as const }
const administrator = { id: 30, name: 'Ada Administradora', role: 'admin' as const }

function makeTicket(
  status: TicketStatus = 'open',
  assignedTechnician: Ticket['technician'] = null,
): Ticket {
  return {
    id: 1,
    title: 'Acesso ao ERP indisponível',
    description: 'Não consigo acessar o sistema.',
    priority: 'high',
    status,
    solution: null,
    requester: { id: requester.id, name: requester.name },
    technician: assignedTechnician,
    category: { id: 1, name: 'Sistemas' },
    resolved_at: null,
    closed_at: null,
    created_at: '2026-08-30T12:00:00.000000Z',
    updated_at: '2026-08-30T12:00:00.000000Z',
  }
}

function response(ticket: Ticket) {
  return { data: { data: ticket } } as never
}

function mountView() {
  return mount(TicketDetailsView, {
    global: {
      stubs: {
        RouterLink: { template: '<a><slot /></a>' },
      },
    },
  })
}

describe('TicketDetailsView', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    auth.user = requester
    vi.mocked(getTicket).mockResolvedValue(response(makeTicket()))
    vi.mocked(getTicketComments).mockResolvedValue({ data: { data: [] } } as never)
  })

  it('permite que um técnico assuma um chamado aberto', async () => {
    auth.user = technician
    vi.mocked(assignTicket).mockResolvedValue(
      response(makeTicket('in_progress', { id: technician.id, name: technician.name })),
    )

    const wrapper = mountView()

    await flushPromises()
    await wrapper.find('.hero-actions button').trigger('click')
    await flushPromises()

    expect(assignTicket).toHaveBeenCalledWith(1)
    expect(wrapper.text()).toContain('Em atendimento')
  })

  it('permite que um administrador atribua um técnico', async () => {
    auth.user = administrator
    vi.mocked(getTechnicians).mockResolvedValue({
      data: { data: [{ id: technician.id, name: technician.name }] },
    } as never)
    vi.mocked(assignTechnician).mockResolvedValue(
      response(makeTicket('in_progress', { id: technician.id, name: technician.name })),
    )

    const wrapper = mountView()

    await flushPromises()
    expect(wrapper.text()).not.toContain('Assumir chamado')
    expect(wrapper.find('.resolve-card').exists()).toBe(false)
    await wrapper.find('#technician').setValue(String(technician.id))
    await wrapper.find('.assignment-card button').trigger('click')
    await flushPromises()

    expect(assignTechnician).toHaveBeenCalledWith(1, { technician_id: technician.id })
    expect(wrapper.text()).toContain(technician.name)
    expect(wrapper.text()).toContain('Em atendimento')
  })

  it('permite que o técnico responsável registre a solução', async () => {
    auth.user = technician
    const ticket = makeTicket('in_progress', { id: technician.id, name: technician.name })
    vi.mocked(getTicket).mockResolvedValue(response(ticket))
    vi.mocked(resolveTicket).mockResolvedValue(
      response({
        ...ticket,
        status: 'resolved',
        solution: 'A permissão do usuário foi restaurada.',
        resolved_at: '2026-08-30T13:00:00.000000Z',
      }),
    )

    const wrapper = mountView()

    await flushPromises()
    await wrapper.find('#solution').setValue('A permissão do usuário foi restaurada.')
    await wrapper.find('.resolve-card button').trigger('click')
    await flushPromises()

    expect(resolveTicket).toHaveBeenCalledWith(1, {
      solution: 'A permissão do usuário foi restaurada.',
    })
    expect(wrapper.text()).toContain('Solução apresentada')
  })

  it('permite que o solicitante feche um chamado resolvido', async () => {
    const ticket = makeTicket('resolved', { id: technician.id, name: technician.name })
    vi.mocked(getTicket).mockResolvedValue(response(ticket))
    vi.mocked(closeTicket).mockResolvedValue(
      response({
        ...ticket,
        status: 'closed',
        closed_at: '2026-08-30T14:00:00.000000Z',
      }),
    )

    const wrapper = mountView()

    await flushPromises()
    await wrapper.find('.hero-actions button').trigger('click')
    await flushPromises()

    expect(closeTicket).toHaveBeenCalledWith(1)
    expect(wrapper.find('.comment-form').exists()).toBe(false)
  })

  it('adiciona comentários a um chamado aberto', async () => {
    vi.mocked(createTicketComment).mockResolvedValue({
      data: {
        data: {
          id: 1,
          content: 'O problema começou após a atualização.',
          user: { id: requester.id, name: requester.name },
          created_at: '2026-08-30T12:30:00.000000Z',
        },
      },
    } as never)

    const wrapper = mountView()

    await flushPromises()
    await wrapper.find('#comment').setValue('O problema começou após a atualização.')
    await wrapper.find('.comment-form').trigger('submit')
    await flushPromises()

    expect(createTicketComment).toHaveBeenCalledWith(1, 'O problema começou após a atualização.')
    expect(wrapper.text()).toContain('O problema começou após a atualização.')
  })

  it('oculta o formulário de comentário de chamados fechados', async () => {
    vi.mocked(getTicket).mockResolvedValue(response(makeTicket('closed')))

    const wrapper = mountView()

    await flushPromises()

    expect(wrapper.find('.comment-form').exists()).toBe(false)
  })
})
