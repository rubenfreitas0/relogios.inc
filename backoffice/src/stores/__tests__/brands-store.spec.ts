import { setActivePinia, createPinia } from 'pinia'
import { useBrandsStore } from '../brands-store'
import { brandsApi } from '../../services/api'

vi.mock('../../services/api', () => ({
  brandsApi: {
    list: vi.fn(),
    create: vi.fn(),
    update: vi.fn(),
    destroy: vi.fn(),
  },
}))

const mockedApi = vi.mocked(brandsApi)

describe('brands-store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  describe('fetchBrands', () => {
    it('populates brands and pagination on success', async () => {
      mockedApi.list.mockResolvedValue({
        data: { data: [{ id: 1, name: 'Casio' }], meta: { current_page: 1, last_page: 1, per_page: 15, total: 1 } },
      } as any)

      const store = useBrandsStore()
      await store.fetchBrands()

      expect(store.brands).toEqual([{ id: 1, name: 'Casio' }])
    })

    it('sets a default error message on failure', async () => {
      mockedApi.list.mockRejectedValue(new Error('boom'))

      const store = useBrandsStore()
      await store.fetchBrands()

      expect(store.error).toBe('Erro ao carregar marcas.')
    })
  })

  describe('createBrand', () => {
    it('returns the created brand on success', async () => {
      const created = { id: 2, name: 'Seiko' }
      mockedApi.create.mockResolvedValue({ data: { data: created } } as any)

      const store = useBrandsStore()
      const result = await store.createBrand(new FormData())

      expect(result).toEqual(created)
    })

    it('joins 422 validation errors', async () => {
      mockedApi.create.mockRejectedValue({
        response: { status: 422, data: { errors: { name: ['Obrigatório'] } } },
      })

      const store = useBrandsStore()
      const result = await store.createBrand(new FormData())

      expect(result).toBeNull()
      expect(store.error).toBe('Obrigatório')
    })
  })

  describe('updateBrand', () => {
    it('unwraps response.data.data when present', async () => {
      const updated = { id: 1, name: 'Casio Atualizado' }
      mockedApi.update.mockResolvedValue({ data: { data: updated } } as any)

      const store = useBrandsStore()
      const result = await store.updateBrand(1, new FormData())

      expect(result).toEqual(updated)
    })

    it('falls back to response.data when there is no nested data key', async () => {
      const updated = { id: 1, name: 'Casio Atualizado' }
      mockedApi.update.mockResolvedValue({ data: updated } as any)

      const store = useBrandsStore()
      const result = await store.updateBrand(1, new FormData())

      expect(result).toEqual(updated)
    })
  })

  describe('deleteBrand', () => {
    it('returns true on success', async () => {
      mockedApi.destroy.mockResolvedValue({} as any)

      const store = useBrandsStore()
      expect(await store.deleteBrand(1)).toBe(true)
    })

    it('returns false and sets a deactivation error on failure', async () => {
      mockedApi.destroy.mockRejectedValue({ response: { data: { message: 'Marca com produtos ativos' } } })

      const store = useBrandsStore()
      const result = await store.deleteBrand(1)

      expect(result).toBe(false)
      expect(store.error).toBe('Marca com produtos ativos')
    })
  })

  describe('setPage', () => {
    it('updates the current page', () => {
      const store = useBrandsStore()
      store.setPage(3)
      expect(store.pagination.current_page).toBe(3)
    })
  })
})
