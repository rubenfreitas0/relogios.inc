import { setActivePinia, createPinia } from 'pinia'
import { useShippingMethodsStore } from '../shipping-methods-store'
import { shippingMethodsApi } from '../../services/api'

vi.mock('../../services/api', () => ({
  shippingMethodsApi: {
    list: vi.fn(),
    create: vi.fn(),
    update: vi.fn(),
    destroy: vi.fn(),
  },
}))

const mockedApi = vi.mocked(shippingMethodsApi)

describe('shipping-methods-store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  describe('fetchMethods', () => {
    it('populates methods and pagination on success', async () => {
      mockedApi.list.mockResolvedValue({
        data: {
          data: [{ id: 1, name: 'CTT Standard' }],
          meta: { current_page: 1, last_page: 1, per_page: 15, total: 1 },
        },
      } as any)

      const store = useShippingMethodsStore()
      await store.fetchMethods()

      expect(store.methods).toEqual([{ id: 1, name: 'CTT Standard' }])
      expect(store.pagination.total).toBe(1)
    })

    it('sets a default error message on failure', async () => {
      mockedApi.list.mockRejectedValue(new Error('boom'))

      const store = useShippingMethodsStore()
      await store.fetchMethods()

      expect(store.error).toBe('Erro ao carregar métodos de envio.')
    })
  })

  describe('createMethod', () => {
    it('returns the created method on success', async () => {
      const created = { id: 2, name: 'DPD Express' }
      mockedApi.create.mockResolvedValue({ data: { data: created } } as any)

      const store = useShippingMethodsStore()
      const result = await store.createMethod({ name: 'DPD Express' })

      expect(result).toEqual(created)
      expect(store.saving).toBe(false)
    })

    it('joins 422 validation errors', async () => {
      mockedApi.create.mockRejectedValue({
        response: { status: 422, data: { errors: { price: ['Obrigatório'] } } },
      })

      const store = useShippingMethodsStore()
      const result = await store.createMethod({})

      expect(result).toBeNull()
      expect(store.error).toBe('Obrigatório')
    })
  })

  describe('updateMethod', () => {
    it('unwraps response.data.data when present', async () => {
      const updated = { id: 1, name: 'CTT Expresso' }
      mockedApi.update.mockResolvedValue({ data: { data: updated } } as any)

      const store = useShippingMethodsStore()
      const result = await store.updateMethod(1, { name: 'CTT Expresso' })

      expect(result).toEqual(updated)
    })

    it('falls back to response.data when there is no nested data key', async () => {
      const updated = { id: 1, name: 'CTT Expresso' }
      mockedApi.update.mockResolvedValue({ data: updated } as any)

      const store = useShippingMethodsStore()
      const result = await store.updateMethod(1, { name: 'CTT Expresso' })

      expect(result).toEqual(updated)
    })

    it('joins 422 validation errors', async () => {
      mockedApi.update.mockRejectedValue({
        response: { status: 422, data: { errors: { estimated_days: ['Inválido'] } } },
      })

      const store = useShippingMethodsStore()
      const result = await store.updateMethod(1, {})

      expect(result).toBeNull()
      expect(store.error).toBe('Inválido')
    })
  })

  describe('deleteMethod', () => {
    it('returns true on success', async () => {
      mockedApi.destroy.mockResolvedValue({} as any)

      const store = useShippingMethodsStore()
      expect(await store.deleteMethod(1)).toBe(true)
    })

    it('returns false and sets error on failure', async () => {
      mockedApi.destroy.mockRejectedValue({ response: { data: { message: 'Em uso por encomendas' } } })

      const store = useShippingMethodsStore()
      const result = await store.deleteMethod(1)

      expect(result).toBe(false)
      expect(store.error).toBe('Em uso por encomendas')
    })
  })

  describe('setPage', () => {
    it('updates the current page', () => {
      const store = useShippingMethodsStore()
      store.setPage(4)
      expect(store.pagination.current_page).toBe(4)
    })
  })
})
