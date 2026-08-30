import { api } from '@/api/client'
import type { User, UserRole } from '@/types/user'

interface UserCollectionResponse {
  data: User[]
}

interface UserResponse {
  data: User
}

export interface UserPayload {
  name: string
  email: string
  role: UserRole
  password?: string
}

export function getUsers() {
  return api.get<UserCollectionResponse>('/api/v1/users')
}

export function createUser(payload: Required<UserPayload>) {
  return api.post<UserResponse>('/api/v1/users', payload)
}

export function updateUser(id: number, payload: UserPayload) {
  return api.put<UserResponse>(`/api/v1/users/${id}`, payload)
}

export function deactivateUser(id: number) {
  return api.patch<UserResponse>(`/api/v1/users/${id}/deactivate`)
}

export function activateUser(id: number) {
  return api.patch<UserResponse>(`/api/v1/users/${id}/activate`)
}
