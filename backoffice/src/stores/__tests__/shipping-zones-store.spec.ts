import { setActivePinia, createPinia } from 'pinia'
import { useShippingZonesStore } from '../shipping-zones-store'
import { shippingZonesApi } from '../../services/api'

vi.mock('../../services/api', () => ({
  shippingZonesApi: {
    list: vi.fn(),
    create: vi.fn(),
    update: vi.fn(),
    destroy: vi.fn(),
  },
}))

const mockedApi = vi.mocked(shippingZonesApi)

describe('shipping-zones-store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  describe('fetchZones', () => {
    it('populates zones and pagination on success', async () => {
      mockedApi.list.mockResolvedValue({
        data: {
          data: [{ id: 1, name: 'Portugal Continental' }],
          meta: { current_page: 1, last_page: 1, per_page: 15, total: 1 },
        },
      } as any)

      const store = useShippingZonesStore()
      await store.fetchZones()

      expect(store.zones).toEqual([{ id: 1, name: 'Portugal Continental' }])
      expect(store.pagination.total).toBe(1)
    })

    it('sets a default error message on failure', async () => {
      mockedApi.list.mockRejectedValue(new Error('boom'))

      const store = useShippingZonesStore()
      await store.fetchZones()

      expect(store.error).toBe('Erro ao carregar zonas de envio.')
    })
  })

  describe('createZone', () => {
    it('returns the created zone on success', async () => {
      const created = { id: 2, name: 'Ilhas' }
      mockedApi.create.mockResolvedValue({ data: { data: created } } as any)

      const store = useShippingZonesStore()
      const result = await store.createZone({ name: 'Ilhas' })

      expect(result).toEqual(created)
    })

    it('joins 422 validation errors', async () => {
      mockedApi.create.mockRejectedValue({
        response: { status: 422, data: { errors: { name: ['Obrigatório'] } } },
      })

      const store = useShippingZonesStore()
      const result = await store.createZone({})

      expect(result).toBeNull()
      expect(store.error).toBe('Obrigatório')
    })

    it('falls back to a generic message for non-422 errors', async () => {
      mockedApi.create.mockRejectedValue({ response: { status: 500, data: {} } })

      const store = useShippingZonesStore()
      const result = await store.createZone({})

      expect(result).toBeNull()
      expect(store.error).toBe('Erro ao criar zona de envio.')
    })
  })

  describe('updateZone', () => {
    it('unwraps response.data.data when present', async () => {
      const updated = { id: 1, name: 'Ilhas Atualizado' }
      mockedApi.update.mockResolvedValue({ data: { data: updated } } as any)

      const store = useShippingZonesStore()
      const result = await store.updateZone(1, { name: 'Ilhas Atualizado' })

      expect(result).toEqual(updated)
    })

    it('falls back to response.data when there is no nested data key', async () => {
      const updated = { id: 1, name: 'Ilhas Atualizado' }
      mockedApi.update.mockResolvedValue({ data: updated } as any)

      const store = useShippingZonesStore()
      const result = await store.updateZone(1, { name: 'Ilhas Atualizado' })

      expect(result).toEqual(updated)
    })
  })

  describe('deleteZone', () => {
    it('returns true on success', async () => {
      mockedApi.destroy.mockResolvedValue({} as any)

      const store = useShippingZonesStore()
      expect(await store.deleteZone(1)).toBe(true)
    })

    it('returns false and sets error on failure', async () => {
      mockedApi.destroy.mockRejectedValue({ response: { data: { message: 'Zona com métodos associados' } } })

      const store = useShippingZonesStore()
      const result = await store.deleteZone(1)

      expect(result).toBe(false)
      expect(store.error).toBe('Zona com métodos associados')
    })
  })

  describe('setPage', () => {
    it('updates the current page', () => {
      const store = useShippingZonesStore()
      store.setPage(2)
      expect(store.pagination.current_page).toBe(2)
    })
  })
})
