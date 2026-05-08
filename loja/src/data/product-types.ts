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

export interface Product {
	id: number
	name: string
	slug: string
	short_description?: string
	description?: string
	price: number | string
	stock: number
	is_active: boolean
	is_featured: boolean
	gender: string
	
	brand?: Brand
	category?: Category
	images?: ProductImage[]
	primary_image?: ProductImage

	// Para compatibilidade temporária com componentes antigos (se necessário)
	features?: string
	inthebox?: any[]
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
