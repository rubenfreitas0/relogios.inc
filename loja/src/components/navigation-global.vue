<script setup lang="ts">
import cartIcon from '/icons/cart-icon.svg'
import Cart from './Cart/cart-modal.vue'
import { computed, ref, onMounted } from 'vue'
import { useCartStore } from '../pinia/cartStore.ts'
import { useAuthStore } from '../pinia/authStore.ts'
import { useRouter, useRoute } from 'vue-router'

interface Props {
	color?: 'black' | 'transparent' | 'k-black'
}

const props = withDefaults(defineProps<Props>(), {
	color: 'transparent',
})

const cartStore = useCartStore()
const authStore = useAuthStore()
const router = useRouter()
const route = useRoute()

const isAuthPage = computed(() =>
	['/login', '/register', '/forgot-password', '/reset-password'].some(
		(p) => route.path === p || route.path.startsWith(p + '/'),
	),
)

let style = computed(() => {
	return 'bg-' + props.color
})

const hamburgerState = ref('hide')
const userMenuOpen = ref(false)
const activeMega = ref<string | null>(null)
let megaLeaveTimeout: ReturnType<typeof setTimeout> | null = null

function showHamburger(): void {
	hamburgerState.value = 'show'
}

function hideHamburger(): void {
	hamburgerState.value = 'hide'
}

async function handleLogout(): Promise<void> {
	userMenuOpen.value = false
	await authStore.logout()
	router.push('/')
}

function openMega(key: string) {
	if (megaLeaveTimeout) clearTimeout(megaLeaveTimeout)
	activeMega.value = key
}

function closeMegaDelayed() {
	megaLeaveTimeout = setTimeout(() => {
		activeMega.value = null
	}, 120)
}

function keepMega() {
	if (megaLeaveTimeout) clearTimeout(megaLeaveTimeout)
}

// ── Mega menu data ────────────────────────────────────────────────
interface MegaPrice {
	label: string
	min: number
	max: number
}

const megaMenus: Record<
	string,
	{
		brands: string[]
		types: string[]
		kind: string[]
		prices: MegaPrice[]
		colors: { name: string; hex: string }[]
	}
> = {
	homens: {
		brands: [
			'Casio',
			'Seiko',
			'Citizen',
			'Orient',
			'Tissot',
			'Festina',
			'G-Shock',
			'Hugo Boss',
		],
		types: [
			'Clássico',
			'Desportivo',
			'Casual',
			'Mergulho',
			'Aviador',
			'Cronógrafo',
			'Militar',
		],
		kind: ['Analógico', 'Digital', 'Analógico-Digital', 'Smartwatch'],
		prices: [
			{ label: 'Até €100', min: 0, max: 100 },
			{ label: '€100 – €250', min: 100, max: 250 },
			{ label: '€250 – €500', min: 250, max: 500 },
			{ label: 'Acima de €500', min: 500, max: 999999 },
		],
		colors: [
			{ name: 'Preto', hex: '#1a1a1a' },
			{ name: 'Prata', hex: '#c0c0c0' },
			{ name: 'Dourado', hex: '#c8a44a' },
			{ name: 'Azul', hex: '#1e3a5f' },
			{ name: 'Verde', hex: '#2d5a3d' },
			{ name: 'Branco', hex: '#f0f0f0' },
		],
	},
	mulheres: {
		brands: [
			'Casio',
			'Citizen',
			'Michael Kors',
			'Anne Klein',
			'Festina',
			'Tissot',
			'Cluse',
			'Fossil',
		],
		types: [
			'Clássico',
			'Elegante',
			'Casual',
			'Desportivo',
			'Minimalista',
			'Cronógrafo',
		],
		kind: ['Analógico', 'Digital', 'Smartwatch'],
		prices: [
			{ label: 'Até €80', min: 0, max: 80 },
			{ label: '€80 – €200', min: 80, max: 200 },
			{ label: '€200 – €450', min: 200, max: 450 },
			{ label: 'Acima de €450', min: 450, max: 999999 },
		],
		colors: [
			{ name: 'Dourado', hex: '#c8a44a' },
			{ name: 'Rosa Gold', hex: '#b76e79' },
			{ name: 'Prata', hex: '#c0c0c0' },
			{ name: 'Branco', hex: '#f0f0f0' },
			{ name: 'Preto', hex: '#1a1a1a' },
			{ name: 'Rose', hex: '#e8a0a0' },
		],
	},
	unisexo: {
		brands: [
			'Casio',
			'Swatch',
			'Timex',
			'Orient',
			'Seiko',
			'Garmin',
			'Apple',
			'Samsung',
		],
		types: [
			'Casual',
			'Desportivo',
			'Smartwatch',
			'Minimalista',
			'Vintage',
			'Outdoor',
		],
		kind: ['Analógico', 'Digital', 'Smartwatch', 'Híbrido'],
		prices: [
			{ label: 'Até €80', min: 0, max: 80 },
			{ label: '€80 – €200', min: 80, max: 200 },
			{ label: '€200 – €500', min: 200, max: 500 },
			{ label: 'Acima de €500', min: 500, max: 999999 },
		],
		colors: [
			{ name: 'Preto', hex: '#1a1a1a' },
			{ name: 'Branco', hex: '#f0f0f0' },
			{ name: 'Prata', hex: '#c0c0c0' },
			{ name: 'Laranja', hex: '#d4621a' },
			{ name: 'Verde', hex: '#2d5a3d' },
			{ name: 'Azul', hex: '#1e3a5f' },
		],
	},
}

onMounted(async () => {
	if (authStore.token && !authStore.user) {
		await authStore.fetchUser()
	}
})

const categorySlugMap: Record<string, string> = {
	Clássico: 'classicos',
	Desportivo: 'desporto',
	Casual: 'casual',
	Mergulho: 'mergulho',
	Aviador: 'aviador',
	Cronógrafo: 'cronografos',
	Militar: 'militar',
	Analógico: 'analogico',
	Digital: 'digital',
	'Analógico-Digital': 'analogico-digital',
	Smartwatch: 'smartwatch',
	Minimalista: 'classicos', // fallback ou mapeamento aproximado
	Vintage: 'classicos',
	Outdoor: 'desporto',
	Híbrido: 'automaticos',
	Elegante: 'classicos',
}

function getCategorySlug(name: string): string {
	return (
		categorySlugMap[name] ||
		name
			.toLowerCase()
			.normalize('NFD')
			.replace(/[\u0300-\u036f]/g, '')
	)
}
</script>

<template>
	<header
		id="navi"
		class="main-container relative z-50 flex h-full w-screen flex-col items-center"
		:class="style"
		data-test="nav-desktop"
	>
		<div
			class="relative flex w-4/5 max-w-6xl flex-row items-center justify-between border-b border-zinc-500 py-6 md:w-11/12 lg:w-4/5"
		>
			<!-- Esquerda: hamburger (mobile) + logo -->
			<button
				id="hamburger"
				class="select-none lg:hidden"
				@click="showHamburger()"
				data-test="hamburger"
			>
				<svg
					xmlns="http://www.w3.org/2000/svg"
					fill="none"
					viewBox="0 0 24 24"
					stroke-width="2"
					stroke="currentColor"
					class="h-8 w-8"
				>
					<path
						stroke-linecap="round"
						stroke-linejoin="round"
						d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"
					/>
				</svg>
			</button>

			<router-link
				to="/"
				class="text-3xl font-extrabold tracking-tight antialiased transition duration-300 hover:scale-110 hover:text-k-main"
				data-test="nav-logo"
			>
				RELOGIOS.inc
			</router-link>

			<!-- Centro: links de navegação (apenas desktop) -->
			<nav class="hidden tracking-widest lg:flex lg:items-center lg:gap-8">
				<router-link
					to="/"
					class="text-sm uppercase text-white transition duration-300 hover:text-k-main active:translate-y-0.5"
					data-test="nav-home"
					>Home</router-link
				>

				<!-- Mega: Homens -->
				<div
					class="relative"
					@mouseenter="openMega('homens')"
					@mouseleave="closeMegaDelayed()"
				>
					<router-link
						to="/homens"
						class="flex items-center gap-1 text-sm uppercase text-white transition duration-300 hover:text-k-main"
						:class="activeMega === 'homens' ? 'text-k-main' : ''"
						@click="activeMega = null"
						data-test="nav-homens"
					>
						Homens
						<svg
							class="h-3 w-3 transition-transform duration-200"
							:class="activeMega === 'homens' ? 'rotate-180' : ''"
							fill="none"
							viewBox="0 0 24 24"
							stroke="currentColor"
						>
							<path
								stroke-linecap="round"
								stroke-linejoin="round"
								stroke-width="2.5"
								d="M19 9l-7 7-7-7"
							/>
						</svg>
					</router-link>
				</div>

				<!-- Mega: Mulheres -->
				<div
					class="relative"
					@mouseenter="openMega('mulheres')"
					@mouseleave="closeMegaDelayed()"
				>
					<router-link
						to="/mulheres"
						class="flex items-center gap-1 text-sm uppercase text-white transition duration-300 hover:text-k-main"
						:class="activeMega === 'mulheres' ? 'text-k-main' : ''"
						@click="activeMega = null"
						data-test="nav-mulheres"
					>
						Mulheres
						<svg
							class="h-3 w-3 transition-transform duration-200"
							:class="activeMega === 'mulheres' ? 'rotate-180' : ''"
							fill="none"
							viewBox="0 0 24 24"
							stroke="currentColor"
						>
							<path
								stroke-linecap="round"
								stroke-linejoin="round"
								stroke-width="2.5"
								d="M19 9l-7 7-7-7"
							/>
						</svg>
					</router-link>
				</div>

				<!-- Mega: Unisexo -->
				<div
					class="relative"
					@mouseenter="openMega('unisexo')"
					@mouseleave="closeMegaDelayed()"
				>
					<router-link
						to="/unisexo"
						class="flex items-center gap-1 text-sm uppercase text-white transition duration-300 hover:text-k-main"
						:class="activeMega === 'unisexo' ? 'text-k-main' : ''"
						@click="activeMega = null"
						data-test="nav-unisexo"
					>
						Unisexo
						<svg
							class="h-3 w-3 transition-transform duration-200"
							:class="activeMega === 'unisexo' ? 'rotate-180' : ''"
							fill="none"
							viewBox="0 0 24 24"
							stroke="currentColor"
						>
							<path
								stroke-linecap="round"
								stroke-linejoin="round"
								stroke-width="2.5"
								d="M19 9l-7 7-7-7"
							/>
						</svg>
					</router-link>
				</div>

				<router-link
					to="/sobre-nos"
					class="text-sm uppercase text-white transition duration-300 hover:text-k-main active:translate-y-0.5"
					data-test="nav-sobre-nos"
					>Sobre Nós</router-link
				>
			</nav>

			<!-- Direita: Auth + Carrinho -->
			<div class="flex items-center gap-4">
				<!-- === LOGADO: Avatar + dropdown === -->
				<div
					v-if="authStore.user"
					class="relative hidden lg:block"
					data-test="user-menu"
				>
					<button
						@click="userMenuOpen = !userMenuOpen"
						class="group flex items-center gap-2 transition duration-200"
					>
						<div
							class="flex h-8 w-8 select-none items-center justify-center rounded-full bg-k-main text-xs font-black tracking-tight text-k-black"
						>
							{{ (authStore.user?.firstname?.charAt(0) || '').toUpperCase()
							}}{{ (authStore.user?.lastname?.charAt(0) || '').toUpperCase() }}
						</div>
						<span
							class="hidden text-xs uppercase tracking-wider text-white/70 transition duration-200 group-hover:text-white xl:block"
						>
							{{ authStore.user.firstname }}
						</span>
						<svg
							class="h-3 w-3 text-white/40 transition duration-200"
							:class="userMenuOpen ? 'rotate-180' : ''"
							fill="none"
							viewBox="0 0 24 24"
							stroke="currentColor"
						>
							<path
								stroke-linecap="round"
								stroke-linejoin="round"
								stroke-width="2"
								d="M19 9l-7 7-7-7"
							/>
						</svg>
					</button>

					<Transition name="dropdown">
						<div
							v-if="userMenuOpen"
							class="absolute right-0 top-12 z-50 w-52 overflow-hidden rounded-xl border border-white/10 bg-k-dark-grey shadow-2xl"
						>
							<div class="border-b border-white/10 px-4 py-3">
								<p class="text-sm font-semibold text-white">
									{{ authStore.user.firstname }} {{ authStore.user.lastname }}
								</p>
								<p class="mt-0.5 truncate text-xs text-white/40">
									{{ authStore.user.email }}
								</p>
							</div>
							<div class="p-1.5">
								<router-link
									to="/conta"
									@click="userMenuOpen = false"
									class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-white/60 transition duration-200 hover:bg-[#FFC700]/5 hover:text-[#FFC700]"
									data-test="nav-account"
								>
									<svg
										class="h-4 w-4"
										fill="none"
										viewBox="0 0 24 24"
										stroke="currentColor"
									>
										<path
											stroke-linecap="round"
											stroke-linejoin="round"
											stroke-width="2"
											d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
										/>
									</svg>
									A Minha Conta
								</router-link>
								<router-link
									to="/conta/encomendas"
									@click="userMenuOpen = false"
									class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-white/60 transition duration-200 hover:bg-[#FFC700]/5 hover:text-[#FFC700]"
									data-test="nav-orders"
								>
									<svg
										class="h-4 w-4"
										fill="none"
										viewBox="0 0 24 24"
										stroke="currentColor"
									>
										<path
											stroke-linecap="round"
											stroke-linejoin="round"
											stroke-width="2"
											d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"
										/>
									</svg>
									As Minhas Encomendas
								</router-link>
								<button
									@click="handleLogout"
									class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-white/60 transition duration-200 hover:bg-red-500/10 hover:text-red-400"
									data-test="nav-logout"
								>
									<svg
										class="h-4 w-4"
										fill="none"
										viewBox="0 0 24 24"
										stroke="currentColor"
									>
										<path
											stroke-linecap="round"
											stroke-linejoin="round"
											stroke-width="2"
											d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
										/>
									</svg>
									Terminar sessão
								</button>
							</div>
						</div>
					</Transition>

					<div
						v-if="userMenuOpen"
						class="fixed inset-0 z-40"
						@click="userMenuOpen = false"
					></div>
				</div>

				<!-- === NÃO LOGADO: Botões Auth (desktop) === -->
				<div
					v-else
					class="hidden items-center gap-2 lg:flex"
					data-test="auth-buttons"
				>
					<router-link
						to="/login"
						class="px-3 py-1.5 text-xs uppercase tracking-wider text-white/70 transition duration-200 hover:text-white"
						data-test="nav-login"
					>
						Entrar
					</router-link>
					<router-link
						to="/register"
						class="flex items-center gap-1.5 rounded-full bg-k-main px-4 py-2 text-xs font-bold uppercase tracking-wider text-k-black shadow-md shadow-k-main/20 transition duration-200 hover:bg-yellow-400 active:scale-95"
						data-test="nav-register"
					>
						<svg
							class="h-3 w-3"
							fill="none"
							viewBox="0 0 24 24"
							stroke="currentColor"
						>
							<path
								stroke-linecap="round"
								stroke-linejoin="round"
								stroke-width="2.5"
								d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
							/>
						</svg>
						Criar Conta
					</router-link>
				</div>

				<!-- Carrinho -->
				<div
					v-if="!isAuthPage"
					class="relative h-5 cursor-pointer"
					@click="cartStore.cartOn()"
					data-test="cart-button"
				>
					<img
						class="h-full origin-center hover:opacity-50 active:translate-y-0.5"
						:class="{ 'animate-bump': cartStore.isBumping }"
						:src="cartIcon"
						alt="Cart Icon"
					/>
					<Transition>
						<div
							v-show="cartStore.cartLength !== 0"
							class="absolute -right-2 top-3 flex h-4 w-4 flex-col items-center justify-center rounded-full bg-red-600 text-xs font-black transition-all duration-300"
							data-test="cart-bubble"
						>
							{{ cartStore.cartLength }}
						</div>
					</Transition>
				</div>
			</div>
		</div>

		<!-- ── MEGA MENU PANEL ─────────────────────────────────────── -->
		<Transition name="mega">
			<div
				v-if="activeMega && megaMenus[activeMega]"
				class="absolute left-0 top-full z-40 w-screen border-b border-t border-white/10 bg-[#111] shadow-2xl"
				@mouseenter="keepMega()"
				@mouseleave="closeMegaDelayed()"
			>
				<div class="mx-auto grid max-w-6xl grid-cols-5 gap-8 px-6 py-8">
					<!-- Marcas Populares -->
					<div>
						<p
							class="mb-4 text-[0.6rem] font-bold uppercase tracking-[0.2em] text-[#FFC700]"
						>
							Marcas Populares
						</p>
						<ul class="space-y-2">
							<li v-for="brand in megaMenus[activeMega!].brands" :key="brand">
								<router-link
									:to="`/${activeMega}?brand=${brand.toLowerCase()}`"
									class="block text-sm text-white/60 transition-all duration-150 hover:translate-x-1 hover:text-white"
									@click="activeMega = null"
								>
									{{ brand }}
								</router-link>
							</li>
						</ul>
						<router-link
							:to="`/${activeMega}`"
							class="mt-4 block text-[0.65rem] font-bold uppercase tracking-wider text-[#FFC700] transition-colors hover:text-yellow-300"
							@click="activeMega = null"
							>Ver todas as marcas →</router-link
						>
					</div>

					<!-- Tipos de Relógio -->
					<div>
						<p
							class="mb-4 text-[0.6rem] font-bold uppercase tracking-[0.2em] text-[#FFC700]"
						>
							Tipo de Relógio
						</p>
						<ul class="space-y-2">
							<li v-for="t in megaMenus[activeMega!].types" :key="t">
								<router-link
									:to="`/${activeMega}?category=${getCategorySlug(t)}`"
									class="block text-sm text-white/60 transition-all duration-150 hover:translate-x-1 hover:text-white"
									@click="activeMega = null"
								>
									{{ t }}
								</router-link>
							</li>
						</ul>
					</div>

					<!-- Analógico / Smartwatch -->
					<div>
						<p
							class="mb-4 text-[0.6rem] font-bold uppercase tracking-[0.2em] text-[#FFC700]"
						>
							Mecanismo
						</p>
						<ul class="space-y-2">
							<li v-for="k in megaMenus[activeMega!].kind" :key="k">
								<router-link
									:to="`/${activeMega}?category=${getCategorySlug(k)}`"
									class="block text-sm text-white/60 transition-all duration-150 hover:translate-x-1 hover:text-white"
									@click="activeMega = null"
								>
									{{ k }}
								</router-link>
							</li>
						</ul>
					</div>

					<!-- Gama de Preços -->
					<div>
						<p
							class="mb-4 text-[0.6rem] font-bold uppercase tracking-[0.2em] text-[#FFC700]"
						>
							Gama de Preço
						</p>
						<ul class="space-y-2">
							<li v-for="p in megaMenus[activeMega!].prices" :key="p.label">
								<router-link
									:to="`/${activeMega}?min_price=${p.min}&max_price=${p.max}`"
									class="block text-sm text-white/60 transition-all duration-150 hover:translate-x-1 hover:text-white"
									@click="activeMega = null"
								>
									{{ p.label }}
								</router-link>
							</li>
						</ul>
					</div>

					<!-- Cores -->
					<div>
						<p
							class="mb-4 text-[0.6rem] font-bold uppercase tracking-[0.2em] text-[#FFC700]"
						>
							Cores
						</p>
						<div class="flex flex-wrap gap-3">
							<button
								v-for="c in megaMenus[activeMega!].colors"
								:key="c.name"
								:title="c.name"
								class="group flex flex-col items-center gap-1.5"
								@click="activeMega = null"
							>
								<span
									class="block h-7 w-7 rounded-full border-2 border-white/20 transition-colors duration-150 hover:border-[#FFC700]"
									:style="{ backgroundColor: c.hex }"
								></span>
								<span
									class="text-[0.6rem] text-white/40 transition-colors group-hover:text-white/70"
									>{{ c.name }}</span
								>
							</button>
						</div>
					</div>
				</div>
			</div>
		</Transition>

		<Cart v-if="cartStore.showCart" />

		<!-- Menu Mobile (hamburger) -->
		<transition>
			<nav
				class="absolute z-50 flex w-screen flex-col items-center gap-4 bg-black p-9 text-sm font-semibold tracking-widest"
				v-if="hamburgerState === 'show'"
				:class="$route.path === '/' ? 'bg-k-black' : 'bg-black'"
				data-test="nav-mobile"
			>
				<button
					class="uppercase text-white transition duration-300 hover:text-k-main active:translate-y-0.5"
					@click="hideHamburger()"
					data-test="close-hamburger"
				>
					Fechar ✕
				</button>
				<router-link
					to="/"
					class="uppercase text-white transition duration-300 hover:text-k-main active:translate-y-0.5"
					:class="$route.path === '/' && 'hidden'"
					@click="hideHamburger()"
					data-test="mobile-nav-home"
					>Home</router-link
				>
				<router-link
					to="/homens"
					class="uppercase text-white transition duration-300 hover:text-k-main active:translate-y-0.5"
					@click="hideHamburger()"
					data-test="mobile-nav-homens"
					>Homens</router-link
				>
				<router-link
					to="/mulheres"
					class="uppercase text-white transition duration-300 hover:text-k-main active:translate-y-0.5"
					@click="hideHamburger()"
					data-test="mobile-nav-mulheres"
					>Mulheres</router-link
				>
				<router-link
					to="/unisexo"
					class="uppercase text-white transition duration-300 hover:text-k-main active:translate-y-0.5"
					@click="hideHamburger()"
					data-test="mobile-nav-unisexo"
					>Unisexo</router-link
				>
				<router-link
					to="/sobre-nos"
					class="uppercase text-white transition duration-300 hover:text-k-main active:translate-y-0.5"
					:class="$route.path === '/sobre-nos' && 'hidden'"
					@click="hideHamburger()"
					data-test="mobile-nav-sobre-nos"
					>Sobre Nós</router-link
				>

				<!-- Divisor -->
				<div class="my-1 h-px w-16 bg-white/10"></div>

				<!-- Auth mobile -->
				<template v-if="authStore.user">
					<div class="text-center">
						<div
							class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-k-main text-sm font-black text-k-black"
						>
							{{ (authStore.user?.firstname?.charAt(0) || '').toUpperCase()
							}}{{ (authStore.user?.lastname?.charAt(0) || '').toUpperCase() }}
						</div>
						<p class="text-xs text-white/60">
							{{ authStore.user.firstname }} {{ authStore.user.lastname }}
						</p>
					</div>
					<router-link
						to="/conta"
						class="uppercase text-[#FFC700]/80 transition duration-300 hover:text-[#FFC700]"
						@click="hideHamburger()"
						data-test="mobile-nav-account"
						>A Minha Conta</router-link
					>
					<router-link
						to="/conta/encomendas"
						class="uppercase text-[#FFC700]/80 transition duration-300 hover:text-[#FFC700]"
						@click="hideHamburger()"
						data-test="mobile-nav-orders"
						>As Minhas Encomendas</router-link
					>
					<button
						@click="handleLogout(); hideHamburger();"
						class="uppercase text-red-400/80 transition duration-300 hover:text-red-400"
						data-test="mobile-nav-logout"
					>
						Terminar Sessão
					</button>
				</template>
				<template v-else>
					<router-link
						to="/login"
						class="uppercase text-white/70 transition duration-300 hover:text-white"
						@click="hideHamburger()"
						data-test="mobile-nav-login"
						>Entrar</router-link
					>
					<router-link
						to="/register"
						class="flex items-center gap-2 rounded-full bg-k-main px-6 py-2.5 font-bold uppercase text-k-black transition duration-200 hover:bg-yellow-400"
						@click="hideHamburger()"
						data-test="mobile-nav-register"
						>Criar Conta</router-link
					>
				</template>
			</nav>
		</transition>
	</header>
</template>

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active {
	transition: all 0.2s ease;
}
.dropdown-enter-from,
.dropdown-leave-to {
	opacity: 0;
	transform: translateY(-8px) scale(0.97);
}

.mega-enter-active,
.mega-leave-active {
	transition: all 0.2s ease;
}
.mega-enter-from,
.mega-leave-to {
	opacity: 0;
	transform: translateY(-6px);
}
</style>
