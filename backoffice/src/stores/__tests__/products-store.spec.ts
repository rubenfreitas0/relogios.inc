import { setActivePinia, createPinia } from 'pinia'
import { useProductsStore } from '../products-store'
import { productsApi, brandsApi, categoriesApi } from '../../services/api'

vi.mock('../../services/api', () => ({
  productsApi: {
    list: vi.fn(),
    show: vi.fn(),
    create: vi.fn(),
    update: vi.fn(),
    destroy: vi.fn(),
    updateStock: vi.fn(),
    restore: vi.fn(),
  },
  brandsApi: { list: vi.fn() },
  categoriesApi: { list: vi.fn() },
}))

const mockedProductsApi = vi.mocked(productsApi)
const mockedBrandsApi = vi.mocked(brandsApi)
const mockedCategoriesApi = vi.mocked(categoriesApi)

function makeProduct(overrides: Partial<{ id: number; stock: number }> = {}) {
  return { id: 1, name: 'Relógio X', stock: 10, ...overrides } as any
}

describe('products-store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  describe('fetchProducts', () => {
    it('populates products and pagination on success', async () => {
      mockedProductsApi.list.mockResolvedValue({
        data: { data: [makeProduct()], meta: { current_page: 1, last_page: 1, per_page: 10, total: 1 } },
      } as any)

      const store = useProductsStore()
      await store.fetchProducts()

      expect(store.products).toHaveLength(1)
      expect(store.pagination.total).toBe(1)
      expect(store.error).toBeNull()
    })

    it('sets a default error message on failure', async () => {
      mockedProductsApi.list.mockRejectedValue(new Error('boom'))

      const store = useProductsStore()
      await store.fetchProducts()

      expect(store.error).toBe('Erro ao carregar produtos.')
      expect(store.loading).toBe(false)
    })
  })

  describe('fetchProduct', () => {
    it('returns the product on success', async () => {
      mockedProductsApi.show.mockResolvedValue({ data: { data: makeProduct() } } as any)

      const store = useProductsStore()
      const result = await store.fetchProduct(1)

      expect(result).toEqual(makeProduct())
    })

    it('returns null and sets error on failure', async () => {
      mockedProductsApi.show.mockRejectedValue({ response: { data: { message: 'Produto não encontrado' } } })

      const store = useProductsStore()
      const result = await store.fetchProduct(999)

      expect(result).toBeNull()
      expect(store.error).toBe('Produto não encontrado')
    })
  })

  describe('createProduct', () => {
    it('returns the created product on success', async () => {
      mockedProductsApi.create.mockResolvedValue({ data: { data: makeProduct() } } as any)

      const store = useProductsStore()
      const result = await store.createProduct(new FormData())

      expect(result).toEqual(makeProduct())
      expect(store.saving).toBe(false)
    })

    it('joins 422 validation errors', async () => {
      mockedProductsApi.create.mockRejectedValue({
        response: { status: 422, data: { errors: { name: ['Obrigatório'], price: ['Inválido'] } } },
      })

      const store = useProductsStore()
      const result = await store.createProduct(new FormData())

      expect(result).toBeNull()
      expect(store.error).toBe('Obrigatório\nInválido')
    })

    it('falls back to a generic message for non-422 errors', async () => {
      mockedProductsApi.create.mockRejectedValue({ response: { status: 500, data: {} } })

      const store = useProductsStore()
      const result = await store.createProduct(new FormData())

      expect(result).toBeNull()
      expect(store.error).toBe('Erro ao criar produto.')
    })
  })

  describe('updateProduct', () => {
    it('returns the updated product on success', async () => {
      const updated = makeProduct({ id: 5 })
      mockedProductsApi.update.mockResolvedValue({ data: { data: updated } } as any)

      const store = useProductsStore()
      const result = await store.updateProduct(5, new FormData())

      expect(result).toEqual(updated)
    })

    it('joins 422 validation errors', async () => {
      mockedProductsApi.update.mockRejectedValue({
        response: { status: 422, data: { errors: { stock: ['Deve ser positivo'] } } },
      })

      const store = useProductsStore()
      const result = await store.updateProduct(5, new FormData())

      expect(result).toBeNull()
      expect(store.error).toBe('Deve ser positivo')
    })
  })

  describe('deleteProduct', () => {
    it('removes the product from local state on success', async () => {
      mockedProductsApi.destroy.mockResolvedValue({} as any)

      const store = useProductsStore()
      store.products.push(makeProduct({ id: 1 }), makeProduct({ id: 2 }))
      const result = await store.deleteProduct(1)

      expect(result).toBe(true)
      expect(store.products.map((p) => p.id)).toEqual([2])
    })

    it('keeps local state and sets error on failure', async () => {
      mockedProductsApi.destroy.mockRejectedValue({ response: { data: { message: 'Não pode ser eliminado' } } })

      const store = useProductsStore()
      store.products.push(makeProduct({ id: 1 }))
      const result = await store.deleteProduct(1)

      expect(result).toBe(false)
      expect(store.error).toBe('Não pode ser eliminado')
      expect(store.products).toHaveLength(1)
    })
  })

  describe('updateStock', () => {
    it('updates stock on the matching local product', async () => {
      mockedProductsApi.updateStock.mockResolvedValue({} as any)

      const store = useProductsStore()
      store.products.push(makeProduct({ id: 1, stock: 3 }))
      const result = await store.updateStock(1, 99)

      expect(result).toBe(true)
      expect(store.products[0].stock).toBe(99)
    })

    it('does not throw when the product is not in local state', async () => {
      mockedProductsApi.updateStock.mockResolvedValue({} as any)

      const store = useProductsStore()
      const result = await store.updateStock(404, 5)

      expect(result).toBe(true)
    })

    it('sets error on failure', async () => {
      mockedProductsApi.updateStock.mockRejectedValue({ response: { data: { message: 'Stock inválido' } } })

      const store = useProductsStore()
      const result = await store.updateStock(1, -1)

      expect(result).toBe(false)
      expect(store.error).toBe('Stock inválido')
    })
  })

  describe('restoreProduct', () => {
    it('returns true on success', async () => {
      mockedProductsApi.restore.mockResolvedValue({} as any)

      const store = useProductsStore()
      const result = await store.restoreProduct(1)

      expect(result).toBe(true)
    })

    it('returns false and sets error on failure', async () => {
      mockedProductsApi.restore.mockRejectedValue({ response: { data: { message: 'Erro ao restaurar' } } })

      const store = useProductsStore()
      const result = await store.restoreProduct(1)

      expect(result).toBe(false)
      expect(store.error).toBe('Erro ao restaurar')
    })
  })

  describe('fetchBrandOptions', () => {
    it('maps brands to select options', async () => {
      mockedBrandsApi.list.mockResolvedValue({
        data: {
          data: [
            { id: 1, name: 'Casio' },
            { id: 2, name: 'Seiko' },
          ],
        },
      } as any)

      const store = useProductsStore()
      await store.fetchBrandOptions()

      expect(store.brandOptions).toEqual([
        { value: 1, text: 'Casio' },
        { value: 2, text: 'Seiko' },
      ])
    })

    it('clears options silently on failure', async () => {
      mockedBrandsApi.list.mockRejectedValue(new Error('down'))

      const store = useProductsStore()
      store.brandOptions.push({ value: 1, text: 'stale' })
      await store.fetchBrandOptions()

      expect(store.brandOptions).toEqual([])
      expect(store.error).toBeNull()
    })
  })

  describe('fetchCategoryOptions', () => {
    it('maps categories to select options', async () => {
      mockedCategoriesApi.list.mockResolvedValue({
        data: { data: [{ id: 3, name: 'Desportivo' }] },
      } as any)

      const store = useProductsStore()
      await store.fetchCategoryOptions()

      expect(store.categoryOptions).toEqual([{ value: 3, text: 'Desportivo' }])
    })

    it('clears options silently on failure', async () => {
      mockedCategoriesApi.list.mockRejectedValue(new Error('down'))

      const store = useProductsStore()
      await store.fetchCategoryOptions()

      expect(store.categoryOptions).toEqual([])
    })
  })

  describe('setPage', () => {
    it('updates the current page', () => {
      const store = useProductsStore()
      store.setPage(3)
      expect(store.pagination.current_page).toBe(3)
    })
  })
})
