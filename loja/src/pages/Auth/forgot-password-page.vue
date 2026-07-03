<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '../../pinia/authStore'
import Navigation from '../../components/navigation-global.vue'
import Footer from '../../components/footer-global.vue'

const auth = useAuthStore()
const email = ref('')
const submitted = ref(false)

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
	email.value = 'admin@relogios.inc'
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

async function handleForgotPassword() {
	if (isRateLimited.value) return

	const emEl = document.getElementById(
		'forgot-email',
	) as HTMLInputElement | null
	if (emEl && emEl.value && !email.value) {
		email.value = emEl.value
	}

	if (checkRateLimit()) return

	const ok = await auth.forgotPassword(email.value)
	if (ok) {
		submitted.value = true
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
					<!-- Estado: sucesso (email enviado) -->
					<Transition name="fade-slide" mode="out-in">
						<div v-if="submitted" key="success" class="py-4 text-center">
							<div
								class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full border border-k-main/30 bg-k-main/10"
							>
								<svg
									class="h-8 w-8 text-k-main"
									fill="none"
									viewBox="0 0 24 24"
									stroke="currentColor"
								>
									<path
										stroke-linecap="round"
										stroke-linejoin="round"
										stroke-width="2"
										d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
									/>
								</svg>
							</div>
							<h2 class="mb-3 text-xl font-bold text-white">
								Verifica o teu email
							</h2>
							<p class="mb-8 text-sm leading-relaxed text-white/50">
								{{ auth.successMessage }}
							</p>
							<router-link
								to="/login"
								class="inline-flex items-center gap-2 text-sm font-semibold text-k-main transition duration-200 hover:text-yellow-400"
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

						<!-- Estado: formulário -->
						<div v-else key="form">
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
											d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"
										/>
									</svg>
								</div>
								<h1 class="text-2xl font-bold tracking-tight text-white">
									Recuperar password
								</h1>
								<p class="mt-1 text-sm text-white/50">
									Envia-te um link para redefires a tua password
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

							<form @submit.prevent="handleForgotPassword" class="space-y-5">
								<div>
									<label
										class="mb-2 block text-xs font-semibold uppercase tracking-wider text-white/60"
										for="forgot-email"
									>
										Email
									</label>
									<input
										id="forgot-email"
										name="email"
										autocomplete="email"
										v-model="email"
										type="email"
										required
										placeholder="o.teu@email.com"
										class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder-white/20 transition duration-200 focus:border-k-main focus:outline-none"
									/>
								</div>

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
									{{
										isRateLimited
											? `Bloqueado (${cooldownSeconds}s)`
											: auth.isLoading
											? 'A enviar...'
											: 'Enviar link'
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
					</Transition>
				</div>
			</div>
		</main>

		<Footer />
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
