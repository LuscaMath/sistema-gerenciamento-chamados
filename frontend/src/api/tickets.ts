import { api } from '@/api/client'
import type { Ticket, TicketPriority } from '@/types/ticket'

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

export function getTickets() {
    return api.get<TicketCollectionResponse>('/api/v1/tickets')
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

export function resolveTicket(id: number, payload: ResolveTicketPayload) {
    return api.patch<TicketResponse>(`/api/v1/tickets/${id}/resolve`, payload,)
}

export function closeTicket(id: number) {
    return api.patch<TicketResponse>(`/api/v1/tickets/${id}/close`)
}