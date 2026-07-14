import { setActivePinia, createPinia } from 'pinia'
import { useCategoryItemsStore } from '../category-items-store'

describe('category-items-store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  describe('getItemsBySlug', () => {
    it('returns the seeded items for a known slug', () => {
      const store = useCategoryItemsStore()
      const items = store.getItemsBySlug('sexo')

      expect(items).toHaveLength(3)
      expect(items[0]).toMatchObject({ name: 'Homens', slug: 'homens' })
    })

    it('returns an empty array for an unknown slug', () => {
      const store = useCategoryItemsStore()
      expect(store.getItemsBySlug('inexistente')).toEqual([])
    })
  })

  describe('createItem', () => {
    it('assigns the next sequential id within an existing category', () => {
      const store = useCategoryItemsStore()
      const created = store.createItem('sexo', { name: 'Infantil', slug: 'infantil', is_active: true })

      expect(created.id).toBe(4)
      expect(store.getItemsBySlug('sexo')).toHaveLength(4)
    })

    it('starts a brand new category at id 1', () => {
      const store = useCategoryItemsStore()
      const created = store.createItem('material', { name: 'Aço', is_active: true })

      expect(created.id).toBe(1)
      expect(store.getItemsBySlug('material')).toEqual([created])
    })
  })

  describe('updateItem', () => {
    it('merges the update into the matching item', () => {
      const store = useCategoryItemsStore()
      const updated = store.updateItem('sexo', 1, { name: 'Homem' })

      expect(updated).toMatchObject({ id: 1, name: 'Homem', slug: 'homens' })
      expect(store.getItemsBySlug('sexo')[0].name).toBe('Homem')
    })

    it('returns null when the item id does not exist', () => {
      const store = useCategoryItemsStore()
      expect(store.updateItem('sexo', 999, { name: 'X' })).toBeNull()
    })

    it('returns null when the category does not exist', () => {
      const store = useCategoryItemsStore()
      expect(store.updateItem('inexistente', 1, { name: 'X' })).toBeNull()
    })
  })

  describe('deleteItem', () => {
    it('removes the matching item and returns true', () => {
      const store = useCategoryItemsStore()
      const result = store.deleteItem('sexo', 2)

      expect(result).toBe(true)
      expect(store.getItemsBySlug('sexo').map((i) => i.id)).toEqual([1, 3])
    })

    it('returns false when the item id does not exist', () => {
      const store = useCategoryItemsStore()
      expect(store.deleteItem('sexo', 999)).toBe(false)
    })

    it('returns false when the category does not exist', () => {
      const store = useCategoryItemsStore()
      expect(store.deleteItem('inexistente', 1)).toBe(false)
    })
  })
})
