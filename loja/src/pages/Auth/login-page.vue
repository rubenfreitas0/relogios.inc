<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../pinia/authStore'
import Navigation from '../../components/navigation-global.vue'
import Footer from '../../components/footer-global.vue'

const auth = useAuthStore()
const router = useRouter()

const email = ref('')
const password = ref('')
const showPassword = ref(false)

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
		auth.error = `Demasiadas tentativas de login. Aguarde ${cooldownSeconds.value}s.`

		if (cooldownTimer) clearInterval(cooldownTimer)
		cooldownTimer = window.setInterval(() => {
			if (cooldownSeconds.value > 1) {
				cooldownSeconds.value--
				auth.error = `Demasiadas tentativas de login. Aguarde ${cooldownSeconds.value}s.`
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
	email.value = 'admin@relogios.inc'
	password.value = 'password'
}

function handleKeyDown(e: KeyboardEvent) {
	if (e.ctrlKey && e.shiftKey && e.key.toLowerCase() === 'y') {
		e.preventDefault()
		injectTestData()
	}
}

onMounted(() => {
	window.addEventListener('keydown', handleKeyDown)
})

onUnmounted(() => {
	window.removeEventListener('keydown', handleKeyDown)
	if (cooldownTimer) clearInterval(cooldownTimer)
})

async function handleLogin() {
	if (isRateLimited.value) return

	// Fallback to DOM values if browser autofill didn't trigger Vue's input events
	const emailEl = document.getElementById('login-email') as HTMLInputElement | null
	const passwordEl = document.getElementById('login-password') as HTMLInputElement | null
	if (emailEl && emailEl.value && !email.value) {
		email.value = emailEl.value
	}
	if (passwordEl && passwordEl.value && !password.value) {
		password.value = passwordEl.value
	}

	if (checkRateLimit()) return

	const ok = await auth.login(email.value, password.value)
	if (ok) {
		router.push('/')
	}
}
</script>

<template>
	<div class="flex min-h-screen w-screen flex-col bg-k-black">
		<Navigation color="k-black" />

		<main class="flex flex-1 items-center justify-center px-4 py-20">
			<!-- Background decorativo -->
			<div class="pointer-events-none absolute inset-0 overflow-hidden">
				<div
					class="absolute -right-40 top-1/4 h-96 w-96 rounded-full bg-k-main opacity-5 blur-3xl"
				></div>
				<div
					class="absolute -left-40 bottom-1/4 h-96 w-96 rounded-full bg-k-main opacity-5 blur-3xl"
				></div>
			</div>

			<div class="relative w-full max-w-md">
				<!-- Card -->
				<div
					class="rounded-2xl border border-white/10 bg-k-dark-grey p-8 shadow-2xl"
				>
					<!-- Header -->
					<div class="mb-8">
						<h1 class="text-2xl font-bold tracking-tight text-white">
							Bem-vindo de volta
						</h1>
						<p class="mt-1 text-sm text-white/50">
							Entra na tua conta para continuar
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

					<!-- Formulário -->
					<form @submit.prevent="handleLogin" class="space-y-5">
						<!-- Email -->
						<div>
							<label
								class="mb-2 block text-xs font-semibold uppercase tracking-wider text-white/60"
								for="login-email"
							>
								Email
							</label>
							<input
								id="login-email"
								name="email"
								autocomplete="username"
								v-model="email"
								type="email"
								required
								placeholder="o.teu@email.com"
								class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder-white/20 transition duration-200 focus:border-k-main focus:outline-none"
							/>
						</div>

						<!-- Password -->
						<div>
							<div class="mb-2 flex items-center justify-between">
								<label
									class="block text-xs font-semibold uppercase tracking-wider text-white/60"
									for="login-password"
								>
									Password
								</label>
								<router-link
									to="/forgot-password"
									class="text-xs text-k-main/80 transition duration-200 hover:text-k-main"
								>
									Esqueceste-te?
								</router-link>
							</div>
							<div class="relative">
								<input
									id="login-password"
									name="password"
									autocomplete="current-password"
									v-model="password"
									:type="showPassword ? 'text' : 'password'"
									required
									placeholder="••••••••"
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
						</div>

						<!-- Botão -->
						<button
							type="submit"
							:disabled="auth.isLoading || isRateLimited"
							class="flex w-full items-center justify-center gap-2 rounded-xl bg-k-main py-3.5 text-sm font-bold uppercase tracking-wider text-k-black transition duration-200 hover:bg-yellow-400 active:translate-y-0.5 disabled:cursor-not-allowed disabled:opacity-60"
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
							{{ isRateLimited ? `Bloqueado (${cooldownSeconds}s)` : auth.isLoading ? 'A entrar...' : 'Entrar' }}
						</button>
					</form>

					<!-- Dev Helper -->
					<div v-if="isDev" class="mt-6 pt-4 border-t border-white/5 flex justify-center">
						<button
							type="button"
							@click="injectTestData"
							class="text-xs text-k-main/60 hover:text-k-main transition duration-200 flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/5 hover:bg-white/10"
						>
							<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
							</svg>
							Preencher dados de teste <span class="text-white/30 text-[10px] font-mono ml-1">(Ctrl+Shift+Y)</span>
						</button>
					</div>

					<!-- Link registo -->
					<p class="mt-6 text-center text-sm text-white/40">
						Ainda não tens conta?
						<router-link
							to="/register"
							class="font-semibold text-k-main transition duration-200 hover:text-yellow-400"
						>
							Cria uma aqui
						</router-link>
					</p>
				</div>
			</div>
		</main>

		<Footer />
	</div>
</template>

<style scoped>
.fade-slide-enter-active,
.fade-slide-leave-active {
	transition: all 0.25s ease;
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
