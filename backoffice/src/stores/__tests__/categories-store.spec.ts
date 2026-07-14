import { setActivePinia, createPinia } from 'pinia'
import { useCategoriesStore } from '../categories-store'
import { categoriesApi } from '../../services/api'

vi.mock('../../services/api', () => ({
  categoriesApi: {
    list: vi.fn(),
    update: vi.fn(),
  },
}))

const mockedApi = vi.mocked(categoriesApi)

describe('categories-store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  describe('fetchCategories', () => {
    it('populates categories and pagination on success', async () => {
      mockedApi.list.mockResolvedValue({
        data: {
          data: [{ id: 1, name: 'Relógios de Pulso' }],
          meta: { current_page: 1, last_page: 1, per_page: 15, total: 1 },
        },
      } as any)

      const store = useCategoriesStore()
      await store.fetchCategories()

      expect(store.categories).toEqual([{ id: 1, name: 'Relógios de Pulso' }])
    })

    it('sets a default error message on failure', async () => {
      mockedApi.list.mockRejectedValue(new Error('boom'))

      const store = useCategoriesStore()
      await store.fetchCategories()

      expect(store.error).toBe('Erro ao carregar categorias.')
    })
  })

  describe('updateCategory', () => {
    it('unwraps response.data.data when present', async () => {
      const updated = { id: 1, name: 'Atualizado' }
      mockedApi.update.mockResolvedValue({ data: { data: updated } } as any)

      const store = useCategoriesStore()
      const result = await store.updateCategory(1, { name: 'Atualizado' })

      expect(result).toEqual(updated)
    })

    it('falls back to response.data when there is no nested data key', async () => {
      const updated = { id: 1, name: 'Atualizado' }
      mockedApi.update.mockResolvedValue({ data: updated } as any)

      const store = useCategoriesStore()
      const result = await store.updateCategory(1, { name: 'Atualizado' })

      expect(result).toEqual(updated)
    })

    it('joins 422 validation errors', async () => {
      mockedApi.update.mockRejectedValue({
        response: { status: 422, data: { errors: { name: ['Obrigatório'] } } },
      })

      const store = useCategoriesStore()
      const result = await store.updateCategory(1, {})

      expect(result).toBeNull()
      expect(store.error).toBe('Obrigatório')
    })

    it('falls back to a generic message for non-422 errors', async () => {
      mockedApi.update.mockRejectedValue({ response: { status: 500, data: {} } })

      const store = useCategoriesStore()
      const result = await store.updateCategory(1, {})

      expect(result).toBeNull()
      expect(store.error).toBe('Erro ao atualizar categoria.')
    })
  })

  describe('setPage', () => {
    it('updates the current page', () => {
      const store = useCategoriesStore()
      store.setPage(2)
      expect(store.pagination.current_page).toBe(2)
    })
  })
})
