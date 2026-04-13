<script setup lang="ts">
import { ref } from 'vue'
import { useAuthStore } from '../../pinia/authStore'

const auth = useAuthStore()
const email = ref('')
const submitted = ref(false)

async function handleForgotPassword() {
	const ok = await auth.forgotPassword(email.value)
	if (ok) {
		submitted.value = true
	}
}
</script>

<template>
	<div class="min-h-screen w-screen bg-k-black flex items-center justify-center px-4 py-16">
		<!-- Background decorativo -->
		<div class="absolute inset-0 overflow-hidden pointer-events-none">
			<div class="absolute -top-40 -right-40 w-96 h-96 rounded-full bg-k-main opacity-5 blur-3xl"></div>
			<div class="absolute -bottom-40 -left-40 w-96 h-96 rounded-full bg-k-main opacity-5 blur-3xl"></div>
		</div>

		<div class="relative w-full max-w-md">
			<!-- Logo -->
			<div class="text-center mb-10">
				<router-link
					to="/"
					class="text-3xl font-extrabold tracking-tight text-white hover:text-k-main transition duration-300"
				>
					RELOGIOS<span class="text-k-main">.inc</span>
				</router-link>
			</div>

			<!-- Card -->
			<div class="bg-k-dark-grey border border-white/10 rounded-2xl p-8 shadow-2xl">
				<!-- Estado: sucesso (email enviado) -->
				<Transition name="fade-slide" mode="out-in">
					<div v-if="submitted" key="success" class="text-center py-4">
						<div class="flex items-center justify-center w-16 h-16 rounded-full bg-k-main/10 border border-k-main/30 mx-auto mb-6">
							<svg class="w-8 h-8 text-k-main" fill="none" viewBox="0 0 24 24" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
							</svg>
						</div>
						<h2 class="text-xl font-bold text-white mb-3">Verifica o teu email</h2>
						<p class="text-white/50 text-sm leading-relaxed mb-8">
							{{ auth.successMessage }}
						</p>
						<router-link
							to="/login"
							class="inline-flex items-center gap-2 text-k-main hover:text-yellow-400 text-sm font-semibold transition duration-200"
						>
							<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
							</svg>
							Voltar ao Login
						</router-link>
					</div>

					<!-- Estado: formulário -->
					<div v-else key="form">
						<div class="mb-8">
							<div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-k-main/10 border border-k-main/20 mb-5">
								<svg class="w-7 h-7 text-k-main" fill="none" viewBox="0 0 24 24" stroke="currentColor">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
								</svg>
							</div>
							<h1 class="text-2xl font-bold text-white tracking-tight">Recuperar password</h1>
							<p class="text-white/50 text-sm mt-1">Envia-te um link para redefires a tua password</p>
						</div>

						<!-- Erro -->
						<Transition name="fade-slide">
							<div
								v-if="auth.error"
								class="mb-6 flex items-start gap-3 bg-red-500/10 border border-red-500/30 rounded-xl p-4"
							>
								<svg class="w-5 h-5 text-red-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
								</svg>
								<p class="text-red-400 text-sm">{{ auth.error }}</p>
							</div>
						</Transition>

						<form @submit.prevent="handleForgotPassword" class="space-y-5">
							<div>
								<label class="block text-xs font-semibold text-white/60 uppercase tracking-wider mb-2" for="forgot-email">
									Email
								</label>
								<input
									id="forgot-email"
									v-model="email"
									type="email"
									required
									placeholder="o.teu@email.com"
									class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-white/20 text-sm focus:outline-none focus:border-k-main transition duration-200"
								/>
							</div>

							<button
								type="submit"
								:disabled="auth.isLoading"
								class="w-full bg-k-main text-k-black font-bold py-3.5 rounded-xl tracking-wider uppercase text-sm hover:bg-yellow-400 active:translate-y-0.5 transition duration-200 disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2"
							>
								<svg v-if="auth.isLoading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
									<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
									<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
								</svg>
								{{ auth.isLoading ? 'A enviar...' : 'Enviar link' }}
							</button>
						</form>

						<div class="text-center mt-6">
							<router-link
								to="/login"
								class="inline-flex items-center gap-2 text-white/40 hover:text-white/70 text-sm transition duration-200"
							>
								<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
								</svg>
								Voltar ao Login
							</router-link>
						</div>
					</div>
				</Transition>
			</div>
		</div>
	</div>
</template>

<style scoped>
.fade-slide-enter-active,
.fade-slide-leave-active {
	transition: all 0.3s ease;
}
.fade-slide-enter-from,
.fade-slide-leave-to {
	opacity: 0;
	transform: translateY(-6px);
}

input:focus {
	background-color: rgba(255, 199, 0, 0.04);
}
</style>
