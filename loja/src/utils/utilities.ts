export function randomRange(min: number, max: number): number {
	return Math.floor(Math.random() * (max - min + 1)) + min
}

export function capitalize(str: string): string {
	if (str.length === 1) {
		return str.toUpperCase()
	}

	return str.charAt(0).toUpperCase() + str.slice(1)
}

export function resolveProductImageUrl(url?: string, id?: number, sortOrder?: number): string {
	if (!url) {
		return '/images/placeholder.png'
	}
	
	if (url.includes('placeholder.com') || url.includes('placehold.co')) {
		const watchNumber = id ? ((id % 4) + 1) : 1
		const order = sortOrder ?? 1
		
		if (order === 2) {
			return `/products/premium/watch${watchNumber}_side.png`
		} else if (order === 3) {
			return `/products/premium/watch${watchNumber}_detail.png`
		}
		
		return `/products/premium/watch${watchNumber}.png`
	}

	if (url.includes('products/premium/')) {
		const index = url.indexOf('products/premium/')
		return '/' + url.substring(index)
	}
	
	const httpIndex = url.indexOf('http', 4)
	if (httpIndex !== -1) {
		return url.substring(httpIndex)
	}
	
	return url
}

export function getProductImageStyle(id?: number): Record<string, string> {
	if (id) {
		// No-op to satisfy eslint
	}
	return {}
}
