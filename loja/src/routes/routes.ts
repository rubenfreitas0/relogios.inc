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
		path: '/404',
		component: () => import('../pages/404/404-page.vue'),
		beforeEnter: () => handleRouteMeta(get404PageMeta),
	},
	{
		path: '/:pathMatch(.*)',
		component: () => import('../pages/404/404-page.vue'),
		beforeEnter: () => handleRouteMeta(get404PageMeta),
	},
	{
		path: '/deskmats',
		redirect: '/sobre-nos',
	},
	{
		path: '/sobre-nos',
		name: 'About',
		component: () => import('../pages/About/about-page.vue'),
		beforeEnter: () => handleRouteMeta(getAboutPageMeta),
	},
	categoryRoute('keyboards'),
	categoryRoute('keycaps'),
	productRoute('keyboards'),
	productRoute('keycaps'),
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
