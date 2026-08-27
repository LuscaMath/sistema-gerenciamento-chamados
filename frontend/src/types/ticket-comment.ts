export interface TicketCommentUser {
    id: number
    name: string
}

export interface TicketComment {
    id: number
    content: string
    user: TicketCommentUser
    created_at: string
}