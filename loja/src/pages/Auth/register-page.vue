<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../pinia/authStore'
import Navigation from '../../components/navigation-global.vue'
import Footer from '../../components/footer-global.vue'

const auth = useAuthStore()
const router = useRouter()

const firstname = ref('')
const lastname = ref('')
const email = ref('')
const phone = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const showPassword = ref(false)
const showConfirmPassword = ref(false)

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

const strengthLabel = computed(() => ['', 'Fraca', 'Razoável', 'Boa', 'Forte'][passwordStrength.value] ?? '')
const strengthColor = computed(() => ['', 'bg-red-500', 'bg-yellow-500', 'bg-blue-400', 'bg-green-500'][passwordStrength.value] ?? '')

async function handleRegister() {
	if (!passwordsMatch.value) return
	const ok = await auth.register({
		firstname: firstname.value,
		lastname: lastname.value,
		email: email.value,
		password: password.value,
		password_confirmation: passwordConfirmation.value,
		phone: phone.value || undefined,
	})
	if (ok) router.push('/')
}
</script>

<template>
	<div class="min-h-screen w-screen bg-k-black flex flex-col">
		<Navigation color="k-black" />

		<main class="flex-1 flex items-center justify-center px-4 py-16">
			<!-- Glow decorativo -->
			<div class="absolute inset-0 overflow-hidden pointer-events-none">
				<div class="absolute top-1/3 -right-40 w-96 h-96 rounded-full bg-k-main opacity-5 blur-3xl"></div>
				<div class="absolute bottom-1/3 -left-40 w-96 h-96 rounded-full bg-k-main opacity-5 blur-3xl"></div>
			</div>

			<!-- Container 2 colunas -->
			<div class="relative w-full max-w-5xl">
				<div class="grid grid-cols-1 lg:grid-cols-2 bg-k-dark-grey border border-white/10 rounded-2xl shadow-2xl overflow-hidden">

					<!-- ===== LEFT: Branding ===== -->
					<div class="relative flex flex-col justify-between p-10 bg-k-black overflow-hidden">
						<!-- Padrão decorativo de fundo -->
						<div class="absolute inset-0 opacity-5" style="background-image: radial-gradient(circle, #FFC700 1px, transparent 1px); background-size: 28px 28px;"></div>
						<div class="absolute bottom-0 left-0 right-0 h-48 bg-gradient-to-t from-k-black to-transparent"></div>

						<!-- Conteúdo -->
						<div class="relative">
							<router-link
								to="/"
								class="text-2xl font-extrabold tracking-tight text-white hover:text-k-main transition duration-300 inline-block"
							>
								RELOGIOS<span class="text-k-main">.inc</span>
							</router-link>
						</div>

						<div class="relative mt-12">
							<!-- Accent bar -->
							<div class="w-10 h-0.5 bg-k-main mb-6"></div>

							<h2 class="text-3xl font-bold text-white leading-tight mb-4">
								O tempo é o teu<br/>
								<span class="text-k-main">bem mais precioso.</span>
							</h2>
							<p class="text-white/50 text-sm leading-relaxed mb-8">
								Cria a tua conta e acede à nossa coleção exclusiva de relógios premium, acompanha as tuas encomendas e guarda as tuas moradas preferidas.
							</p>

							<!-- Benefícios -->
							<ul class="space-y-3">
								<li v-for="item in ['Acesso antecipado a novas coleções', 'Gestão simplificada de encomendas', 'Moradas guardadas para compra rápida']" :key="item"
									class="flex items-center gap-3 text-white/60 text-sm"
								>
									<div class="flex-shrink-0 w-5 h-5 rounded-full bg-k-main/15 border border-k-main/30 flex items-center justify-center">
										<svg class="w-2.5 h-2.5 text-k-main" fill="none" viewBox="0 0 24 24" stroke="currentColor">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
										</svg>
									</div>
									{{ item }}
								</li>
							</ul>
						</div>

						<!-- Footer do card esquerdo -->
						<div class="relative mt-10 pt-6 border-t border-white/10">
							<p class="text-white/30 text-xs">
								Já tens conta?
								<router-link to="/login" class="text-k-main hover:text-yellow-400 font-semibold transition duration-200 ml-1">
									Entra aqui →
								</router-link>
							</p>
						</div>
					</div>

					<!-- ===== RIGHT: Formulário ===== -->
					<div class="p-8 lg:p-10 flex flex-col justify-center">
						<div class="mb-7">
							<h1 class="text-xl font-bold text-white tracking-tight">Criar conta</h1>
							<p class="text-white/40 text-sm mt-1">Preenche os campos abaixo para começar</p>
						</div>

						<!-- Erro -->
						<Transition name="fade-slide">
							<div v-if="auth.error" class="mb-5 flex items-start gap-3 bg-red-500/10 border border-red-500/30 rounded-xl p-3.5">
								<svg class="w-4 h-4 text-red-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
								</svg>
								<p class="text-red-400 text-sm">{{ auth.error }}</p>
							</div>
						</Transition>

						<!-- Sucesso -->
						<Transition name="fade-slide">
							<div v-if="auth.successMessage" class="mb-5 flex items-start gap-3 bg-green-500/10 border border-green-500/30 rounded-xl p-3.5">
								<svg class="w-4 h-4 text-green-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
								</svg>
								<p class="text-green-400 text-sm">{{ auth.successMessage }}</p>
							</div>
						</Transition>

						<form @submit.prevent="handleRegister" class="space-y-4">
							<!-- Row 1: Nome + Apelido -->
							<div class="grid grid-cols-2 gap-3">
								<div>
									<label class="block text-xs font-semibold text-white/50 uppercase tracking-wider mb-1.5" for="reg-firstname">Nome</label>
									<input
										id="reg-firstname"
										v-model="firstname"
										type="text"
										required
										placeholder="João"
										class="input-field"
									/>
								</div>
								<div>
									<label class="block text-xs font-semibold text-white/50 uppercase tracking-wider mb-1.5" for="reg-lastname">Apelido</label>
									<input
										id="reg-lastname"
										v-model="lastname"
										type="text"
										required
										placeholder="Silva"
										class="input-field"
									/>
								</div>
							</div>

							<!-- Row 2: Email + Telefone -->
							<div class="grid grid-cols-2 gap-3">
								<div>
									<label class="block text-xs font-semibold text-white/50 uppercase tracking-wider mb-1.5" for="reg-email">Email</label>
									<input
										id="reg-email"
										v-model="email"
										type="email"
										required
										placeholder="o.teu@email.com"
										class="input-field"
									/>
								</div>
								<div>
									<label class="block text-xs font-semibold text-white/50 uppercase tracking-wider mb-1.5" for="reg-phone">
										Telefone <span class="text-white/25 normal-case font-normal">(opcional)</span>
									</label>
									<input
										id="reg-phone"
										v-model="phone"
										type="tel"
										placeholder="+351 9XX XXX XXX"
										class="input-field"
									/>
								</div>
							</div>

							<!-- Row 3: Password + Confirmar -->
							<div class="grid grid-cols-2 gap-3">
								<div>
									<label class="block text-xs font-semibold text-white/50 uppercase tracking-wider mb-1.5" for="reg-password">Password</label>
									<div class="relative">
										<input
											id="reg-password"
											v-model="password"
											:type="showPassword ? 'text' : 'password'"
											required
											minlength="8"
											placeholder="Mín. 8 caracteres"
											class="input-field pr-10"
										/>
										<button type="button" @click="showPassword = !showPassword"
											class="absolute right-3 top-1/2 -translate-y-1/2 text-white/30 hover:text-white/60 transition duration-200">
											<svg v-if="!showPassword" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
												<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
												<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
											</svg>
											<svg v-else class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
												<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
											</svg>
										</button>
									</div>
									<!-- Barra de força -->
									<div v-if="password" class="mt-1.5 flex items-center gap-1.5">
										<div class="flex gap-0.5 flex-1">
											<div v-for="i in 4" :key="i" class="h-0.5 flex-1 rounded-full transition-all duration-300"
												:class="i <= passwordStrength ? strengthColor : 'bg-white/10'"></div>
										</div>
										<span class="text-xs text-white/40">{{ strengthLabel }}</span>
									</div>
								</div>
								<div>
									<label class="block text-xs font-semibold text-white/50 uppercase tracking-wider mb-1.5" for="reg-confirm">Confirmar</label>
									<div class="relative">
										<input
											id="reg-confirm"
											v-model="passwordConfirmation"
											:type="showConfirmPassword ? 'text' : 'password'"
											required
											placeholder="Repete a password"
											class="input-field pr-10"
											:class="passwordConfirmation && !passwordsMatch ? '!border-red-500/60' : ''"
										/>
										<button type="button" @click="showConfirmPassword = !showConfirmPassword"
											class="absolute right-3 top-1/2 -translate-y-1/2 text-white/30 hover:text-white/60 transition duration-200">
											<svg v-if="!showConfirmPassword" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
												<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
												<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
											</svg>
											<svg v-else class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
												<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
											</svg>
										</button>
									</div>
									<Transition name="fade-slide">
										<p v-if="passwordConfirmation && !passwordsMatch" class="mt-1.5 text-xs text-red-400">Não coincidem.</p>
									</Transition>
								</div>
							</div>

							<!-- CTA -->
							<button
								type="submit"
								:disabled="auth.isLoading || !passwordsMatch"
								class="w-full bg-k-main text-k-black font-bold py-3.5 rounded-xl tracking-wider uppercase text-sm hover:bg-yellow-400 active:translate-y-0.5 transition duration-200 disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2 mt-2"
							>
								<svg v-if="auth.isLoading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
									<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
									<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
								</svg>
								{{ auth.isLoading ? 'A criar conta...' : 'Criar conta' }}
							</button>
						</form>
					</div>

				</div>
			</div>
		</main>

		<Footer />
	</div>
</template>

<style scoped>
.input-field {
	@apply w-full bg-white/5 border border-white/10 rounded-xl px-3.5 py-2.5 text-white placeholder-white/20 text-sm focus:outline-none focus:border-k-main transition duration-200;
}
.input-field:focus {
	background-color: rgba(255, 199, 0, 0.04);
}

.fade-slide-enter-active,
.fade-slide-leave-active {
	transition: all 0.25s ease;
}
.fade-slide-enter-from,
.fade-slide-leave-to {
	opacity: 0;
	transform: translateY(-6px);
}
</style>
