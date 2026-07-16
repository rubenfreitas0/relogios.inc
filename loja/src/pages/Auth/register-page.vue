<script setup lang="ts">
import { ref, computed, onUnmounted } from 'vue'
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
    auth.error = `Demasiadas tentativas de registo. Aguarde ${cooldownSeconds.value}s.`

    if (cooldownTimer) clearInterval(cooldownTimer)
    cooldownTimer = window.setInterval(() => {
      if (cooldownSeconds.value > 1) {
        cooldownSeconds.value--
        auth.error = `Demasiadas tentativas de registo. Aguarde ${cooldownSeconds.value}s.`
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

onUnmounted(() => {
  if (cooldownTimer) clearInterval(cooldownTimer)
})

// Telefone tem de incluir o indicativo do país (+351, +34, ...)
const phoneValid = computed(() => {
  if (!phone.value) return true
  return /^\+\d{1,3}(?:\s?\d){6,12}$/.test(phone.value.trim())
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

const strengthLabel = computed(
  () => ['', 'Fraca', 'Razoável', 'Boa', 'Forte'][passwordStrength.value] ?? '',
)
const strengthColor = computed(
  () =>
    ['', 'bg-red-500', 'bg-yellow-500', 'bg-blue-400', 'bg-green-500'][
      passwordStrength.value
    ] ?? '',
)

async function handleRegister() {
  if (isRateLimited.value) return

  // Fallback to DOM values if browser autofill didn't trigger Vue's input events
  const fnEl = document.getElementById(
    'reg-firstname',
  ) as HTMLInputElement | null
  const lnEl = document.getElementById(
    'reg-lastname',
  ) as HTMLInputElement | null
  const emEl = document.getElementById('reg-email') as HTMLInputElement | null
  const phEl = document.getElementById('reg-phone') as HTMLInputElement | null
  const pwEl = document.getElementById(
    'reg-password',
  ) as HTMLInputElement | null
  const coEl = document.getElementById('reg-confirm') as HTMLInputElement | null

  if (fnEl && fnEl.value && !firstname.value) firstname.value = fnEl.value
  if (lnEl && lnEl.value && !lastname.value) lastname.value = lnEl.value
  if (emEl && emEl.value && !email.value) email.value = emEl.value
  if (phEl && phEl.value && !phone.value) phone.value = phEl.value
  if (pwEl && pwEl.value && !password.value) password.value = pwEl.value
  if (coEl && coEl.value && !passwordConfirmation.value)
    passwordConfirmation.value = coEl.value

  if (checkRateLimit()) return

  if (!passwordsMatch.value || !phoneValid.value) return
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
	<div class="flex min-h-screen w-screen flex-col bg-k-black">
		<Navigation color="k-black" />

		<main class="flex flex-1 items-center justify-center px-4 py-16">
			<!-- Glow decorativo -->
			<div class="pointer-events-none absolute inset-0 overflow-hidden">
				<div
					class="absolute -right-40 top-1/3 h-96 w-96 rounded-full bg-k-main opacity-5 blur-3xl"
				></div>
				<div
					class="absolute -left-40 bottom-1/3 h-96 w-96 rounded-full bg-k-main opacity-5 blur-3xl"
				></div>
			</div>

			<!-- Container 2 colunas -->
			<div class="relative w-full max-w-5xl">
				<div
					class="grid grid-cols-1 overflow-hidden rounded-2xl border border-white/10 bg-k-dark-grey shadow-2xl lg:grid-cols-2"
				>
					<!-- ===== LEFT: Branding ===== -->
					<div
						class="relative flex flex-col justify-between overflow-hidden bg-k-black p-10"
					>
						<!-- Padrão decorativo de fundo -->
						<div
							class="absolute inset-0 opacity-5"
							style="
								background-image: radial-gradient(
									circle,
									#ffc700 1px,
									transparent 1px
								);
								background-size: 28px 28px;
							"
						></div>
						<div
							class="absolute bottom-0 left-0 right-0 h-48 bg-gradient-to-t from-k-black to-transparent"
						></div>

						<!-- Conteúdo -->
						<div class="relative">
							<RouterLink
								to="/"
								class="inline-block text-2xl font-extrabold tracking-tight text-white transition duration-300 hover:text-k-main"
							>
								RELOGIOS<span class="text-k-main">.inc</span>
							</RouterLink>
						</div>

						<div class="relative mt-12">
							<!-- Accent bar -->
							<div class="mb-6 h-0.5 w-10 bg-k-main"></div>

							<h2 class="mb-4 text-3xl font-bold leading-tight text-white">
								O tempo é o teu<br />
								<span class="text-k-main">bem mais precioso.</span>
							</h2>
							<p class="mb-8 text-sm leading-relaxed text-white/50">
								Cria a tua conta e acede à nossa coleção exclusiva de relógios
								premium, acompanha as tuas encomendas e guarda as tuas moradas
								preferidas.
							</p>

							<!-- Benefícios -->
							<ul class="space-y-3">
								<li
									v-for="item in [
										'Acesso antecipado a novas coleções',
										'Gestão simplificada de encomendas',
										'Moradas guardadas para compra rápida',
									]"
									:key="item"
									class="flex items-center gap-3 text-sm text-white/60"
								>
									<div
										class="bg-k-main/15 flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full border border-k-main/30"
									>
										<svg
											class="h-2.5 w-2.5 text-k-main"
											fill="none"
											viewBox="0 0 24 24"
											stroke="currentColor"
										>
											<path
												stroke-linecap="round"
												stroke-linejoin="round"
												stroke-width="3"
												d="M5 13l4 4L19 7"
											/>
										</svg>
									</div>
									{{ item }}
								</li>
							</ul>
						</div>

						<!-- Footer do card esquerdo -->
						<div class="relative mt-10 border-t border-white/10 pt-6">
							<p class="text-xs text-white/30">
								Já tens conta?
								<RouterLink
									to="/login"
									class="ml-1 font-semibold text-k-main transition duration-200 hover:text-yellow-400"
								>
									Entra aqui →
								</RouterLink>
							</p>
						</div>
					</div>

					<!-- ===== RIGHT: Formulário ===== -->
					<div class="flex flex-col justify-center p-8 lg:p-10">
						<div class="mb-7">
							<h1 class="text-xl font-bold tracking-tight text-white">
								Criar conta
							</h1>
							<p class="mt-1 text-sm text-white/40">
								Preenche os campos abaixo para começar
							</p>
						</div>

						<!-- Erro -->
						<Transition name="fade-slide">
							<div
								v-if="auth.error"
								class="mb-5 flex items-start gap-3 rounded-xl border border-red-500/30 bg-red-500/10 p-3.5"
							>
								<svg
									class="mt-0.5 h-4 w-4 flex-shrink-0 text-red-400"
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

						<!-- Sucesso -->
						<Transition name="fade-slide">
							<div
								v-if="auth.successMessage"
								class="mb-5 flex items-start gap-3 rounded-xl border border-green-500/30 bg-green-500/10 p-3.5"
							>
								<svg
									class="mt-0.5 h-4 w-4 flex-shrink-0 text-green-400"
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
								<p class="text-sm text-green-400">{{ auth.successMessage }}</p>
							</div>
						</Transition>

						<form class="space-y-4" @submit.prevent="handleRegister">
							<!-- Row 1: Nome + Apelido -->
							<div class="grid grid-cols-2 gap-3">
								<div>
									<label
										class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-white/50"
										for="reg-firstname"
										>Nome</label
									>
									<input
										id="reg-firstname"
										v-model="firstname"
										name="firstname"
										autocomplete="given-name"
										type="text"
										required
										placeholder="João"
										class="input-field"
									/>
								</div>
								<div>
									<label
										class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-white/50"
										for="reg-lastname"
										>Apelido</label
									>
									<input
										id="reg-lastname"
										v-model="lastname"
										name="lastname"
										autocomplete="family-name"
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
									<label
										class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-white/50"
										for="reg-email"
										>Email</label
									>
									<input
										id="reg-email"
										v-model="email"
										name="email"
										autocomplete="email"
										type="email"
										required
										placeholder="o.teu@email.com"
										class="input-field"
									/>
								</div>
								<div>
									<label
										class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-white/50"
										for="reg-phone"
									>
										Telefone
										<span class="font-normal normal-case text-white/25"
											>(opcional)</span
										>
									</label>
									<input
										id="reg-phone"
										v-model="phone"
										name="phone"
										autocomplete="tel"
										type="tel"
										placeholder="+351 9XX XXX XXX"
										class="input-field"
									/>
									<p
										v-if="phone && !phoneValid"
										class="mt-1 text-[0.65rem] text-red-400"
									>
										Inclui o indicativo do país (ex.: +351 912345678)
									</p>
								</div>
							</div>

							<!-- Row 3: Password + Confirmar -->
							<div class="grid grid-cols-2 gap-3">
								<div>
									<label
										class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-white/50"
										for="reg-password"
										>Password</label
									>
									<div class="relative">
										<input
											id="reg-password"
											v-model="password"
											name="password"
											autocomplete="new-password"
											:type="showPassword ? 'text' : 'password'"
											required
											minlength="8"
											placeholder="Mín. 8 caracteres"
											class="input-field pr-10"
										/>
										<button
											type="button"
											class="absolute right-3 top-1/2 -translate-y-1/2 text-white/30 transition duration-200 hover:text-white/60"
											@click="showPassword = !showPassword"
										>
											<svg
												v-if="!showPassword"
												class="h-3.5 w-3.5"
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
												class="h-3.5 w-3.5"
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
									<div v-if="password" class="mt-1.5 flex items-center gap-1.5">
										<div class="flex flex-1 gap-0.5">
											<div
												v-for="i in 4"
												:key="i"
												class="h-0.5 flex-1 rounded-full transition-all duration-300"
												:class="
													i <= passwordStrength ? strengthColor : 'bg-white/10'
												"
											></div>
										</div>
										<span class="text-xs text-white/40">{{
											strengthLabel
										}}</span>
									</div>
								</div>
								<div>
									<label
										class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-white/50"
										for="reg-confirm"
										>Confirmar</label
									>
									<div class="relative">
										<input
											id="reg-confirm"
											v-model="passwordConfirmation"
											name="password_confirmation"
											autocomplete="new-password"
											:type="showConfirmPassword ? 'text' : 'password'"
											required
											placeholder="Repete a password"
											class="input-field pr-10"
											:class="
												passwordConfirmation && !passwordsMatch
													? '!border-red-500/60'
													: ''
											"
										/>
										<button
											type="button"
											class="absolute right-3 top-1/2 -translate-y-1/2 text-white/30 transition duration-200 hover:text-white/60"
											@click="showConfirmPassword = !showConfirmPassword"
										>
											<svg
												v-if="!showConfirmPassword"
												class="h-3.5 w-3.5"
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
												class="h-3.5 w-3.5"
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
											Não coincidem.
										</p>
									</Transition>
								</div>
							</div>

							<!-- CTA -->
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
										? 'A criar conta...'
										: 'Criar conta'
								}}
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
	@apply w-full rounded-xl border border-white/10 bg-white/5 px-3.5 py-2.5 text-sm text-white placeholder-white/20 transition duration-200 focus:border-k-main focus:outline-none;
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
