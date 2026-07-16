export interface Brand {
	id: number
	name: string
	slug: string
	logo?: string
}

export interface Category {
	id: number
	name: string
	slug: string
}

export interface ProductImage {
	id: number
	url: string
	sort_order: number
	is_primary: boolean
}

export interface BoxItem {
	count?: number
	content: string
}

export interface Product {
	id: number
	name: string
	slug: string
	short_description?: string
	description?: string
	price: number | string
	discount_price?: number | string | null
	stock: number
	is_active: boolean
	is_featured: boolean
	gender: string

	brand?: Brand
	category?: Category
	categories?: Category[]
	images?: ProductImage[]
	primary_image?: ProductImage
	weight?: number | string

	// Para compatibilidade temporária com componentes antigos (se necessário)
	features?: string
	inthebox?: (string | BoxItem)[]
	in_the_box?: (string | BoxItem)[]
}

export interface PaginatedResponse<T> {
	data: T[]
	meta: {
		current_page: number
		last_page: number
		per_page: number
		total: number
	}
}
