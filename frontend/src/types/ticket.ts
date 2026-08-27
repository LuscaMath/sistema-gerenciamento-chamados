export type TicketStatus = 'open' | 'in_progress' | 'resolved' | 'closed'

export type TicketPriority = 'low' | 'medium' | 'high'

export interface TicketUser {
    id: number
    name: string
}

export interface TicketCategory {
    id: number
    name: string
}

export interface Ticket {
    id: number
    title: string
    description: string
    priority: TicketPriority
    status: TicketStatus
    solution: string | null
    requester: TicketUser
    technician: TicketUser | null
    category: TicketCategory
    resolved_at: string | null
    closed_at: string | null
    created_at: string
    updated_at: string
}