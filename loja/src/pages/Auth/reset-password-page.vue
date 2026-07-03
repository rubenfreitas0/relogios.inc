<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
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

const isDev = import.meta.env.DEV

// Rate limiting state
const attempts = ref<number[]>([])
const cooldownSeconds = ref(0)
let cooldownTimer: number | null = null

const isRateLimited = computed(() => cooldownSeconds.value > 0)

function checkRateLimit(): boolean {
	const now = Date.now()
	attempts.value = attempts.value.filter((t) => now - t < 30000)

	if (attempts.value.length >= 5) {
		const earliest = attempts.value[0]
		cooldownSeconds.value = Math.ceil((30000 - (now - earliest)) / 1000)
		auth.error = `Demasiadas tentativas. Aguarde ${cooldownSeconds.value}s.`

		if (cooldownTimer) clearInterval(cooldownTimer)
		cooldownTimer = window.setInterval(() => {
			if (cooldownSeconds.value > 1) {
				cooldownSeconds.value--
				auth.error = `Demasiadas tentativas. Aguarde ${cooldownSeconds.value}s.`
			} else {
				cooldownSeconds.value = 0
				auth.error = null
				if (cooldownTimer) {
					clearInterval(cooldownTimer)
					cooldownTimer = null
				}
			}
		}, 1000)
		return true
	}

	attempts.value.push(now)
	return false
}

function injectTestData() {
	password.value = 'password123'
	passwordConfirmation.value = 'password123'
}

function handleKeyDown(e: KeyboardEvent) {
	if (e.ctrlKey && e.shiftKey && e.key.toLowerCase() === 'y') {
		e.preventDefault()
		injectTestData()
	}
}

onMounted(() => {
	window.addEventListener('keydown', handleKeyDown)
	// O email e token chegam via query string: /reset-password?token=xxx&email=yyy
	token.value = (route.query.token as string) ?? ''
	email.value = (route.query.email as string) ?? ''
})

onUnmounted(() => {
	window.removeEventListener('keydown', handleKeyDown)
	if (cooldownTimer) clearInterval(cooldownTimer)
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
	const colors = [
		'',
		'bg-red-500',
		'bg-yellow-500',
		'bg-blue-400',
		'bg-green-500',
	]
	return colors[passwordStrength.value] ?? ''
})

async function handleReset() {
	if (isRateLimited.value) return

	const emEl = document.getElementById('reset-email') as HTMLInputElement | null
	const pwEl = document.getElementById(
		'reset-password',
	) as HTMLInputElement | null
	const coEl = document.getElementById(
		'reset-confirm',
	) as HTMLInputElement | null

	if (emEl && emEl.value && !email.value) email.value = emEl.value
	if (pwEl && pwEl.value && !password.value) password.value = pwEl.value
	if (coEl && coEl.value && !passwordConfirmation.value)
		passwordConfirmation.value = coEl.value

	if (checkRateLimit()) return

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
	<div
		class="flex min-h-screen w-screen items-center justify-center bg-k-black px-4 py-16"
	>
		<!-- Background decorativo -->
		<div class="pointer-events-none absolute inset-0 overflow-hidden">
			<div
				class="absolute -right-40 -top-40 h-96 w-96 rounded-full bg-k-main opacity-5 blur-3xl"
			></div>
			<div
				class="absolute -bottom-40 -left-40 h-96 w-96 rounded-full bg-k-main opacity-5 blur-3xl"
			></div>
		</div>

		<div class="relative w-full max-w-md">
			<!-- Logo -->
			<div class="mb-10 text-center">
				<router-link
					to="/"
					class="text-3xl font-extrabold tracking-tight text-white transition duration-300 hover:text-k-main"
				>
					RELOGIOS<span class="text-k-main">.inc</span>
				</router-link>
			</div>

			<!-- Card -->
			<div
				class="rounded-2xl border border-white/10 bg-k-dark-grey p-8 shadow-2xl"
			>
				<!-- Estado: sucesso -->
				<Transition name="fade-slide" mode="out-in">
					<div v-if="resetDone" key="success" class="py-4 text-center">
						<div
							class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full border border-green-500/30 bg-green-500/10"
						>
							<svg
								class="h-8 w-8 text-green-400"
								fill="none"
								viewBox="0 0 24 24"
								stroke="currentColor"
							>
								<path
									stroke-linecap="round"
									stroke-linejoin="round"
									stroke-width="2"
									d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
								/>
							</svg>
						</div>
						<h2 class="mb-3 text-xl font-bold text-white">
							Password alterada!
						</h2>
						<p class="mb-2 text-sm leading-relaxed text-white/50">
							{{ auth.successMessage }}
						</p>
						<p class="mb-8 text-xs text-white/30">
							A redirecionar para o login em instantes...
						</p>
						<router-link
							to="/login"
							class="inline-flex items-center gap-2 rounded-xl bg-k-main px-6 py-2.5 text-sm font-bold text-k-black transition duration-200 hover:bg-yellow-400"
						>
							Ir para o Login
						</router-link>
					</div>

					<!-- Estado: formulário -->
					<div v-else key="form">
						<!-- Token em falta -->
						<div v-if="!token" class="py-4 text-center">
							<div
								class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full border border-red-500/30 bg-red-500/10"
							>
								<svg
									class="h-8 w-8 text-red-400"
									fill="none"
									viewBox="0 0 24 24"
									stroke="currentColor"
								>
									<path
										stroke-linecap="round"
										stroke-linejoin="round"
										stroke-width="2"
										d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
									/>
								</svg>
							</div>
							<h2 class="mb-3 text-xl font-bold text-white">Link inválido</h2>
							<p class="mb-8 text-sm text-white/50">
								Este link de recuperação é inválido ou expirou.
							</p>
							<router-link
								to="/forgot-password"
								class="inline-flex items-center gap-2 rounded-xl bg-k-main px-6 py-2.5 text-sm font-bold text-k-black transition duration-200 hover:bg-yellow-400"
							>
								Pedir novo link
							</router-link>
						</div>

						<!-- Formulário normal -->
						<div v-else>
							<div class="mb-8">
								<div
									class="mb-5 flex h-14 w-14 items-center justify-center rounded-2xl border border-k-main/20 bg-k-main/10"
								>
									<svg
										class="h-7 w-7 text-k-main"
										fill="none"
										viewBox="0 0 24 24"
										stroke="currentColor"
									>
										<path
											stroke-linecap="round"
											stroke-linejoin="round"
											stroke-width="2"
											d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
										/>
									</svg>
								</div>
								<h1 class="text-2xl font-bold tracking-tight text-white">
									Nova password
								</h1>
								<p class="mt-1 text-sm text-white/50">
									Define a tua nova password de acesso
								</p>
							</div>

							<!-- Erro -->
							<Transition name="fade-slide">
								<div
									v-if="auth.error"
									class="mb-6 flex items-start gap-3 rounded-xl border border-red-500/30 bg-red-500/10 p-4"
								>
									<svg
										class="mt-0.5 h-5 w-5 flex-shrink-0 text-red-400"
										fill="none"
										viewBox="0 0 24 24"
										stroke="currentColor"
									>
										<path
											stroke-linecap="round"
											stroke-linejoin="round"
											stroke-width="2"
											d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
										/>
									</svg>
									<p class="text-sm text-red-400">{{ auth.error }}</p>
								</div>
							</Transition>

							<form @submit.prevent="handleReset" class="space-y-5">
								<!-- Email (readonly, pré-preenchido) -->
								<div>
									<label
										class="mb-2 block text-xs font-semibold uppercase tracking-wider text-white/60"
										for="reset-email"
									>
										Email
									</label>
									<input
										id="reset-email"
										name="email"
										autocomplete="email"
										v-model="email"
										type="email"
										required
										placeholder="o.teu@email.com"
										class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white/60 placeholder-white/20 transition duration-200 focus:border-k-main focus:outline-none"
									/>
								</div>

								<!-- Nova password -->
								<div>
									<label
										class="mb-2 block text-xs font-semibold uppercase tracking-wider text-white/60"
										for="reset-password"
									>
										Nova Password
									</label>
									<div class="relative">
										<input
											id="reset-password"
											name="password"
											autocomplete="new-password"
											v-model="password"
											:type="showPassword ? 'text' : 'password'"
											required
											minlength="8"
											placeholder="Mínimo 8 caracteres"
											class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 pr-12 text-sm text-white placeholder-white/20 transition duration-200 focus:border-k-main focus:outline-none"
										/>
										<button
											type="button"
											@click="showPassword = !showPassword"
											class="absolute right-4 top-1/2 -translate-y-1/2 text-white/30 transition duration-200 hover:text-white/70"
										>
											<svg
												v-if="!showPassword"
												class="h-4 w-4"
												fill="none"
												viewBox="0 0 24 24"
												stroke="currentColor"
											>
												<path
													stroke-linecap="round"
													stroke-linejoin="round"
													stroke-width="2"
													d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
												/>
												<path
													stroke-linecap="round"
													stroke-linejoin="round"
													stroke-width="2"
													d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
												/>
											</svg>
											<svg
												v-else
												class="h-4 w-4"
												fill="none"
												viewBox="0 0 24 24"
												stroke="currentColor"
											>
												<path
													stroke-linecap="round"
													stroke-linejoin="round"
													stroke-width="2"
													d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"
												/>
											</svg>
										</button>
									</div>
									<!-- Barra de força -->
									<div v-if="password" class="mt-2 flex items-center gap-2">
										<div class="flex flex-1 gap-1">
											<div
												v-for="i in 4"
												:key="i"
												class="h-1 flex-1 rounded-full transition-all duration-300"
												:class="
													i <= passwordStrength ? strengthColor : 'bg-white/10'
												"
											></div>
										</div>
										<span class="text-xs text-white/50">{{
											strengthLabel
										}}</span>
									</div>
								</div>

								<!-- Confirmar password -->
								<div>
									<label
										class="mb-2 block text-xs font-semibold uppercase tracking-wider text-white/60"
										for="reset-confirm"
									>
										Confirmar Password
									</label>
									<div class="relative">
										<input
											id="reset-confirm"
											name="password_confirmation"
											autocomplete="new-password"
											v-model="passwordConfirmation"
											:type="showConfirmPassword ? 'text' : 'password'"
											required
											placeholder="Repete a nova password"
											class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 pr-12 text-sm text-white placeholder-white/20 transition duration-200 focus:outline-none"
											:class="
												passwordConfirmation && !passwordsMatch
													? 'border-red-500/60'
													: 'border-white/10 focus:border-k-main'
											"
										/>
										<button
											type="button"
											@click="showConfirmPassword = !showConfirmPassword"
											class="absolute right-4 top-1/2 -translate-y-1/2 text-white/30 transition duration-200 hover:text-white/70"
										>
											<svg
												v-if="!showConfirmPassword"
												class="h-4 w-4"
												fill="none"
												viewBox="0 0 24 24"
												stroke="currentColor"
											>
												<path
													stroke-linecap="round"
													stroke-linejoin="round"
													stroke-width="2"
													d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
												/>
												<path
													stroke-linecap="round"
													stroke-linejoin="round"
													stroke-width="2"
													d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
												/>
											</svg>
											<svg
												v-else
												class="h-4 w-4"
												fill="none"
												viewBox="0 0 24 24"
												stroke="currentColor"
											>
												<path
													stroke-linecap="round"
													stroke-linejoin="round"
													stroke-width="2"
													d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"
												/>
											</svg>
										</button>
									</div>
									<Transition name="fade-slide">
										<p
											v-if="passwordConfirmation && !passwordsMatch"
											class="mt-1.5 text-xs text-red-400"
										>
											As passwords não coincidem.
										</p>
									</Transition>
								</div>

								<button
									type="submit"
									:disabled="auth.isLoading || !passwordsMatch || isRateLimited"
									class="mt-2 flex w-full items-center justify-center gap-2 rounded-xl bg-k-main py-3.5 text-sm font-bold uppercase tracking-wider text-k-black transition duration-200 hover:bg-yellow-400 active:translate-y-0.5 disabled:cursor-not-allowed disabled:opacity-60"
								>
									<svg
										v-if="auth.isLoading"
										class="h-4 w-4 animate-spin"
										fill="none"
										viewBox="0 0 24 24"
									>
										<circle
											class="opacity-25"
											cx="12"
											cy="12"
											r="10"
											stroke="currentColor"
											stroke-width="4"
										/>
										<path
											class="opacity-75"
											fill="currentColor"
											d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
										/>
									</svg>
									{{
										isRateLimited
											? `Bloqueado (${cooldownSeconds}s)`
											: auth.isLoading
											? 'A guardar...'
											: 'Guardar nova password'
									}}
								</button>
							</form>

							<!-- Dev Helper -->
							<div
								v-if="isDev"
								class="mt-6 flex justify-center border-t border-white/5 pt-4"
							>
								<button
									type="button"
									@click="injectTestData"
									class="flex items-center gap-1.5 rounded-lg bg-white/5 px-3 py-1.5 text-xs text-k-main/60 transition duration-200 hover:bg-white/10 hover:text-k-main"
								>
									<svg
										class="h-3.5 w-3.5"
										fill="none"
										viewBox="0 0 24 24"
										stroke="currentColor"
									>
										<path
											stroke-linecap="round"
											stroke-linejoin="round"
											stroke-width="2"
											d="M13 10V3L4 14h7v7l9-11h-7z"
										/>
									</svg>
									Preencher dados de teste
									<span class="ml-1 font-mono text-[10px] text-white/30"
										>(Ctrl+Shift+Y)</span
									>
								</button>
							</div>

							<div class="mt-6 text-center">
								<router-link
									to="/login"
									class="inline-flex items-center gap-2 text-sm text-white/40 transition duration-200 hover:text-white/70"
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
											d="M10 19l-7-7m0 0l7-7m-7 7h18"
										/>
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
