<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../../pinia/authStore'
import Navigation from '../../components/navigation-global.vue'
import Footer from '../../components/footer-global.vue'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const status = ref<'loading' | 'success' | 'error'>('loading')
const message = ref('A verificar o teu email...')

onMounted(async () => {
	const id = route.params.id
	const signature = route.query.signature
	const expires = route.query.expires
	
	if (!id || !signature || !expires) {
		status.value = 'error'
		message.value = 'Link de verificação inválido ou incompleto.'
		return
	}
	
	try {
		const res = await fetch(`/api/email/verify/${id}?signature=${signature}&expires=${expires}`, {
			method: 'GET',
			headers: {
				Accept: 'application/json',
			},
		})
		const data = await res.json()
		
		if (res.ok) {
			status.value = 'success'
			message.value = data.message || 'Email verificado com sucesso! Já podes efetuar compras.'
			// Atualizar o estado do utilizador localmente
			if (authStore.user) {
				authStore.user.email_verified_at = new Date().toISOString()
			}
			// Redirecionar após 3 segundos
			setTimeout(() => {
				router.push('/conta/perfil')
			}, 3000)
		} else {
			status.value = 'error'
			message.value = data.message || 'Falha ao verificar email. O link pode ter expirado.'
		}
	} catch {
		status.value = 'error'
		message.value = 'Sem ligação ao servidor. Tenta novamente mais tarde.'
	}
})
</script>

<template>
	<div class="flex min-h-screen w-screen flex-col bg-k-black">
		<Navigation color="k-black" />

		<main class="flex flex-1 items-center justify-center px-4 py-20">
			<!-- Background decorativo -->
			<div class="pointer-events-none absolute inset-0 overflow-hidden">
				<div class="absolute -right-40 top-1/4 h-96 w-96 rounded-full bg-k-main opacity-5 blur-3xl"></div>
				<div class="absolute -left-40 bottom-1/4 h-96 w-96 rounded-full bg-k-main opacity-5 blur-3xl"></div>
			</div>

			<div class="relative w-full max-w-md">
				<div class="rounded-2xl border border-white/10 bg-k-dark-grey p-8 shadow-2xl text-center">
					<div v-if="status === 'loading'">
						<div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full border border-k-main/30 bg-k-main/10 animate-pulse">
							<svg class="h-8 w-8 text-k-main animate-spin" fill="none" viewBox="0 0 24 24">
								<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
								<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
							</svg>
						</div>
						<h2 class="mb-3 text-xl font-bold text-white">A validar email</h2>
						<p class="text-sm text-white/50">{{ message }}</p>
					</div>

					<div v-else-if="status === 'success'">
						<div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full border border-green-500/30 bg-green-500/10">
							<svg class="h-8 w-8 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
							</svg>
						</div>
						<h2 class="mb-3 text-xl font-bold text-white">Email verificado!</h2>
						<p class="mb-8 text-sm leading-relaxed text-white/50">{{ message }}</p>
						<p class="text-xs text-white/30">A redirecionar para o teu perfil...</p>
					</div>

					<div v-else>
						<div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full border border-red-500/30 bg-red-500/10">
							<svg class="h-8 w-8 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
							</svg>
						</div>
						<h2 class="mb-3 text-xl font-bold text-white">Erro na verificação</h2>
						<p class="mb-8 text-sm leading-relaxed text-white/50">{{ message }}</p>
						<RouterLink to="/" class="inline-flex items-center gap-2 rounded-xl bg-k-main px-6 py-2.5 text-sm font-bold text-k-black transition duration-200 hover:bg-yellow-400">
							Ir para a Loja
						</RouterLink>
					</div>
				</div>
			</div>
		</main>

		<Footer />
	</div>
</template>
