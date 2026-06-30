import { defineStore } from 'pinia'
import { ref } from 'vue'

export interface CategoryItem {
  id: number
  name: string
  slug?: string
  hex?: string
  min?: number
  max?: number
  is_active: boolean
}

export const useCategoryItemsStore = defineStore('category-items', () => {
  const itemsMap = ref<Record<string, CategoryItem[]>>({
    cor: [
      { id: 1, name: 'Preto', hex: '#1a1a1a', is_active: true },
      { id: 2, name: 'Branco', hex: '#f0f0f0', is_active: true },
      { id: 3, name: 'Prata', hex: '#c0c0c0', is_active: true },
      { id: 4, name: 'Dourado', hex: '#c8a44a', is_active: true },
      { id: 5, name: 'Azul', hex: '#1e3a5f', is_active: true },
      { id: 6, name: 'Verde', hex: '#2d5a3d', is_active: true },
      { id: 7, name: 'Rosa Gold', hex: '#b76e79', is_active: true },
      { id: 8, name: 'Laranja', hex: '#d4621a', is_active: true },
    ],
    'gama-de-preco': [
      { id: 1, name: 'Até €100', min: 0, max: 100, is_active: true },
      { id: 2, name: '€100 – €250', min: 100, max: 250, is_active: true },
      { id: 3, name: '€250 – €500', min: 250, max: 500, is_active: true },
      { id: 4, name: 'Acima de €500', min: 500, max: 999999, is_active: true },
    ],
    sexo: [
      { id: 1, name: 'Homens', slug: 'homens', is_active: true },
      { id: 2, name: 'Mulheres', slug: 'mulheres', is_active: true },
      { id: 3, name: 'Unisexo', slug: 'unisexo', is_active: true },
    ],
    genero: [
      { id: 1, name: 'Clássico', slug: 'classicos', is_active: true },
      { id: 2, name: 'Desportivo', slug: 'desporto', is_active: true },
      { id: 3, name: 'Mergulho', slug: 'mergulho', is_active: true },
      { id: 4, name: 'Cronógrafo', slug: 'cronografos', is_active: true },
      { id: 5, name: 'Automático', slug: 'automaticos', is_active: true },
      { id: 6, name: 'Digital', slug: 'digital', is_active: true },
      { id: 7, name: 'Smartwatch', slug: 'smartwatch', is_active: true },
    ],
  })

  function getItemsBySlug(categorySlug: string): CategoryItem[] {
    return itemsMap.value[categorySlug] || []
  }

  function createItem(categorySlug: string, data: Omit<CategoryItem, 'id'>) {
    if (!itemsMap.value[categorySlug]) {
      itemsMap.value[categorySlug] = []
    }
    const categoryItems = itemsMap.value[categorySlug]
    const nextId = categoryItems.length > 0 ? Math.max(...categoryItems.map((item) => item.id)) + 1 : 1
    const newItem: CategoryItem = {
      id: nextId,
      ...data,
    }
    categoryItems.push(newItem)
    return newItem
  }

  function updateItem(categorySlug: string, itemId: number, data: Partial<CategoryItem>) {
    const categoryItems = itemsMap.value[categorySlug]
    if (!categoryItems) return null
    const index = categoryItems.findIndex((item) => item.id === itemId)
    if (index !== -1) {
      categoryItems[index] = {
        ...categoryItems[index],
        ...data,
      }
      return categoryItems[index]
    }
    return null
  }

  function deleteItem(categorySlug: string, itemId: number): boolean {
    const categoryItems = itemsMap.value[categorySlug]
    if (!categoryItems) return false
    const index = categoryItems.findIndex((item) => item.id === itemId)
    if (index !== -1) {
      categoryItems.splice(index, 1)
      return true
    }
    return false
  }

  return {
    itemsMap,
    getItemsBySlug,
    createItem,
    updateItem,
    deleteItem,
  }
})
