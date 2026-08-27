import { api } from '@/api/client'
import type { Category } from '@/types/category'

interface CategoryCollectionResponse {
  data: Category[]
}

export function getCategories() {
  return api.get<CategoryCollectionResponse>('/api/v1/categories')
}