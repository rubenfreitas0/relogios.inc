<script setup lang="ts">
import cartIcon from '/icons/cart-icon.svg'
import Cart from './Cart/cart-modal.vue'
import { computed, ref } from 'vue'
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
	['/login', '/register', '/forgot-password', '/reset-password'].some(p =>
		route.path === p || route.path.startsWith(p + '/')
	)
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
const megaMenus: Record<string, {
	brands: string[]
	types: string[]
	kind: string[]
	prices: string[]
	colors: { name: string; hex: string }[]
}> = {
	homens: {
		brands: ['Casio', 'Seiko', 'Citizen', 'Orient', 'Tissot', 'Festina', 'G-Shock', 'Hugo Boss'],
		types: ['Clássico', 'Desportivo', 'Casual', 'Mergulho', 'Aviador', 'Cronógrafo', 'Militar'],
		kind: ['Analógico', 'Digital', 'Analógico-Digital', 'Smartwatch'],
		prices: ['Até €100', '€100 – €250', '€250 – €500', 'Acima de €500'],
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
		brands: ['Casio', 'Citizen', 'Michael Kors', 'Anne Klein', 'Festina', 'Tissot', 'Cluse', 'Fossil'],
		types: ['Clássico', 'Elegante', 'Casual', 'Desportivo', 'Minimalista', 'Cronógrafo'],
		kind: ['Analógico', 'Digital', 'Smartwatch'],
		prices: ['Até €80', '€80 – €200', '€200 – €450', 'Acima de €450'],
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
		brands: ['Casio', 'Swatch', 'Timex', 'Orient', 'Seiko', 'Garmin', 'Apple', 'Samsung'],
		types: ['Casual', 'Desportivo', 'Smartwatch', 'Minimalista', 'Vintage', 'Outdoor'],
		kind: ['Analógico', 'Digital', 'Smartwatch', 'Híbrido'],
		prices: ['Até €80', '€80 – €200', '€200 – €500', 'Acima de €500'],
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
</script>

<template>
	<header
		id="navi"
		class="main-container flex h-full w-screen flex-col items-center relative z-50"
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
				<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-8 w-8">
					<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
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
			<nav class="hidden tracking-widest lg:flex lg:gap-8 lg:items-center">
				<router-link
					to="/"
					class="uppercase text-white transition duration-300 hover:text-k-main active:translate-y-0.5 text-sm"
					data-test="nav-home"
				>Home</router-link>

				<!-- Mega: Homens -->
				<div
					class="relative"
					@mouseenter="openMega('homens')"
					@mouseleave="closeMegaDelayed()"
				>
					<router-link
						to="/keyboards?genero=homens"
						class="uppercase text-white transition duration-300 hover:text-k-main text-sm flex items-center gap-1"
						:class="activeMega === 'homens' ? 'text-k-main' : ''"
						@click="activeMega = null"
					>
						Homens
						<svg class="w-3 h-3 transition-transform duration-200" :class="activeMega === 'homens' ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
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
						to="/keyboards?genero=mulheres"
						class="uppercase text-white transition duration-300 hover:text-k-main text-sm flex items-center gap-1"
						:class="activeMega === 'mulheres' ? 'text-k-main' : ''"
						@click="activeMega = null"
					>
						Mulheres
						<svg class="w-3 h-3 transition-transform duration-200" :class="activeMega === 'mulheres' ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
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
						to="/keyboards?genero=unisexo"
						class="uppercase text-white transition duration-300 hover:text-k-main text-sm flex items-center gap-1"
						:class="activeMega === 'unisexo' ? 'text-k-main' : ''"
						@click="activeMega = null"
					>
						Unisexo
						<svg class="w-3 h-3 transition-transform duration-200" :class="activeMega === 'unisexo' ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
						</svg>
					</router-link>
				</div>

				<router-link
					to="/sobre-nos"
					class="uppercase text-white transition duration-300 hover:text-k-main active:translate-y-0.5 text-sm"
					data-test="nav-deskmats"
				>Sobre Nós</router-link>
			</nav>

			<!-- Direita: Auth + Carrinho -->
			<div class="flex items-center gap-4">

				<!-- === LOGADO: Avatar + dropdown === -->
				<div v-if="authStore.user" class="relative hidden lg:block" data-test="user-menu">
					<button @click="userMenuOpen = !userMenuOpen" class="flex items-center gap-2 group transition duration-200">
						<div class="flex items-center justify-center w-8 h-8 rounded-full bg-k-main text-k-black text-xs font-black tracking-tight select-none">
							{{ authStore.user.firstname.charAt(0).toUpperCase() }}{{ authStore.user.lastname.charAt(0).toUpperCase() }}
						</div>
						<span class="text-white/70 text-xs uppercase tracking-wider group-hover:text-white transition duration-200 hidden xl:block">
							{{ authStore.user.firstname }}
						</span>
						<svg class="w-3 h-3 text-white/40 transition duration-200" :class="userMenuOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
						</svg>
					</button>

					<Transition name="dropdown">
						<div v-if="userMenuOpen" class="absolute right-0 top-12 w-52 bg-k-dark-grey border border-white/10 rounded-xl shadow-2xl overflow-hidden z-50">
							<div class="px-4 py-3 border-b border-white/10">
								<p class="text-white text-sm font-semibold">{{ authStore.user.firstname }} {{ authStore.user.lastname }}</p>
								<p class="text-white/40 text-xs mt-0.5 truncate">{{ authStore.user.email }}</p>
							</div>
							<div class="p-1.5">
								<button
									@click="handleLogout"
									class="w-full flex items-center gap-2.5 px-3 py-2 text-sm text-white/60 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition duration-200"
									data-test="nav-logout"
								>
									<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
									</svg>
									Terminar sessão
								</button>
							</div>
						</div>
					</Transition>

					<div v-if="userMenuOpen" class="fixed inset-0 z-40" @click="userMenuOpen = false"></div>
				</div>

				<!-- === NÃO LOGADO: Botões Auth (desktop) === -->
				<div v-else class="hidden lg:flex items-center gap-2" data-test="auth-buttons">
					<router-link to="/login" class="text-white/70 text-xs uppercase tracking-wider hover:text-white transition duration-200 px-3 py-1.5" data-test="nav-login">
						Entrar
					</router-link>
					<router-link
						to="/register"
						class="flex items-center gap-1.5 bg-k-main text-k-black text-xs font-bold uppercase tracking-wider px-4 py-2 rounded-full hover:bg-yellow-400 active:scale-95 transition duration-200 shadow-md shadow-k-main/20"
						data-test="nav-register"
					>
						<svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
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
						class="h-full hover:opacity-50 active:translate-y-0.5 origin-center"
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
				class="absolute top-full left-0 w-screen bg-[#111] border-t border-b border-white/10 shadow-2xl z-40"
				@mouseenter="keepMega()"
				@mouseleave="closeMegaDelayed()"
			>
				<div class="max-w-6xl mx-auto px-6 py-8 grid grid-cols-5 gap-8">

					<!-- Marcas Populares -->
					<div>
						<p class="text-[0.6rem] font-bold tracking-[0.2em] uppercase text-[#FFC700] mb-4">Marcas Populares</p>
						<ul class="space-y-2">
							<li v-for="brand in megaMenus[activeMega!].brands" :key="brand">
								<router-link
									:to="`/keyboards?brand=${brand.toLowerCase()}`"
									class="text-sm text-white/60 hover:text-white hover:translate-x-1 transition-all duration-150 block"
									@click="activeMega = null"
								>
									{{ brand }}
								</router-link>
							</li>
						</ul>
						<router-link
							to="/keyboards"
							class="mt-4 block text-[0.65rem] font-bold uppercase tracking-wider text-[#FFC700] hover:text-yellow-300 transition-colors"
							@click="activeMega = null"
						>Ver todas as marcas →</router-link>
					</div>

					<!-- Tipos de Relógio -->
					<div>
						<p class="text-[0.6rem] font-bold tracking-[0.2em] uppercase text-[#FFC700] mb-4">Tipo de Relógio</p>
						<ul class="space-y-2">
							<li v-for="t in megaMenus[activeMega!].types" :key="t">
								<router-link
									:to="`/keyboards?tipo=${t.toLowerCase()}`"
									class="text-sm text-white/60 hover:text-white hover:translate-x-1 transition-all duration-150 block"
									@click="activeMega = null"
								>
									{{ t }}
								</router-link>
							</li>
						</ul>
					</div>

					<!-- Analógico / Smartwatch -->
					<div>
						<p class="text-[0.6rem] font-bold tracking-[0.2em] uppercase text-[#FFC700] mb-4">Mecanismo</p>
						<ul class="space-y-2">
							<li v-for="k in megaMenus[activeMega!].kind" :key="k">
								<router-link
									:to="`/keyboards?mecanismo=${k.toLowerCase()}`"
									class="text-sm text-white/60 hover:text-white hover:translate-x-1 transition-all duration-150 block"
									@click="activeMega = null"
								>
									{{ k }}
								</router-link>
							</li>
						</ul>
					</div>

					<!-- Gama de Preços -->
					<div>
						<p class="text-[0.6rem] font-bold tracking-[0.2em] uppercase text-[#FFC700] mb-4">Gama de Preço</p>
						<ul class="space-y-2">
							<li v-for="p in megaMenus[activeMega!].prices" :key="p">
								<router-link
									:to="`/keyboards?preco=${encodeURIComponent(p)}`"
									class="text-sm text-white/60 hover:text-white hover:translate-x-1 transition-all duration-150 block"
									@click="activeMega = null"
								>
									{{ p }}
								</router-link>
							</li>
						</ul>
					</div>

					<!-- Cores -->
					<div>
						<p class="text-[0.6rem] font-bold tracking-[0.2em] uppercase text-[#FFC700] mb-4">Cores</p>
						<div class="flex flex-wrap gap-3">
							<button
								v-for="c in megaMenus[activeMega!].colors"
								:key="c.name"
								:title="c.name"
								class="group flex flex-col items-center gap-1.5"
								@click="activeMega = null"
							>
								<span
									class="w-7 h-7 rounded-full border-2 border-white/20 hover:border-[#FFC700] transition-colors duration-150 block"
									:style="{ backgroundColor: c.hex }"
								></span>
								<span class="text-[0.6rem] text-white/40 group-hover:text-white/70 transition-colors">{{ c.name }}</span>
							</button>
						</div>
					</div>

				</div>
			</div>
		</Transition>

		<Cart v-show="cartStore.showCart" />

		<!-- Menu Mobile (hamburger) -->
		<transition>
			<nav
				class="absolute flex w-screen flex-col items-center gap-4 bg-black p-9 text-sm font-semibold tracking-widest z-50"
				v-if="hamburgerState === 'show'"
				:class="$route.path === '/' ? 'bg-k-black' : 'bg-black'"
				data-test="nav-mobile"
			>
				<button class="uppercase text-white transition duration-300 hover:text-k-main active:translate-y-0.5" @click="hideHamburger()" data-test="close-hamburger">
					Fechar ✕
				</button>
				<router-link to="/" class="uppercase text-white transition duration-300 hover:text-k-main active:translate-y-0.5" :class="$route.path === '/' && 'hidden'" @click="hideHamburger()" data-test="mobile-nav-home">Home</router-link>
				<router-link to="/keyboards" class="uppercase text-white transition duration-300 hover:text-k-main active:translate-y-0.5" @click="hideHamburger()">Homens</router-link>
				<router-link to="/keyboards" class="uppercase text-white transition duration-300 hover:text-k-main active:translate-y-0.5" @click="hideHamburger()">Mulheres</router-link>
				<router-link to="/keyboards" class="uppercase text-white transition duration-300 hover:text-k-main active:translate-y-0.5" @click="hideHamburger()">Unisexo</router-link>
				<router-link to="/sobre-nos" class="uppercase text-white transition duration-300 hover:text-k-main active:translate-y-0.5" :class="$route.path === '/sobre-nos' && 'hidden'" @click="hideHamburger()" data-test="mobile-nav-deskmats">Sobre Nós</router-link>

				<!-- Divisor -->
				<div class="w-16 h-px bg-white/10 my-1"></div>

				<!-- Auth mobile -->
				<template v-if="authStore.user">
					<div class="text-center">
						<div class="flex items-center justify-center w-10 h-10 rounded-full bg-k-main text-k-black text-sm font-black mx-auto mb-2">
							{{ authStore.user.firstname.charAt(0).toUpperCase() }}{{ authStore.user.lastname.charAt(0).toUpperCase() }}
						</div>
						<p class="text-white/60 text-xs">{{ authStore.user.firstname }} {{ authStore.user.lastname }}</p>
					</div>
					<button @click="handleLogout(); hideHamburger()" class="uppercase text-red-400/80 hover:text-red-400 transition duration-300" data-test="mobile-nav-logout">Terminar Sessão</button>
				</template>
				<template v-else>
					<router-link to="/login" class="uppercase text-white/70 hover:text-white transition duration-300" @click="hideHamburger()" data-test="mobile-nav-login">Entrar</router-link>
					<router-link to="/register" class="flex items-center gap-2 bg-k-main text-k-black font-bold uppercase px-6 py-2.5 rounded-full hover:bg-yellow-400 transition duration-200" @click="hideHamburger()" data-test="mobile-nav-register">Criar Conta</router-link>
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
