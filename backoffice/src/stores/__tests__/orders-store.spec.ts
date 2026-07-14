import { setActivePinia, createPinia } from 'pinia'
import { useOrdersStore } from '../orders-store'
import { ordersApi } from '../../services/api'

vi.mock('../../services/api', () => ({
  ordersApi: {
    list: vi.fn(),
    show: vi.fn(),
    updateStatus: vi.fn(),
  },
}))

const mockedOrdersApi = vi.mocked(ordersApi)

describe('orders-store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  describe('fetchOrders', () => {
    it('populates orders and pagination on success', async () => {
      mockedOrdersApi.list.mockResolvedValue({
        data: {
          data: [{ order_number: 'ORD-1' }],
          meta: { current_page: 1, last_page: 3, per_page: 15, total: 42 },
        },
      } as any)

      const store = useOrdersStore()
      await store.fetchOrders()

      expect(store.orders).toEqual([{ order_number: 'ORD-1' }])
      expect(store.pagination).toEqual({ current_page: 1, last_page: 3, per_page: 15, total: 42 })
      expect(store.loading).toBe(false)
      expect(store.error).toBeNull()
    })

    it('merges pagination state and extra params into the request', async () => {
      mockedOrdersApi.list.mockResolvedValue({
        data: { data: [], meta: { current_page: 2, last_page: 2, per_page: 15, total: 20 } },
      } as any)

      const store = useOrdersStore()
      store.setPage(2)
      await store.fetchOrders({ status: 'paid' })

      expect(mockedOrdersApi.list).toHaveBeenCalledWith({ per_page: 15, page: 2, status: 'paid' })
    })

    it('sets error message and clears loading on failure', async () => {
      mockedOrdersApi.list.mockRejectedValue({ response: { data: { message: 'Falha ao ligar à API' } } })

      const store = useOrdersStore()
      await store.fetchOrders()

      expect(store.error).toBe('Falha ao ligar à API')
      expect(store.loading).toBe(false)
      expect(store.orders).toEqual([])
    })

    it('falls back to a default message when the API gives none', async () => {
      mockedOrdersApi.list.mockRejectedValue(new Error('network down'))

      const store = useOrdersStore()
      await store.fetchOrders()

      expect(store.error).toBe('Erro ao carregar encomendas.')
    })

    it('sets loading true while the request is in flight', async () => {
      let resolveRequest: (value: unknown) => void
      mockedOrdersApi.list.mockReturnValue(
        new Promise((resolve) => {
          resolveRequest = resolve
        }) as any,
      )

      const store = useOrdersStore()
      const promise = store.fetchOrders()
      expect(store.loading).toBe(true)

      resolveRequest!({ data: { data: [], meta: { current_page: 1, last_page: 1, per_page: 15, total: 0 } } })
      await promise

      expect(store.loading).toBe(false)
    })
  })

  describe('fetchOrder', () => {
    it('returns the order on success', async () => {
      const order = { order_number: 'ORD-42' }
      mockedOrdersApi.show.mockResolvedValue({ data: { data: order } } as any)

      const store = useOrdersStore()
      const result = await store.fetchOrder('ORD-42')

      expect(result).toEqual(order)
      expect(store.error).toBeNull()
    })

    it('returns null and sets error on failure', async () => {
      mockedOrdersApi.show.mockRejectedValue({ response: { data: { message: 'Encomenda não encontrada' } } })

      const store = useOrdersStore()
      const result = await store.fetchOrder('missing')

      expect(result).toBeNull()
      expect(store.error).toBe('Encomenda não encontrada')
    })
  })

  describe('updateStatus', () => {
    it('returns the updated order on success', async () => {
      const updated = { order_number: 'ORD-1', status: { value: 'shipped', label: 'Enviado' } }
      mockedOrdersApi.updateStatus.mockResolvedValue({ data: { data: updated } } as any)

      const store = useOrdersStore()
      const result = await store.updateStatus('ORD-1', { status: 'shipped', tracking_number: 'TRK1' })

      expect(result).toEqual(updated)
      expect(store.saving).toBe(false)
      expect(mockedOrdersApi.updateStatus).toHaveBeenCalledWith('ORD-1', {
        status: 'shipped',
        tracking_number: 'TRK1',
      })
    })

    it('joins field validation errors from a 422 response', async () => {
      mockedOrdersApi.updateStatus.mockRejectedValue({
        response: {
          status: 422,
          data: { errors: { status: ['Estado inválido'], tracking_number: ['Demasiado longo'] } },
        },
      })

      const store = useOrdersStore()
      const result = await store.updateStatus('ORD-1', { status: 'bogus' })

      expect(result).toBeNull()
      expect(store.error).toBe('Estado inválido\nDemasiado longo')
      expect(store.saving).toBe(false)
    })

    it('falls back to a generic message for non-422 errors', async () => {
      mockedOrdersApi.updateStatus.mockRejectedValue({ response: { status: 500, data: {} } })

      const store = useOrdersStore()
      const result = await store.updateStatus('ORD-1', { status: 'shipped' })

      expect(result).toBeNull()
      expect(store.error).toBe('Erro ao atualizar estado.')
    })
  })

  describe('setPage', () => {
    it('updates the current page in pagination state', () => {
      const store = useOrdersStore()
      store.setPage(5)
      expect(store.pagination.current_page).toBe(5)
    })
  })
})
