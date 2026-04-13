import bannerImage from '/icons/KIIIBS-banner.png'
import keyboardsImage from '/products/keyboards/cow-full.webp'
import keycapsImage from '/products/keycaps/cap2.webp'
import deskmatsImage from '/products/deskmats/grrr-full.webp'
import { meta, metaContainer } from './meta-types.ts'

export const landingPageMeta: meta = {
	title: 'KIIIBS Mechanical Keyboard Store',
	description:
		'We sell custom mechanical keyboards, keycaps and deskmats for your gaming and office setup, right in the heart of Berlin.',
	image: bannerImage,
}

export const categoryPageMeta: metaContainer = {
	keyboards: {
		title: 'All Mechanical Keyboards | KIIIBS',
		description:
			'Explore our selection of high quality mechanical keyboards that will elevate your typing experience.',
		image: keyboardsImage,
	},
	keycaps: {
		title: 'All Custom Keycaps | KIIIBS',
		description:
			'Enjoy typing with style and upgrade your custom mechanical keyboard with our elegant keycaps',
		image: keycapsImage,
	},
	deskmats: {
		title: 'All Deskmats | KIIIBS',
		description:
			'Make yourself comfortable in your work or gaming setup with one of our KIIIBS deskmats. Soft and stylish!',
		image: deskmatsImage,
	},
}

export const checkoutPageMeta: meta = {
	title: 'Checkout',
	description: 'Complete your purchase.',
	image: bannerImage,
}

export const aboutPageMeta: meta = {
	title: 'Sobre Nos | RELOGIOS.inc',
	description:
		'Conhece a RELOGIOS.inc: uma marca focada em relojoaria de qualidade, design e confianca.',
	image: bannerImage,
}

export const fofPageMeta: meta = {
	title: '404: Page not found.',
	description: '404: Page not found.',
	image: bannerImage,
}

export const loginPageMeta: meta = {
	title: 'Entrar | RELOGIOS.inc',
	description: 'Entra na tua conta RELOGIOS.inc para acederes às tuas encomendas e moradas guardadas.',
	image: bannerImage,
}

export const registerPageMeta: meta = {
	title: 'Criar Conta | RELOGIOS.inc',
	description: 'Cria a tua conta RELOGIOS.inc e junta-te à nossa comunidade de amantes de relojoaria.',
	image: bannerImage,
}

export const forgotPasswordPageMeta: meta = {
	title: 'Recuperar Password | RELOGIOS.inc',
	description: 'Recupera o acesso à tua conta RELOGIOS.inc.',
	image: bannerImage,
}

export const resetPasswordPageMeta: meta = {
	title: 'Nova Password | RELOGIOS.inc',
	description: 'Define uma nova password para a tua conta RELOGIOS.inc.',
	image: bannerImage,
}
