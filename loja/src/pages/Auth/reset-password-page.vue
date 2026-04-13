<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../../pinia/authStore'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

const email = ref('')
const token = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const showPassword = ref(false)
const showConfirmPassword = ref(false)
const resetDone = ref(false)

onMounted(() => {
	// O email e token chegam via query string: /reset-password?token=xxx&email=yyy
	token.value = (route.query.token as string) ?? ''
	email.value = (route.query.email as string) ?? ''
})

const passwordsMatch = computed(() => {
	if (!passwordConfirmation.value) return true
	return password.value === passwordConfirmation.value
})

const passwordStrength = computed(() => {
	const p = password.value
	if (!p) return 0
	let score = 0
	if (p.length >= 8) score++
	if (/[A-Z]/.test(p)) score++
	if (/[0-9]/.test(p)) score++
	if (/[^A-Za-z0-9]/.test(p)) score++
	return score
})

const strengthLabel = computed(() => {
	const labels = ['', 'Fraca', 'Razoável', 'Boa', 'Forte']
	return labels[passwordStrength.value] ?? ''
})

const strengthColor = computed(() => {
	const colors = ['', 'bg-red-500', 'bg-yellow-500', 'bg-blue-400', 'bg-green-500']
	return colors[passwordStrength.value] ?? ''
})

async function handleReset() {
	if (!passwordsMatch.value) return

	const ok = await auth.resetPassword({
		email: email.value,
		token: token.value,
		password: password.value,
		password_confirmation: passwordConfirmation.value,
	})

	if (ok) {
		resetDone.value = true
		// Redireciona para login após 3 segundos
		setTimeout(() => router.push('/login'), 3000)
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
				<!-- Estado: sucesso -->
				<Transition name="fade-slide" mode="out-in">
					<div v-if="resetDone" key="success" class="text-center py-4">
						<div class="flex items-center justify-center w-16 h-16 rounded-full bg-green-500/10 border border-green-500/30 mx-auto mb-6">
							<svg class="w-8 h-8 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
							</svg>
						</div>
						<h2 class="text-xl font-bold text-white mb-3">Password alterada!</h2>
						<p class="text-white/50 text-sm leading-relaxed mb-2">
							{{ auth.successMessage }}
						</p>
						<p class="text-white/30 text-xs mb-8">A redirecionar para o login em instantes...</p>
						<router-link
							to="/login"
							class="inline-flex items-center gap-2 bg-k-main text-k-black font-bold px-6 py-2.5 rounded-xl text-sm hover:bg-yellow-400 transition duration-200"
						>
							Ir para o Login
						</router-link>
					</div>

					<!-- Estado: formulário -->
					<div v-else key="form">
						<!-- Token em falta -->
						<div v-if="!token" class="text-center py-4">
							<div class="flex items-center justify-center w-16 h-16 rounded-full bg-red-500/10 border border-red-500/30 mx-auto mb-6">
								<svg class="w-8 h-8 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
								</svg>
							</div>
							<h2 class="text-xl font-bold text-white mb-3">Link inválido</h2>
							<p class="text-white/50 text-sm mb-8">Este link de recuperação é inválido ou expirou.</p>
							<router-link
								to="/forgot-password"
								class="inline-flex items-center gap-2 bg-k-main text-k-black font-bold px-6 py-2.5 rounded-xl text-sm hover:bg-yellow-400 transition duration-200"
							>
								Pedir novo link
							</router-link>
						</div>

						<!-- Formulário normal -->
						<div v-else>
							<div class="mb-8">
								<div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-k-main/10 border border-k-main/20 mb-5">
									<svg class="w-7 h-7 text-k-main" fill="none" viewBox="0 0 24 24" stroke="currentColor">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
									</svg>
								</div>
								<h1 class="text-2xl font-bold text-white tracking-tight">Nova password</h1>
								<p class="text-white/50 text-sm mt-1">Define a tua nova password de acesso</p>
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

							<form @submit.prevent="handleReset" class="space-y-5">
								<!-- Email (readonly, pré-preenchido) -->
								<div>
									<label class="block text-xs font-semibold text-white/60 uppercase tracking-wider mb-2" for="reset-email">
										Email
									</label>
									<input
										id="reset-email"
										v-model="email"
										type="email"
										required
										placeholder="o.teu@email.com"
										class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white/60 placeholder-white/20 text-sm focus:outline-none focus:border-k-main transition duration-200"
									/>
								</div>

								<!-- Nova password -->
								<div>
									<label class="block text-xs font-semibold text-white/60 uppercase tracking-wider mb-2" for="reset-password">
										Nova Password
									</label>
									<div class="relative">
										<input
											id="reset-password"
											v-model="password"
											:type="showPassword ? 'text' : 'password'"
											required
											minlength="8"
											placeholder="Mínimo 8 caracteres"
											class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 pr-12 text-white placeholder-white/20 text-sm focus:outline-none focus:border-k-main transition duration-200"
										/>
										<button
											type="button"
											@click="showPassword = !showPassword"
											class="absolute right-4 top-1/2 -translate-y-1/2 text-white/30 hover:text-white/70 transition duration-200"
										>
											<svg v-if="!showPassword" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
												<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
												<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
											</svg>
											<svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
												<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
											</svg>
										</button>
									</div>
									<!-- Barra de força -->
									<div v-if="password" class="mt-2 flex items-center gap-2">
										<div class="flex gap-1 flex-1">
											<div
												v-for="i in 4"
												:key="i"
												class="h-1 flex-1 rounded-full transition-all duration-300"
												:class="i <= passwordStrength ? strengthColor : 'bg-white/10'"
											></div>
										</div>
										<span class="text-xs text-white/50">{{ strengthLabel }}</span>
									</div>
								</div>

								<!-- Confirmar password -->
								<div>
									<label class="block text-xs font-semibold text-white/60 uppercase tracking-wider mb-2" for="reset-confirm">
										Confirmar Password
									</label>
									<div class="relative">
										<input
											id="reset-confirm"
											v-model="passwordConfirmation"
											:type="showConfirmPassword ? 'text' : 'password'"
											required
											placeholder="Repete a nova password"
											class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 pr-12 text-white placeholder-white/20 text-sm focus:outline-none transition duration-200"
											:class="passwordConfirmation && !passwordsMatch ? 'border-red-500/60' : 'border-white/10 focus:border-k-main'"
										/>
										<button
											type="button"
											@click="showConfirmPassword = !showConfirmPassword"
											class="absolute right-4 top-1/2 -translate-y-1/2 text-white/30 hover:text-white/70 transition duration-200"
										>
											<svg v-if="!showConfirmPassword" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
												<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
												<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
											</svg>
											<svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
												<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
											</svg>
										</button>
									</div>
									<Transition name="fade-slide">
										<p v-if="passwordConfirmation && !passwordsMatch" class="mt-1.5 text-xs text-red-400">
											As passwords não coincidem.
										</p>
									</Transition>
								</div>

								<button
									type="submit"
									:disabled="auth.isLoading || !passwordsMatch"
									class="w-full bg-k-main text-k-black font-bold py-3.5 rounded-xl tracking-wider uppercase text-sm hover:bg-yellow-400 active:translate-y-0.5 transition duration-200 disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2 mt-2"
								>
									<svg v-if="auth.isLoading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
										<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
										<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
									</svg>
									{{ auth.isLoading ? 'A guardar...' : 'Guardar nova password' }}
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
