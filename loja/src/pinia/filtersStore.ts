import { defineStore } from 'pinia'
import { ref } from 'vue'

export interface BrandOption {
	name: string
	slug: string
}

export interface CategoryOption {
	name: string
	slug: string
	group: 'tipo' | 'mecanismo' | null
}

/**
 * Opções de filtro dinâmicas das páginas de categoria:
 * - Marcas ← tabela `brands` (GET /api/catalog/brands)
 * - Tipo/Mecanismo ← tabela `categories` com campo `group`
 * Gama de preço e cores são estáticas (definidas no frontend).
 */
export const useFiltersStore = defineStore('filters', () => {
	const loading = ref(false)
	const loaded = ref(false)
	const brands = ref<BrandOption[]>([])
	const categories = ref<CategoryOption[]>([])

	const fetchFilters = async (): Promise<void> => {
		if (loaded.value) return

		loading.value = true
		try {
			const [brandsRes, categoriesRes] = await Promise.all([
				fetch('/api/catalog/brands?per_page=100'),
				fetch('/api/catalog/categories?per_page=100'),
			])

			if (brandsRes.ok) {
				const json = await brandsRes.json()
				brands.value = (json.data ?? []).map(
					(b: { name: string; slug: string }) => ({
						name: b.name,
						slug: b.slug,
					}),
				)
			}

			if (categoriesRes.ok) {
				const json = await categoriesRes.json()
				categories.value = (json.data ?? []).map(
					(c: { name: string; slug: string; group: string | null }) => ({
						name: c.name,
						slug: c.slug,
						group: c.group as CategoryOption['group'],
					}),
				)
			}

			loaded.value = true
		} catch {
			// Mantém listas vazias — os componentes usam o fallback estático
		} finally {
			loading.value = false
		}
	}

	const byGroup = (group: 'tipo' | 'mecanismo'): CategoryOption[] =>
		categories.value.filter((c) => c.group === group)

	return { loading, loaded, brands, categories, fetchFilters, byGroup }
})
