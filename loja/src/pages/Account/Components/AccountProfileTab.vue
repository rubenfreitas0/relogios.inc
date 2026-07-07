<script setup lang="ts">
import { ref, watch } from 'vue'
import { useAuthStore } from '../../../pinia/authStore'

const authStore = useAuthStore()
const isEditing = ref(false)
const phoneInput = ref(authStore.user?.phone || '')
const successMsg = ref('')
const errorMsg = ref('')
const isSubmitting = ref(false)

const resendingVerification = ref(false)
const resendSuccess = ref(false)
const resendError = ref('')

watch(
	() => authStore.user,
	(newUser) => {
		if (newUser) {
			phoneInput.value = newUser.phone || ''
		}
	},
	{ immediate: true },
)

function cancelEdit() {
	phoneInput.value = authStore.user?.phone || ''
	isEditing.value = false
	successMsg.value = ''
	errorMsg.value = ''
}

async function saveProfile() {
	isSubmitting.value = true
	successMsg.value = ''
	errorMsg.value = ''
	
	const ok = await authStore.updateProfile({
		phone: phoneInput.value || null,
	})
	
	isSubmitting.value = false
	
	if (ok) {
		successMsg.value = 'Telemóvel atualizado com sucesso!'
		isEditing.value = false
	} else {
		errorMsg.value = authStore.error || 'Erro ao atualizar telemóvel.'
	}
}

async function handleResendVerification() {
	resendingVerification.value = true
	resendSuccess.value = false
	resendError.value = ''

	const ok = await authStore.resendVerification()

	resendingVerification.value = false
	if (ok) {
		resendSuccess.value = true
	} else {
		resendError.value = authStore.error || 'Erro ao enviar email.'
	}
}
</script>

<template>
	<div>
		<!-- Card do perfil -->
		<div class="rounded-xl border border-white/10 bg-white/[0.03] p-6 md:p-8">
			<div class="mb-6 flex items-center gap-4">
				<div
					class="flex h-14 w-14 items-center justify-center rounded-full bg-[#FFC700] text-xl font-black text-black"
				>
					{{ authStore.user?.firstname?.charAt(0)?.toUpperCase()
					}}{{ authStore.user?.lastname?.charAt(0)?.toUpperCase() }}
				</div>
				<div>
					<h2 class="text-lg font-bold text-white">
						{{ authStore.user?.firstname }} {{ authStore.user?.lastname }}
					</h2>
					<div class="flex items-center gap-2">
						<span class="text-sm text-white/40">{{
							authStore.user?.email
						}}</span>
						<span
							v-if="authStore.user?.email_verified_at"
							class="inline-flex items-center gap-1 rounded-full bg-green-500/10 px-2 py-0.5 text-[0.6rem] font-bold uppercase tracking-wider text-green-400"
						>
							<svg
								class="h-3 w-3"
								fill="none"
								viewBox="0 0 24 24"
								stroke="currentColor"
							>
								<path
									stroke-linecap="round"
									stroke-linejoin="round"
									stroke-width="2.5"
									d="M5 13l4 4L19 7"
								/>
							</svg>
							Verificado
						</span>
						<span
							v-else
							class="inline-flex items-center gap-1 rounded-full bg-amber-500/10 px-2 py-0.5 text-[0.6rem] font-bold uppercase tracking-wider text-amber-400"
						>
							Não verificado
						</span>
					</div>
				</div>
			</div>

			<!-- Mensagens de Feedback -->
			<div v-if="successMsg" class="mb-4 text-xs font-semibold text-green-400">
				{{ successMsg }}
			</div>
			<div v-if="errorMsg" class="mb-4 text-xs font-semibold text-red-400">
				{{ errorMsg }}
			</div>

			<!-- Info grid -->
			<div class="grid gap-6 md:grid-cols-2">
				<div>
					<label
						class="mb-1 block text-[0.65rem] font-bold uppercase tracking-[0.15em] text-[#FFC700]"
					>
						Primeiro Nome
					</label>
					<p
						class="rounded-lg border border-white/10 bg-white/[0.03] px-4 py-3 text-sm text-white/70"
					>
						{{ authStore.user?.firstname || '—' }}
					</p>
				</div>
				<div>
					<label
						class="mb-1 block text-[0.65rem] font-bold uppercase tracking-[0.15em] text-[#FFC700]"
					>
						Apelido
					</label>
					<p
						class="rounded-lg border border-white/10 bg-white/[0.03] px-4 py-3 text-sm text-white/70"
					>
						{{ authStore.user?.lastname || '—' }}
					</p>
				</div>
				<div>
					<label
						class="mb-1 block text-[0.65rem] font-bold uppercase tracking-[0.15em] text-[#FFC700]"
					>
						Email
					</label>
					<p
						class="rounded-lg border border-white/10 bg-white/[0.03] px-4 py-3 text-sm text-white/70"
					>
						{{ authStore.user?.email || '—' }}
					</p>
				</div>
				<div>
					<label
						class="mb-1 block text-[0.65rem] font-bold uppercase tracking-[0.15em] text-[#FFC700]"
					>
						Telefone
					</label>
					<div v-if="isEditing">
						<input
							v-model="phoneInput"
							type="text"
							placeholder="Ex: 912345678"
							class="w-full rounded-lg border border-white/10 bg-white/[0.03] px-4 py-3 text-sm text-white focus:border-[#FFC700] focus:outline-none"
						/>
					</div>
					<p
						v-else
						class="rounded-lg border border-white/10 bg-white/[0.03] px-4 py-3 text-sm text-white/70"
					>
						{{ authStore.user?.phone || 'Não definido' }}
					</p>
				</div>
			</div>

			<!-- Mensagens de Verificação -->
			<div v-if="resendSuccess" class="mb-4 text-xs font-semibold text-green-400">
				Email de verificação enviado! Verifica a tua caixa de entrada.
			</div>
			<div v-if="resendError" class="mb-4 text-xs font-semibold text-red-400">
				{{ resendError }}
			</div>

			<!-- Botões de Ação -->
			<div class="mt-6 flex flex-wrap justify-end gap-3 border-t border-white/5 pt-6">
				<button
					v-if="!isEditing && !authStore.user?.email_verified_at"
					type="button"
					:disabled="resendingVerification"
					class="rounded-lg border border-amber-400/40 px-4 py-2 text-xs font-semibold text-amber-400 hover:border-amber-400 hover:bg-amber-400/10 disabled:opacity-50"
					@click="handleResendVerification"
				>
					{{ resendingVerification ? 'A enviar...' : 'Verificar Conta' }}
				</button>

				<template v-if="isEditing">
					<button
						type="button"
						:disabled="isSubmitting"
						class="rounded-lg px-4 py-2 text-xs font-semibold text-white/60 hover:text-white disabled:opacity-50"
						@click="cancelEdit"
					>
						Cancelar
					</button>
					<button
						type="button"
						:disabled="isSubmitting"
						class="rounded-lg bg-[#FFC700] px-4 py-2 text-xs font-bold text-black hover:bg-yellow-400 disabled:opacity-50"
						@click="saveProfile"
					>
						{{ isSubmitting ? 'A guardar...' : 'Guardar' }}
					</button>
				</template>
				<button
					v-else
					type="button"
					class="rounded-lg border border-white/20 px-4 py-2 text-xs font-semibold text-white hover:border-white hover:bg-white/5"
					@click="isEditing = true"
				>
					Editar Telemóvel
				</button>
			</div>
		</div>

		<!-- Nota -->
		<p class="mt-6 text-center text-xs text-white/25">
			Para alterar os outros dados, contacta o suporte.
		</p>
	</div>
</template>
