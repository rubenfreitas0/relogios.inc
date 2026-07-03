<script setup lang="ts">
import Navigation from '../../components/navigation-global.vue'
import Footer from '../../components/footer-global.vue'
import { useAuthStore } from '../../pinia/authStore'
import { useRouter, useRoute } from 'vue-router'
import { computed, onMounted } from 'vue'

const authStore = useAuthStore()
const router = useRouter()
const route = useRoute()

onMounted(() => {
	if (!authStore.token) {
		router.push('/login')
	}
})

const tabs = [
	{ label: 'Perfil', path: '/conta/perfil', icon: 'user' },
	{ label: 'Encomendas', path: '/conta/encomendas', icon: 'orders' },
	{ label: 'Moradas', path: '/conta/moradas', icon: 'address' },
	{ label: 'Ajuda & Suporte', path: '/conta/ajuda', icon: 'help' },
]

const activeTab = computed(() => {
	if (route.path.startsWith('/conta/encomendas')) return '/conta/encomendas'
	if (route.path.startsWith('/conta/moradas')) return '/conta/moradas'
	if (route.path.startsWith('/conta/ajuda')) return '/conta/ajuda'
	return '/conta/perfil'
})

async function handleLogout() {
	await authStore.logout()
	router.push('/')
}
</script>

<template>
	<main class="flex min-h-screen w-screen flex-col items-center bg-k-black">
		<Navigation />

		<div class="w-full max-w-6xl px-4 pb-20 pt-8 md:px-8 lg:px-4">
			<!-- Header -->
			<div class="mb-10">
				<h1 class="text-3xl font-bold uppercase tracking-wider text-white">
					A Minha Conta
				</h1>
				<p v-if="authStore.user" class="mt-2 text-sm text-white/40">
					{{ authStore.user.email }}
				</p>
			</div>

			<!-- Layout: sidebar (desktop) + content -->
			<div class="flex flex-col gap-8 lg:flex-row">
				<!-- Sidebar / Tabs -->
				<nav
					class="flex flex-row gap-2 overflow-x-auto lg:w-56 lg:flex-shrink-0 lg:flex-col lg:gap-1"
				>
					<router-link
						v-for="tab in tabs"
						:key="tab.path"
						:to="tab.path"
						class="flex items-center gap-3 whitespace-nowrap rounded-lg px-4 py-3 text-sm font-semibold transition-all duration-200"
						:class="
							activeTab === tab.path
								? 'border border-[#FFC700]/20 bg-[#FFC700]/10 text-[#FFC700]'
								: 'border border-transparent text-white/50 hover:bg-white/5 hover:text-white/80'
						"
					>
						<!-- User icon -->
						<svg
							v-if="tab.icon === 'user'"
							class="h-4 w-4 flex-shrink-0"
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
						<!-- Orders icon -->
						<svg
							v-if="tab.icon === 'orders'"
							class="h-4 w-4 flex-shrink-0"
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
						<!-- Address icon -->
						<svg
							v-if="tab.icon === 'address'"
							class="h-4 w-4 flex-shrink-0"
							fill="none"
							viewBox="0 0 24 24"
							stroke="currentColor"
						>
							<path
								stroke-linecap="round"
								stroke-linejoin="round"
								stroke-width="2"
								d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
							/>
							<path
								stroke-linecap="round"
								stroke-linejoin="round"
								stroke-width="2"
								d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
							/>
						</svg>
						<!-- Help icon -->
						<svg
							v-if="tab.icon === 'help'"
							class="h-4 w-4 flex-shrink-0"
							fill="none"
							viewBox="0 0 24 24"
							stroke="currentColor"
						>
							<path
								stroke-linecap="round"
								stroke-linejoin="round"
								stroke-width="2"
								d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
							/>
						</svg>
						{{ tab.label }}
					</router-link>

					<!-- Divider (Desktop) -->
					<div class="my-3 hidden h-px bg-white/10 lg:block"></div>

					<!-- Logout Button (Desktop) -->
					<button
						@click="handleLogout"
						class="hidden items-center gap-3 whitespace-nowrap rounded-lg border border-transparent px-4 py-3 text-sm font-semibold text-red-400/80 transition-all duration-200 hover:bg-red-500/10 hover:text-red-400 lg:flex"
					>
						<svg
							class="h-4 w-4 flex-shrink-0"
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
						Terminar Sessão
					</button>
				</nav>

				<!-- Content area -->
				<div class="min-w-0 flex-1">
					<router-view />
				</div>
			</div>
		</div>

		<Footer />
	</main>
</template>
