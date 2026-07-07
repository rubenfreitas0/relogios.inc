import LandingPage from '../pages/Landing/landing-page.vue'
import { createRouter, createWebHistory } from 'vue-router'
import { categoryRoute, productRoute, handleRouteMeta } from './route-utils'
import {
	get404PageMeta,
	getAboutPageMeta,
	getCheckoutPageMeta,
	getLandingPageMeta,
	getLoginPageMeta,
	getRegisterPageMeta,
	getForgotPasswordPageMeta,
	getResetPasswordPageMeta,
	getAccountPageMeta,
} from '../data/meta-utils'

const routes = [
	{
		path: '/',
		name: 'Home',
		component: LandingPage,
		beforeEnter: () => handleRouteMeta(getLandingPageMeta),
	},
	{
		path: '/checkout',
		component: () => import('../pages/Checkout/checkout-page.vue'),
		beforeEnter: () => handleRouteMeta(getCheckoutPageMeta),
	},
	{
		path: '/sobre-nos',
		name: 'About',
		component: () => import('../pages/About/about-page.vue'),
		beforeEnter: () => handleRouteMeta(getAboutPageMeta),
	},
	categoryRoute('homens'),
	categoryRoute('mulheres'),
	categoryRoute('unisexo'),
	productRoute('homens'),
	productRoute('mulheres'),
	productRoute('unisexo'),
	{
		path: '/conta',
		component: () => import('../pages/Account/account-page.vue'),
		beforeEnter: (_to: any, _from: any, next: any) => {
			handleRouteMeta(getAccountPageMeta)
			const token = localStorage.getItem('auth_token')
			if (!token) {
				next('/login')
			} else {
				next()
			}
		},
		children: [
			{
				path: '',
				redirect: '/conta/perfil',
			},
			{
				path: 'perfil',
				name: 'AccountProfile',
				component: () =>
					import('../pages/Account/Components/AccountProfileTab.vue'),
			},
			{
				path: 'encomendas',
				name: 'AccountOrders',
				component: () =>
					import('../pages/Account/Components/AccountOrdersTab.vue'),
			},
			{
				path: 'encomendas/:orderNumber',
				name: 'AccountOrderDetail',
				component: () =>
					import('../pages/Account/Components/AccountOrderDetailTab.vue'),
			},
			{
				path: 'moradas',
				name: 'AccountAddresses',
				component: () =>
					import('../pages/Account/Components/AccountAddressesTab.vue'),
			},
			{
				path: 'ajuda',
				name: 'AccountHelp',
				component: () =>
					import('../pages/Account/Components/AccountHelpTab.vue'),
			},
		],
	},
	{
		path: '/login',
		name: 'Login',
		component: () => import('../pages/Auth/login-page.vue'),
		beforeEnter: () => handleRouteMeta(getLoginPageMeta),
	},
	{
		path: '/register',
		name: 'Register',
		component: () => import('../pages/Auth/register-page.vue'),
		beforeEnter: () => handleRouteMeta(getRegisterPageMeta),
	},
	{
		path: '/forgot-password',
		name: 'ForgotPassword',
		component: () => import('../pages/Auth/forgot-password-page.vue'),
		beforeEnter: () => handleRouteMeta(getForgotPasswordPageMeta),
	},
	{
		path: '/reset-password',
		name: 'ResetPassword',
		component: () => import('../pages/Auth/reset-password-page.vue'),
		beforeEnter: () => handleRouteMeta(getResetPasswordPageMeta),
	},
	{
		path: '/verificar-email/:id',
		name: 'VerifyEmail',
		component: () => import('../pages/Auth/verify-email-page.vue'),
	},
	{
		path: '/404',
		component: () => import('../pages/404/404-page.vue'),
		beforeEnter: () => handleRouteMeta(get404PageMeta),
	},
	{
		path: '/:pathMatch(.*)',
		component: () => import('../pages/404/404-page.vue'),
		beforeEnter: () => handleRouteMeta(get404PageMeta),
	},
]

const Router = createRouter({
	history: createWebHistory(),
	routes,

	scrollBehavior(_1, _2, savedPosition) {
		if (savedPosition) {
			return savedPosition
		} else {
			return { top: 0 }
		}
	},
})

export default Router
