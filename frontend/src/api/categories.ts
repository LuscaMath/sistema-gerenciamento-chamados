import { api } from '@/api/client'
import type { Category } from '@/types/category'

interface CategoryCollectionResponse {
  data: Category[]
}

interface CategoryResponse {
  data: Category
}

export interface CategoryPayload {
  name: string
  description: string | null
}

export function getCategories() {
  return api.get<CategoryCollectionResponse>('/api/v1/categories')
}

export function createCategory(payload: CategoryPayload) {
  return api.post<CategoryResponse>('/api/v1/categories', payload)
}

export function updateCategory(id: number, payload: CategoryPayload) {
  return api.put<CategoryResponse>(`/api/v1/categories/${id}`, payload)
}

export function activateCategory(id: number) {
  return api.patch<CategoryResponse>(`/api/v1/categories/${id}/activate`)
}

export function deactivateCategory(id: number) {
  return api.patch<CategoryResponse>(`/api/v1/categories/${id}/deactivate`)
}
