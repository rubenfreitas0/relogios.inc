import { RouteLocationNormalized, NavigationGuardNext } from 'vue-router'
import { useSeoMeta } from '@unhead/vue'
import { meta } from '../data/meta-types'
import { getCategoryPageMeta } from '../data/meta-utils'

export function handleRouteMeta(metaFunc: () => meta): void {
	const metaData = metaFunc()
	useSeoMeta({
		title: metaData.title,
		description: metaData.description,
		ogTitle: metaData.title,
		ogDescription: metaData.description,
		ogImage: metaData.image,
	})
}

export function categoryRoute(category: string) {
	return {
		path: `/${category}`,
		component: () => import('../pages/Category/category-page.vue'),
		props: { category: category },
		beforeEnter: () => {
			const meta = getCategoryPageMeta(category)
			useSeoMeta({
				title: meta.title,
				description: meta.description,
				ogTitle: meta.title,
				ogDescription: meta.description,
				ogImage: meta.image,
			})
		},
	}
}

export function productRoute(category: string) {
	return {
		path: `/${category}/:slug`,
		name: category,
		component: () => import('../pages/Product/product-page.vue'),
		props: (route: any) => ({
			category: category,
			productSlug: route.params.slug,
		}),
		beforeEnter: (
			_to: RouteLocationNormalized,
			_from: RouteLocationNormalized,
			next: NavigationGuardNext,
		) => {
			// A validação e metadados serão geridos pelo componente product-page.vue após carregar da API
			next()
		},
	}
}
