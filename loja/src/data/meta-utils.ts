import {
	landingPageMeta,
	categoryPageMeta,
	checkoutPageMeta,
	aboutPageMeta,
	fofPageMeta,
	loginPageMeta,
	registerPageMeta,
	forgotPasswordPageMeta,
	resetPasswordPageMeta,
	accountPageMeta,
} from './meta'
import { meta } from './meta-types'
import type { Product } from './product-types.ts'

export function getLandingPageMeta(): meta {
	return landingPageMeta
}

export function getCategoryPageMeta(category: string): meta {
	return categoryPageMeta[category]
}

export function getProductPageMeta(product: Product): meta {
	return {
		title:
			(product.brand?.name || 'Relógio') +
			' ' +
			product.name +
			' | RELOGIOS.inc',
		description: product.short_description || product.description || '',
		image: product.primary_image?.url || '',
	}
}

export function getCheckoutPageMeta(): meta {
	return checkoutPageMeta
}

export function getAboutPageMeta(): meta {
	return aboutPageMeta
}

export function get404PageMeta(): meta {
	return fofPageMeta
}

export function getLoginPageMeta(): meta {
	return loginPageMeta
}

export function getRegisterPageMeta(): meta {
	return registerPageMeta
}

export function getForgotPasswordPageMeta(): meta {
	return forgotPasswordPageMeta
}

export function getResetPasswordPageMeta(): meta {
	return resetPasswordPageMeta
}

export function getAccountPageMeta(): meta {
	return accountPageMeta
}
