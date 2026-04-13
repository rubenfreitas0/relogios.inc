<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../pinia/authStore'
import Navigation from '../../components/navigation-global.vue'
import Footer from '../../components/footer-global.vue'

const auth = useAuthStore()
const router = useRouter()

const email = ref('')
const password = ref('')
const showPassword = ref(false)

async function handleLogin() {
	const ok = await auth.login(email.value, password.value)
	if (ok) {
		router.push('/')
	}
}
</script>

<template>
	<div class="min-h-screen w-screen bg-k-black flex flex-col">
		<Navigation color="k-black" />

		<main class="flex-1 flex items-center justify-center px-4 py-20">
			<!-- Background decorativo -->
			<div class="absolute inset-0 overflow-hidden pointer-events-none">
				<div class="absolute top-1/4 -right-40 w-96 h-96 rounded-full bg-k-main opacity-5 blur-3xl"></div>
				<div class="absolute bottom-1/4 -left-40 w-96 h-96 rounded-full bg-k-main opacity-5 blur-3xl"></div>
			</div>

			<div class="relative w-full max-w-md">
				<!-- Card -->
				<div class="bg-k-dark-grey border border-white/10 rounded-2xl p-8 shadow-2xl">
					<!-- Header -->
					<div class="mb-8">
						<h1 class="text-2xl font-bold text-white tracking-tight">Bem-vindo de volta</h1>
						<p class="text-white/50 text-sm mt-1">Entra na tua conta para continuar</p>
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

					<!-- Formulário -->
					<form @submit.prevent="handleLogin" class="space-y-5">
						<!-- Email -->
						<div>
							<label class="block text-xs font-semibold text-white/60 uppercase tracking-wider mb-2" for="login-email">
								Email
							</label>
							<input
								id="login-email"
								v-model="email"
								type="email"
								required
								placeholder="o.teu@email.com"
								class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-white/20 text-sm focus:outline-none focus:border-k-main transition duration-200"
							/>
						</div>

						<!-- Password -->
						<div>
							<div class="flex items-center justify-between mb-2">
								<label class="block text-xs font-semibold text-white/60 uppercase tracking-wider" for="login-password">
									Password
								</label>
								<router-link
									to="/forgot-password"
									class="text-xs text-k-main/80 hover:text-k-main transition duration-200"
								>
									Esqueceste-te?
								</router-link>
							</div>
							<div class="relative">
								<input
									id="login-password"
									v-model="password"
									:type="showPassword ? 'text' : 'password'"
									required
									placeholder="••••••••"
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
						</div>

						<!-- Botão -->
						<button
							type="submit"
							:disabled="auth.isLoading"
							class="w-full bg-k-main text-k-black font-bold py-3.5 rounded-xl tracking-wider uppercase text-sm hover:bg-yellow-400 active:translate-y-0.5 transition duration-200 disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2"
						>
							<svg v-if="auth.isLoading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
								<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
								<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
							</svg>
							{{ auth.isLoading ? 'A entrar...' : 'Entrar' }}
						</button>
					</form>

					<!-- Link registo -->
					<p class="text-center text-white/40 text-sm mt-6">
						Ainda não tens conta?
						<router-link to="/register" class="text-k-main hover:text-yellow-400 font-semibold transition duration-200">
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
