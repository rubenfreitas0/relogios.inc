export function randomRange(min: number, max: number): number {
	return Math.floor(Math.random() * (max - min + 1)) + min
}

export function capitalize(str: string): string {
	if (str.length === 1) {
		return str.toUpperCase()
	}

	return str.charAt(0).toUpperCase() + str.slice(1)
}

export function resolveProductImageUrl(url?: string, id?: number): string {
	if (!url) {
		return '/images/placeholder.png'
	}
	
	if (url.includes('placeholder.com') || url.includes('placehold.co')) {
		const localWatches = [
			'/products/premium/watch1.png',
			'/products/premium/watch2.png',
			'/products/premium/watch3.png',
			'/products/premium/watch4.png'
		]
		const index = id ? (id % localWatches.length) : 0
		return localWatches[index]
	}
	
	const httpIndex = url.indexOf('http', 4)
	if (httpIndex !== -1) {
		return url.substring(httpIndex)
	}
	
	return url
}
