import { api } from '@/api/client'
import type { TicketComment } from '@/types/ticket-comment'

interface TicketCommentCollectionResponse {
    data: TicketComment[]
}

interface TicketCommentResponse {
    data: TicketComment
}

export function getTicketComments(ticketId: number) {
    return api.get<TicketCommentCollectionResponse>(
        `/api/v1/tickets/${ticketId}/comments`,
    )
}

export function createTicketComment(
    ticketId: number,
    content: string,
) {
    return api.post<TicketCommentResponse>(
        `/api/v1/tickets/${ticketId}/comments`,
        { content },
    )
}