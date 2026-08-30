import { beforeEach, describe, expect, it, vi } from 'vitest'
import { flushPromises, mount } from '@vue/test-utils'
import CategoriesView from '@/views/CategoriesView.vue'
import { createCategory, getCategories } from '@/api/categories'

vi.mock('@/api/categories', () => ({
  activateCategory: vi.fn(),
  createCategory: vi.fn(),
  deactivateCategory: vi.fn(),
  getCategories: vi.fn(),
  updateCategory: vi.fn(),
}))

const categories = [
  {
    id: 1,
    name: 'Hardware',
    description: 'Problemas físicos.',
    is_active: true,
  },
]

describe('CategoriesView', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    vi.mocked(getCategories).mockResolvedValue({ data: { data: categories } } as never)
  })

  it('lists categories returned by the API', async () => {
    const wrapper = mount(CategoriesView)

    await flushPromises()

    expect(wrapper.text()).toContain('Hardware')
    expect(wrapper.text()).toContain('Ativa')
  })

  it('creates a category from the form', async () => {
    vi.mocked(createCategory).mockResolvedValue({
      data: {
        data: categories[0],
      },
    } as never)

    const wrapper = mount(CategoriesView)

    await flushPromises()
    await wrapper.find('#new-category').trigger('click')
    await wrapper.find('#category-name').setValue('Rede')
    await wrapper.find('#category-description').setValue('Conectividade.')
    await wrapper.find('form').trigger('submit')
    await flushPromises()

    expect(createCategory).toHaveBeenCalledWith({
      name: 'Rede',
      description: 'Conectividade.',
    })
  })
})
