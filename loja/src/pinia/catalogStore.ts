import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Product, PaginatedResponse } from '../data/product-types'
import { useStorage } from '@vueuse/core'

export const useCatalogStore = defineStore('catalog', () => {
	const loading = ref(false)
	const error = ref<string | null>(null)

	const featuredProducts = ref<Product[]>([])
	const recentlyViewed = useStorage<Product[]>('recently_viewed', [])

	// Fetch Destaques (Homepage)
	const fetchFeatured = async (): Promise<Product[]> => {
		if (featuredProducts.value.length > 0) {
			return featuredProducts.value
		}
		loading.value = true
		error.value = null
		try {
			const res = await fetch('/api/catalog/products/featured')
			if (!res.ok) throw new Error('Failed to fetch featured products')
			const json = await res.json()
			featuredProducts.value = json.data as Product[]
			return featuredProducts.value
		} catch (err: unknown) {
			error.value = err instanceof Error ? err.message : String(err)
			return []
		} finally {
			loading.value = false
		}
	}

	// Fetch Lista Paginada e Filtrada (Category page)
	const fetchProducts = async (
		params: Record<string, string | number> = {},
	): Promise<PaginatedResponse<Product> | null> => {
		loading.value = true
		error.value = null
		try {
			const query = new URLSearchParams()
			for (const [key, value] of Object.entries(params)) {
				if (value !== undefined && value !== null && value !== '') {
					query.append(key, String(value))
				}
			}

			const res = await fetch(`/api/catalog/products?${query.toString()}`)
			if (!res.ok) throw new Error('Failed to fetch products')
			const json = await res.json()
			return json as PaginatedResponse<Product>
		} catch (err: unknown) {
			error.value = err instanceof Error ? err.message : String(err)
			return null
		} finally {
			loading.value = false
		}
	}

	// Fetch Detalhe (Product page)
	const fetchProductDetail = async (slug: string): Promise<Product | null> => {
		loading.value = true
		error.value = null
		try {
			const res = await fetch(`/api/catalog/products/${slug}`)
			if (!res.ok) throw new Error('Failed to fetch product details')
			const json = await res.json()
			return json.data as Product
		} catch (err: unknown) {
			error.value = err instanceof Error ? err.message : String(err)
			return null
		} finally {
			loading.value = false
		}
	}

	// Fetch Relacionados (YMAL)
	const fetchRelatedProducts = async (slug: string): Promise<Product[]> => {
		try {
			const res = await fetch(`/api/catalog/products/${slug}/related`)
			if (!res.ok) throw new Error('Failed to fetch related products')
			const json = await res.json()
			return json.data as Product[]
		} catch {
			return []
		}
	}

	const addToRecentlyViewed = (product: Product) => {
		if (!product || !product.slug) return
		// Filter out duplicate if it already exists (checking slug and stringified id for safety)
		const filtered = recentlyViewed.value.filter(
			(p) =>
				p && p.slug !== product.slug && String(p.id) !== String(product.id),
		)
		// Limit to 5 items to show up to 4 other items (filtering out the current one)
		recentlyViewed.value = [product, ...filtered].slice(0, 5)
	}

	return {
		loading,
		error,
		fetchFeatured,
		fetchProducts,
		fetchProductDetail,
		fetchRelatedProducts,
		recentlyViewed,
		addToRecentlyViewed,
	}
})
