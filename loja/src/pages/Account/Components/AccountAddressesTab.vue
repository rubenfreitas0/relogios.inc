<script setup lang="ts">
import { useAccountStore, type Address } from '../../../pinia/accountStore'
import { onMounted, ref, reactive } from 'vue'

const store = useAccountStore()

// Modal state
const showModal = ref(false)
const isEditing = ref(false)
const editingId = ref<number | null>(null)
const showDeleteConfirm = ref(false)
const deletingId = ref<number | null>(null)
const isSaving = ref(false)

const form = reactive({
	firstname: '',
	lastname: '',
	phone: '',
	address_line1: '',
	address_line2: '',
	city: '',
	postal_code: '',
	country: 'PT',
})

onMounted(() => {
	store.fetchAddresses()
})

function openCreateModal() {
	isEditing.value = false
	editingId.value = null
	resetForm()
	showModal.value = true
}

function openEditModal(address: Address) {
	isEditing.value = true
	editingId.value = address.id
	form.firstname = address.firstname
	form.lastname = address.lastname
	form.phone = address.phone || ''
	form.address_line1 = address.address_line1
	form.address_line2 = address.address_line2 || ''
	form.city = address.city
	form.postal_code = address.postal_code
	form.country = address.country
	showModal.value = true
}

function resetForm() {
	form.firstname = ''
	form.lastname = ''
	form.phone = ''
	form.address_line1 = ''
	form.address_line2 = ''
	form.city = ''
	form.postal_code = ''
	form.country = 'PT'
}

async function saveAddress() {
	isSaving.value = true
	let success: boolean
	if (isEditing.value && editingId.value) {
		success = await store.updateAddress(editingId.value, { ...form })
	} else {
		success = await store.createAddress({ ...form })
	}
	isSaving.value = false
	if (success) {
		showModal.value = false
	}
}

function confirmDelete(id: number) {
	deletingId.value = id
	showDeleteConfirm.value = true
}

async function executeDelete() {
	if (deletingId.value) {
		await store.deleteAddress(deletingId.value)
	}
	showDeleteConfirm.value = false
	deletingId.value = null
}

async function makeDefault(id: number) {
	await store.setDefaultAddress(id)
}
</script>

<template>
	<div>
		<!-- Header -->
		<div class="mb-6 flex items-center justify-between">
			<p class="text-sm text-white/40">
				{{ store.addresses.length }} morada{{
					store.addresses.length !== 1 ? 's' : ''
				}}
				guardada{{ store.addresses.length !== 1 ? 's' : '' }}
			</p>
			<button
				@click="openCreateModal"
				class="flex items-center gap-2 rounded-lg bg-[#FFC700] px-4 py-2 text-xs font-bold uppercase tracking-wider text-black transition-colors hover:bg-yellow-400"
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
						d="M12 4v16m8-8H4"
					/>
				</svg>
				Nova Morada
			</button>
		</div>

		<!-- Loading -->
		<div
			v-if="store.isLoading && store.addresses.length === 0"
			class="flex items-center justify-center py-20"
		>
			<div
				class="h-6 w-6 animate-spin rounded-full border-2 border-[#FFC700] border-t-transparent"
			></div>
		</div>

		<!-- Empty -->
		<div
			v-else-if="store.addresses.length === 0 && !store.isLoading"
			class="flex flex-col items-center justify-center py-20"
		>
			<svg
				class="mb-4 h-16 w-16 text-white/10"
				fill="none"
				viewBox="0 0 24 24"
				stroke="currentColor"
			>
				<path
					stroke-linecap="round"
					stroke-linejoin="round"
					stroke-width="1"
					d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
				/>
				<path
					stroke-linecap="round"
					stroke-linejoin="round"
					stroke-width="1"
					d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
				/>
			</svg>
			<p class="text-sm font-semibold uppercase tracking-wider text-white/30">
				Sem moradas guardadas
			</p>
			<p class="mt-1 text-xs text-white/20">
				Adiciona uma morada para agilizar o checkout.
			</p>
		</div>

		<!-- Addresses grid -->
		<div v-else class="grid gap-4 md:grid-cols-2">
			<div
				v-for="addr in store.addresses"
				:key="addr.id"
				class="relative rounded-xl border bg-white/[0.03] p-5 transition-all duration-200"
				:class="
					addr.is_default
						? 'border-[#FFC700]/30'
						: 'border-white/10 hover:border-white/20'
				"
			>
				<!-- Default badge -->
				<span
					v-if="addr.is_default"
					class="absolute -top-2.5 right-4 rounded-full bg-[#FFC700] px-3 py-0.5 text-[0.55rem] font-bold uppercase tracking-wider text-black"
				>
					Principal
				</span>

				<p class="font-semibold text-white">
					{{ addr.firstname }} {{ addr.lastname }}
				</p>
				<p class="mt-1 text-sm text-white/50">{{ addr.address_line1 }}</p>
				<p v-if="addr.address_line2" class="text-sm text-white/50">
					{{ addr.address_line2 }}
				</p>
				<p class="text-sm text-white/50">
					{{ addr.postal_code }} {{ addr.city }}
				</p>
				<p class="text-sm text-white/50">{{ addr.country }}</p>
				<p v-if="addr.phone" class="mt-1 text-xs text-white/30">
					📞 {{ addr.phone }}
				</p>

				<!-- Actions -->
				<div class="mt-4 flex items-center gap-3 border-t border-white/5 pt-3">
					<button
						v-if="!addr.is_default"
						@click="makeDefault(addr.id)"
						class="text-[0.65rem] font-bold uppercase tracking-wider text-[#FFC700]/60 transition-colors hover:text-[#FFC700]"
					>
						Definir principal
					</button>
					<button
						@click="openEditModal(addr)"
						class="text-[0.65rem] font-bold uppercase tracking-wider text-white/30 transition-colors hover:text-white"
					>
						Editar
					</button>
					<button
						@click="confirmDelete(addr.id)"
						class="text-[0.65rem] font-bold uppercase tracking-wider text-red-400/50 transition-colors hover:text-red-400"
					>
						Eliminar
					</button>
				</div>
			</div>
		</div>

		<!-- ── Create/Edit Modal ──────────────────────────────────── -->
		<Teleport to="body">
			<Transition name="modal">
				<div
					v-if="showModal"
					class="fixed inset-0 z-[200] flex items-center justify-center bg-black/70 p-4"
					@click.self="showModal = false"
				>
					<div
						class="w-full max-w-lg rounded-2xl border border-white/10 bg-[#111] p-6 shadow-2xl md:p-8"
					>
						<h3 class="mb-6 text-lg font-bold text-white">
							{{ isEditing ? 'Editar Morada' : 'Nova Morada' }}
						</h3>

						<!-- Error -->
						<div
							v-if="store.error"
							class="mb-4 rounded-lg border border-red-500/20 bg-red-500/5 p-3 text-xs text-red-400"
						>
							{{ store.error }}
						</div>

						<form @submit.prevent="saveAddress" class="space-y-4">
							<div class="grid gap-4 md:grid-cols-2">
								<div>
									<label
										class="mb-1 block text-[0.6rem] font-bold uppercase tracking-[0.15em] text-white/40"
										>Primeiro Nome *</label
									>
									<input
										v-model="form.firstname"
										type="text"
										required
										class="w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white outline-none transition-colors focus:border-[#FFC700]/50"
									/>
								</div>
								<div>
									<label
										class="mb-1 block text-[0.6rem] font-bold uppercase tracking-[0.15em] text-white/40"
										>Apelido *</label
									>
									<input
										v-model="form.lastname"
										type="text"
										required
										class="w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white outline-none transition-colors focus:border-[#FFC700]/50"
									/>
								</div>
							</div>
							<div>
								<label
									class="mb-1 block text-[0.6rem] font-bold uppercase tracking-[0.15em] text-white/40"
									>Telefone</label
								>
								<input
									v-model="form.phone"
									type="tel"
									class="w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white outline-none transition-colors focus:border-[#FFC700]/50"
								/>
							</div>
							<div>
								<label
									class="mb-1 block text-[0.6rem] font-bold uppercase tracking-[0.15em] text-white/40"
									>Morada *</label
								>
								<input
									v-model="form.address_line1"
									type="text"
									required
									class="w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white outline-none transition-colors focus:border-[#FFC700]/50"
								/>
							</div>
							<div>
								<label
									class="mb-1 block text-[0.6rem] font-bold uppercase tracking-[0.15em] text-white/40"
									>Morada (linha 2)</label
								>
								<input
									v-model="form.address_line2"
									type="text"
									class="w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white outline-none transition-colors focus:border-[#FFC700]/50"
								/>
							</div>
							<div class="grid gap-4 md:grid-cols-3">
								<div>
									<label
										class="mb-1 block text-[0.6rem] font-bold uppercase tracking-[0.15em] text-white/40"
										>Código Postal *</label
									>
									<input
										v-model="form.postal_code"
										type="text"
										required
										maxlength="8"
										class="w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white outline-none transition-colors focus:border-[#FFC700]/50"
									/>
								</div>
								<div>
									<label
										class="mb-1 block text-[0.6rem] font-bold uppercase tracking-[0.15em] text-white/40"
										>Cidade *</label
									>
									<input
										v-model="form.city"
										type="text"
										required
										class="w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white outline-none transition-colors focus:border-[#FFC700]/50"
									/>
								</div>
								<div>
									<label
										class="mb-1 block text-[0.6rem] font-bold uppercase tracking-[0.15em] text-white/40"
										>País *</label
									>
									<input
										v-model="form.country"
										type="text"
										required
										maxlength="2"
										class="w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2.5 text-sm uppercase text-white outline-none transition-colors focus:border-[#FFC700]/50"
									/>
								</div>
							</div>

							<div class="flex justify-end gap-3 pt-2">
								<button
									type="button"
									@click="showModal = false"
									class="rounded-lg border border-white/10 px-5 py-2 text-xs font-bold uppercase tracking-wider text-white/40 transition-colors hover:text-white"
								>
									Cancelar
								</button>
								<button
									type="submit"
									:disabled="isSaving"
									class="rounded-lg bg-[#FFC700] px-6 py-2 text-xs font-bold uppercase tracking-wider text-black transition-colors hover:bg-yellow-400 disabled:opacity-50"
								>
									{{
										isSaving ? 'A guardar...' : isEditing ? 'Guardar' : 'Criar'
									}}
								</button>
							</div>
						</form>
					</div>
				</div>
			</Transition>
		</Teleport>

		<!-- ── Delete Confirmation Modal ──────────────────────────── -->
		<Teleport to="body">
			<Transition name="modal">
				<div
					v-if="showDeleteConfirm"
					class="fixed inset-0 z-[200] flex items-center justify-center bg-black/70 p-4"
					@click.self="showDeleteConfirm = false"
				>
					<div
						class="w-full max-w-sm rounded-2xl border border-white/10 bg-[#111] p-6 text-center shadow-2xl"
					>
						<div
							class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-500/10"
						>
							<svg
								class="h-6 w-6 text-red-400"
								fill="none"
								viewBox="0 0 24 24"
								stroke="currentColor"
							>
								<path
									stroke-linecap="round"
									stroke-linejoin="round"
									stroke-width="2"
									d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
								/>
							</svg>
						</div>
						<h3 class="text-lg font-bold text-white">Eliminar morada?</h3>
						<p class="mt-2 text-sm text-white/40">Esta ação é irreversível.</p>
						<div class="mt-6 flex justify-center gap-3">
							<button
								@click="showDeleteConfirm = false"
								class="rounded-lg border border-white/10 px-5 py-2 text-xs font-bold uppercase tracking-wider text-white/40 transition-colors hover:text-white"
							>
								Cancelar
							</button>
							<button
								@click="executeDelete"
								class="rounded-lg bg-red-500 px-5 py-2 text-xs font-bold uppercase tracking-wider text-white transition-colors hover:bg-red-600"
							>
								Eliminar
							</button>
						</div>
					</div>
				</div>
			</Transition>
		</Teleport>
	</div>
</template>

<style scoped>
.modal-enter-active,
.modal-leave-active {
	transition: all 0.2s ease;
}
.modal-enter-from,
.modal-leave-to {
	opacity: 0;
}
.modal-enter-from > div,
.modal-leave-to > div {
	transform: scale(0.95) translateY(10px);
}
</style>
