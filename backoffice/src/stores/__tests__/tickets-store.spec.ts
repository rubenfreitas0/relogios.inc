import { setActivePinia, createPinia } from 'pinia'
import { useTicketsStore } from '../tickets-store'
import { ticketsApi } from '../../services/api'

vi.mock('../../services/api', () => ({
  ticketsApi: {
    list: vi.fn(),
    show: vi.fn(),
    updateStatus: vi.fn(),
    destroy: vi.fn(),
  },
}))

const mockedApi = vi.mocked(ticketsApi)

function makeTicket(overrides: Partial<{ id: number; status: 'open' | 'closed' }> = {}) {
  return { id: 1, subject: 'Dúvida sobre encomenda', status: 'open', ...overrides } as any
}

describe('tickets-store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  describe('fetchTickets', () => {
    it('populates tickets and pagination on success', async () => {
      mockedApi.list.mockResolvedValue({
        data: { data: [makeTicket()], meta: { current_page: 1, last_page: 1, per_page: 10, total: 1 } },
      } as any)

      const store = useTicketsStore()
      await store.fetchTickets()

      expect(store.tickets).toHaveLength(1)
    })

    it('sets a default error message on failure', async () => {
      mockedApi.list.mockRejectedValue(new Error('boom'))

      const store = useTicketsStore()
      await store.fetchTickets()

      expect(store.error).toBe('Erro ao carregar tickets de suporte.')
    })
  })

  describe('fetchTicket', () => {
    it('returns the ticket on success', async () => {
      mockedApi.show.mockResolvedValue({ data: { data: makeTicket() } } as any)

      const store = useTicketsStore()
      const result = await store.fetchTicket(1)

      expect(result).toEqual(makeTicket())
    })

    it('returns null and sets error on failure', async () => {
      mockedApi.show.mockRejectedValue({ response: { data: { message: 'Ticket não encontrado' } } })

      const store = useTicketsStore()
      const result = await store.fetchTicket(404)

      expect(result).toBeNull()
      expect(store.error).toBe('Ticket não encontrado')
    })
  })

  describe('updateTicketStatus', () => {
    it('replaces the ticket in local state on success', async () => {
      const closed = makeTicket({ status: 'closed' })
      mockedApi.updateStatus.mockResolvedValue({ data: { data: closed } } as any)

      const store = useTicketsStore()
      store.tickets.push(makeTicket({ id: 1 }), makeTicket({ id: 2 }))
      const result = await store.updateTicketStatus(1, 'closed')

      expect(result).toBe(true)
      expect(store.tickets[0]).toEqual(closed)
      expect(store.tickets[1]).toEqual(makeTicket({ id: 2 }))
    })

    it('does not throw when the ticket is not in local state', async () => {
      mockedApi.updateStatus.mockResolvedValue({ data: { data: makeTicket({ id: 99, status: 'closed' }) } } as any)

      const store = useTicketsStore()
      const result = await store.updateTicketStatus(99, 'closed')

      expect(result).toBe(true)
      expect(store.tickets).toEqual([])
    })

    it('returns false and sets error on failure', async () => {
      mockedApi.updateStatus.mockRejectedValue({ response: { data: { message: 'Erro ao mudar estado' } } })

      const store = useTicketsStore()
      const result = await store.updateTicketStatus(1, 'closed')

      expect(result).toBe(false)
      expect(store.error).toBe('Erro ao mudar estado')
      expect(store.saving).toBe(false)
    })
  })

  describe('deleteTicket', () => {
    it('removes the ticket from local state on success', async () => {
      mockedApi.destroy.mockResolvedValue({} as any)

      const store = useTicketsStore()
      store.tickets.push(makeTicket({ id: 1 }), makeTicket({ id: 2 }))
      const result = await store.deleteTicket(1)

      expect(result).toBe(true)
      expect(store.tickets.map((t) => t.id)).toEqual([2])
    })

    it('keeps local state and sets error on failure', async () => {
      mockedApi.destroy.mockRejectedValue({ response: { data: { message: 'Erro ao eliminar' } } })

      const store = useTicketsStore()
      store.tickets.push(makeTicket({ id: 1 }))
      const result = await store.deleteTicket(1)

      expect(result).toBe(false)
      expect(store.tickets).toHaveLength(1)
    })
  })

  describe('setPage', () => {
    it('updates the current page', () => {
      const store = useTicketsStore()
      store.setPage(2)
      expect(store.pagination.current_page).toBe(2)
    })
  })
})
