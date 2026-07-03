import { defineStore } from 'pinia'
import { ref } from 'vue'
import { ticketsApi } from '../services/api'

export interface Address {
  id: number
  street: string
  city: string
  postal_code: string
  country: string
  is_default: boolean
}

export interface Order {
  id: number
  order_number: string
  total: string
  status: string
  payment_status: string
  created_at: string
}

export interface TicketUser {
  id: number
  firstname: string
  lastname: string
  email: string
  phone: string | null
  addresses: Address[]
  orders: Order[]
}

export interface Ticket {
  id: number
  subject: string
  message: string
  status: 'open' | 'closed'
  type: string
  created_at: string
  updated_at: string
  user: TicketUser
}

export interface PaginationMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
}

export const useTicketsStore = defineStore('tickets', () => {
  const tickets = ref<Ticket[]>([])
  const pagination = ref<PaginationMeta>({ current_page: 1, last_page: 1, per_page: 10, total: 0 })
  const loading = ref(false)
  const saving = ref(false)
  const error = ref<string | null>(null)

  async function fetchTickets(params: Record<string, unknown> = {}) {
    loading.value = true
    error.value = null
    try {
      const response = await ticketsApi.list({
        per_page: pagination.value.per_page,
        page: pagination.value.current_page,
        ...params,
      })
      tickets.value = response.data.data
      pagination.value = {
        current_page: response.data.meta.current_page,
        last_page: response.data.meta.last_page,
        per_page: response.data.meta.per_page,
        total: response.data.meta.total,
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao carregar tickets de suporte.'
    } finally {
      loading.value = false
    }
  }

  async function fetchTicket(id: number): Promise<Ticket | null> {
    loading.value = true
    error.value = null
    try {
      const response = await ticketsApi.show(id)
      return response.data.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao carregar o ticket.'
      return null
    } finally {
      loading.value = false
    }
  }

  async function updateTicketStatus(id: number, status: 'open' | 'closed'): Promise<boolean> {
    saving.value = true
    error.value = null
    try {
      const response = await ticketsApi.updateStatus(id, status)
      const updatedTicket = response.data.data as Ticket
      const index = tickets.value.findIndex((t) => t.id === id)
      if (index !== -1) {
        tickets.value[index] = updatedTicket
      }
      return true
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao atualizar estado do ticket.'
      return false
    } finally {
      saving.value = false
    }
  }

  async function deleteTicket(id: number): Promise<boolean> {
    error.value = null
    try {
      await ticketsApi.destroy(id)
      tickets.value = tickets.value.filter((t) => t.id !== id)
      return true
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao eliminar ticket.'
      return false
    }
  }

  function setPage(page: number) {
    pagination.value.current_page = page
  }

  return {
    tickets,
    pagination,
    loading,
    saving,
    error,
    fetchTickets,
    fetchTicket,
    updateTicketStatus,
    deleteTicket,
    setPage,
  }
})
