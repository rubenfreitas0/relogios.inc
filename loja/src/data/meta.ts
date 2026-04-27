import bannerImage from '/icons/KIIIBS-banner.png'
import keyboardsImage from '/products/keyboards/cow-full.webp'
import keycapsImage from '/products/keycaps/cap2.webp'
import deskmatsImage from '/products/deskmats/grrr-full.webp'
import { meta, metaContainer } from './meta-types.ts'

export const landingPageMeta: meta = {
	title: 'RELOGIOS.inc | Loja de Relógios Online',
	description:
		'Explora a nossa coleção exclusiva de relógios de marcas prestigiadas. Precisão, design e confiança no coração de Portugal.',
	image: bannerImage,
}

export const categoryPageMeta: metaContainer = {
	homens: {
		title: 'Relógios de Homem | RELOGIOS.inc',
		description:
			'Descobre a nossa seleção de relógios masculinos que combinam robustez, precisão e elegância.',
		image: keyboardsImage,
	},
	mulheres: {
		title: 'Relógios de Mulher | RELOGIOS.inc',
		description:
			'Elegância e sofisticação em cada detalhe. Escolhe o relógio perfeito para o teu estilo.',
		image: keycapsImage,
	},
	unisexo: {
		title: 'Relógios Unisexo | RELOGIOS.inc',
		description:
			'Design versátil para todos. Explora a nossa gama de relógios casuais e desportivos.',
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
