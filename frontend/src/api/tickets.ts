import { api } from '@/api/client'
import type { Ticket, TicketPriority } from '@/types/ticket'
import type { Technician } from '@/types/technician'

interface TicketCollectionResponse {
  data: Ticket[]
}

interface TicketResponse {
  data: Ticket
}

export interface CreateTicketPayload {
  category_id: number
  title: string
  description: string
  priority: TicketPriority
}

export interface ResolveTicketPayload {
  solution: string
}

export interface TicketFilters {
  status?: Ticket['status']
  priority?: TicketPriority
  category_id?: number
}

export interface AssignTechnicianPayload {
  technician_id: number
}

export function getTickets(filters: TicketFilters = {}) {
  return api.get<TicketCollectionResponse>('/api/v1/tickets', {
    params: filters,
  })
}

export function createTicket(payload: CreateTicketPayload) {
  return api.post<TicketResponse>('/api/v1/tickets', payload)
}

export function getTicket(id: number) {
  return api.get<TicketResponse>(`/api/v1/tickets/${id}`)
}

export function assignTicket(id: number) {
  return api.patch<TicketResponse>(`/api/v1/tickets/${id}/assign`)
}

export function getTechnicians() {
  return api.get<{ data: Technician[] }>('/api/v1/technicians')
}

export function assignTechnician(id: number, payload: AssignTechnicianPayload) {
  return api.patch<TicketResponse>(`/api/v1/tickets/${id}/assign-technician`, payload)
}

export function resolveTicket(id: number, payload: ResolveTicketPayload) {
  return api.patch<TicketResponse>(`/api/v1/tickets/${id}/resolve`, payload)
}

export function closeTicket(id: number) {
  return api.patch<TicketResponse>(`/api/v1/tickets/${id}/close`)
}
