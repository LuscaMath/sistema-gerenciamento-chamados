export type UserRole = 'requester' | 'technician' | 'admin'

export interface User {
  id: number
  name: string
  email: string
  role: UserRole
  is_active: boolean
  created_at: string
}
