import { beforeEach, describe, expect, it, vi } from 'vitest'
import { flushPromises, mount } from '@vue/test-utils'
import UsersView from '@/views/UsersView.vue'
import { createUser, getUsers } from '@/api/users'

vi.mock('@/api/users', () => ({
  createUser: vi.fn(),
  getUsers: vi.fn(),
  updateUser: vi.fn(),
}))

const users = [
  {
    id: 1,
    name: 'Ana Técnica',
    email: 'ana@example.com',
    role: 'technician' as const,
    created_at: '2026-08-30T12:00:00.000000Z',
  },
]

describe('UsersView', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    vi.mocked(getUsers).mockResolvedValue({ data: { data: users } } as never)
  })

  it('lists users returned by the API', async () => {
    const wrapper = mount(UsersView)

    await flushPromises()

    expect(wrapper.text()).toContain('Ana Técnica')
    expect(wrapper.text()).toContain('Técnico')
  })

  it('creates a user from the administrative form', async () => {
    vi.mocked(createUser).mockResolvedValue({ data: { data: users[0] } } as never)

    const wrapper = mount(UsersView)

    await flushPromises()
    await wrapper.find('#new-user').trigger('click')
    await wrapper.find('#user-name').setValue('Carlos Solicitante')
    await wrapper.find('#user-email').setValue('carlos@example.com')
    await wrapper.find('#user-password').setValue('password123')
    await wrapper.find('form').trigger('submit')
    await flushPromises()

    expect(createUser).toHaveBeenCalledWith({
      name: 'Carlos Solicitante',
      email: 'carlos@example.com',
      password: 'password123',
      role: 'requester',
    })
  })
})
