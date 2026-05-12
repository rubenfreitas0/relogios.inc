<script setup lang="ts">
import { useAuthStore } from '../../../pinia/authStore'

const authStore = useAuthStore()
</script>

<template>
	<div>
		<!-- Card do perfil -->
		<div class="rounded-xl border border-white/10 bg-white/[0.03] p-6 md:p-8">
			<div class="mb-6 flex items-center gap-4">
				<div class="flex h-14 w-14 items-center justify-center rounded-full bg-[#FFC700] text-xl font-black text-black">
					{{ authStore.user?.firstname?.charAt(0)?.toUpperCase() }}{{ authStore.user?.lastname?.charAt(0)?.toUpperCase() }}
				</div>
				<div>
					<h2 class="text-lg font-bold text-white">
						{{ authStore.user?.firstname }} {{ authStore.user?.lastname }}
					</h2>
					<div class="flex items-center gap-2">
						<span class="text-sm text-white/40">{{ authStore.user?.email }}</span>
						<span
							v-if="authStore.user?.email_verified_at"
							class="inline-flex items-center gap-1 rounded-full bg-green-500/10 px-2 py-0.5 text-[0.6rem] font-bold uppercase tracking-wider text-green-400"
						>
							<svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
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

			<!-- Info grid -->
			<div class="grid gap-6 md:grid-cols-2">
				<div>
					<label class="mb-1 block text-[0.65rem] font-bold uppercase tracking-[0.15em] text-[#FFC700]">
						Primeiro Nome
					</label>
					<p class="rounded-lg border border-white/10 bg-white/[0.03] px-4 py-3 text-sm text-white/70">
						{{ authStore.user?.firstname || '—' }}
					</p>
				</div>
				<div>
					<label class="mb-1 block text-[0.65rem] font-bold uppercase tracking-[0.15em] text-[#FFC700]">
						Apelido
					</label>
					<p class="rounded-lg border border-white/10 bg-white/[0.03] px-4 py-3 text-sm text-white/70">
						{{ authStore.user?.lastname || '—' }}
					</p>
				</div>
				<div>
					<label class="mb-1 block text-[0.65rem] font-bold uppercase tracking-[0.15em] text-[#FFC700]">
						Email
					</label>
					<p class="rounded-lg border border-white/10 bg-white/[0.03] px-4 py-3 text-sm text-white/70">
						{{ authStore.user?.email || '—' }}
					</p>
				</div>
				<div>
					<label class="mb-1 block text-[0.65rem] font-bold uppercase tracking-[0.15em] text-[#FFC700]">
						Telefone
					</label>
					<p class="rounded-lg border border-white/10 bg-white/[0.03] px-4 py-3 text-sm text-white/70">
						{{ authStore.user?.phone || 'Não definido' }}
					</p>
				</div>
			</div>
		</div>

		<!-- Nota -->
		<p class="mt-6 text-xs text-white/25 text-center">
			Para alterar os teus dados, contacta o suporte.
		</p>
	</div>
</template>
