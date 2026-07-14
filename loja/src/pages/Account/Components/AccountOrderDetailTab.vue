<script setup lang="ts">
import { useAccountStore } from '../../../pinia/accountStore'
import { onMounted, watch, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { resolveProductImageUrl } from '../../../utils/utilities'

const store = useAccountStore()
const route = useRoute()
const router = useRouter()

const orderNumber = computed(() => route.params.orderNumber as string)

onMounted(() => {
  if (orderNumber.value) {
    store.fetchOrderDetail(orderNumber.value)
  }
})

watch(orderNumber, (newVal) => {
  if (newVal) store.fetchOrderDetail(newVal)
})

function goBack() {
  router.push('/conta/encomendas')
}

function formatDate(iso: string): string {
  return new Date(iso).toLocaleDateString('pt-PT', {
    day: '2-digit',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

function formatPrice(value: number | string): string {
  const num = Number(value)
  if (isNaN(num)) return '0,00'
  return num.toFixed(2).replace('.', ',')
}

const statusColor = (value: string) => {
  switch (value) {
  case 'pending':
    return 'bg-amber-500/10 text-amber-400 border-amber-500/20'
  case 'processing':
    return 'bg-blue-500/10 text-blue-400 border-blue-500/20'
  case 'shipped':
    return 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20'
  case 'delivered':
    return 'bg-green-500/10 text-green-400 border-green-500/20'
  case 'cancelled':
    return 'bg-red-500/10 text-red-400 border-red-500/20'
  case 'refunded':
    return 'bg-gray-500/10 text-gray-400 border-gray-500/20'
  default:
    return 'bg-white/5 text-white/50 border-white/10'
  }
}

// Timeline steps
const timelineSteps = ['pending', 'processing', 'shipped', 'delivered']
const timelineLabels: Record<string, string> = {
  pending: 'Pendente',
  processing: 'Em Processamento',
  shipped: 'Enviado',
  delivered: 'Entregue',
}

const currentStepIndex = computed(() => {
  if (!store.currentOrder) return -1
  const val = store.currentOrder.status.value
  if (val === 'cancelled' || val === 'refunded') return -1
  return timelineSteps.indexOf(val)
})

const payment = computed(() => store.currentOrder?.payments?.[0])

function formatReference(ref: string | number): string {
  const str = String(ref)
  return `${str.substring(0, 3)} ${str.substring(3, 6)} ${str.substring(6, 9)}`
}
</script>

<template>
	<div>
		<!-- Loading -->
		<div v-if="store.isLoading" class="flex items-center justify-center py-20">
			<div
				class="h-6 w-6 animate-spin rounded-full border-2 border-[#FFC700] border-t-transparent"
			></div>
		</div>

		<!-- Error -->
		<div
			v-else-if="store.error"
			class="rounded-xl border border-red-500/20 bg-red-500/5 p-6 text-center"
		>
			<p class="text-sm text-red-400">{{ store.error }}</p>
			<button
				class="mt-4 text-xs font-bold uppercase tracking-wider text-[#FFC700] hover:text-yellow-300"
				@click="goBack"
			>
				← Voltar às encomendas
			</button>
		</div>

		<!-- Order detail -->
		<div v-else-if="store.currentOrder">
			<!-- Back + Header -->
			<button
				class="mb-6 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-white/40 transition-colors hover:text-[#FFC700]"
				@click="goBack"
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
						d="M15 19l-7-7 7-7"
					/>
				</svg>
				Voltar
			</button>

			<div
				class="mb-8 flex flex-col gap-3 md:flex-row md:items-center md:justify-between"
			>
				<div>
					<h2 class="text-xl font-bold tracking-wide text-white">
						Encomenda #{{ store.currentOrder.order_number }}
					</h2>
					<p class="mt-1 text-xs text-white/30">
						{{ formatDate(store.currentOrder.created_at) }}
					</p>
				</div>
				<span
					class="inline-flex self-start rounded-full border px-4 py-1.5 text-[0.65rem] font-bold uppercase tracking-wider"
					:class="statusColor(store.currentOrder.status.value)"
				>
					{{ store.currentOrder.status.label }}
				</span>
			</div>

			<!-- Timeline (only for non-cancelled orders) -->
			<div
				v-if="currentStepIndex >= 0"
				class="mb-10 rounded-xl border border-white/10 bg-white/[0.03] p-6"
			>
				<div class="flex items-center justify-between">
					<div
						v-for="(step, i) in timelineSteps"
						:key="step"
						class="flex flex-1 flex-col items-center"
					>
						<div
							class="flex h-8 w-8 items-center justify-center rounded-full border-2 text-xs font-bold transition-all"
							:class="
								i <= currentStepIndex
									? 'border-[#FFC700] bg-[#FFC700] text-black'
									: 'border-white/15 text-white/20'
							"
						>
							<svg
								v-if="i < currentStepIndex"
								class="h-4 w-4"
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
							<span v-else>{{ i + 1 }}</span>
						</div>
						<p
							class="mt-2 text-center text-[0.6rem] font-bold uppercase tracking-wider"
							:class="
								i <= currentStepIndex ? 'text-[#FFC700]' : 'text-white/20'
							"
						>
							{{ timelineLabels[step] }}
						</p>
					</div>
				</div>
			</div>

			<!-- Items -->
			<div class="mb-6 rounded-xl border border-white/10 bg-white/[0.03] p-6">
				<h3
					class="mb-4 text-[0.65rem] font-bold uppercase tracking-[0.15em] text-[#FFC700]"
				>
					Artigos
				</h3>
				<div class="space-y-4">
					<div
						v-for="item in store.currentOrder.items"
						:key="item.id"
						class="flex items-center gap-4"
					>
						<div
							class="h-14 w-14 flex-shrink-0 overflow-hidden rounded-lg bg-white/5"
						>
							<img
								v-if="item.product_image"
								:src="resolveProductImageUrl(item.product_image)"
								:alt="item.product_name"
								class="h-full w-full object-cover"
							/>
							<div
								v-else
								class="flex h-full w-full items-center justify-center text-white/10"
							>
								<svg
									class="h-6 w-6"
									fill="none"
									viewBox="0 0 24 24"
									stroke="currentColor"
								>
									<path
										stroke-linecap="round"
										stroke-linejoin="round"
										stroke-width="1"
										d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
									/>
								</svg>
							</div>
						</div>
						<div class="min-w-0 flex-1">
							<p class="truncate text-sm font-semibold text-white">
								{{ item.product_name }}
							</p>
							<p class="text-xs text-white/30">
								Qtd: {{ item.quantity }} × €{{ formatPrice(item.unit_price) }}
							</p>
						</div>
						<p class="text-sm font-bold text-white">
							€{{ formatPrice(item.item_total) }}
						</p>
					</div>
				</div>
			</div>

			<!-- Summary + Shipping + Payment grid -->
			<div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
				<!-- Resumo financeiro -->
				<div class="rounded-xl border border-white/10 bg-white/[0.03] p-6">
					<h3
						class="mb-4 text-[0.65rem] font-bold uppercase tracking-[0.15em] text-[#FFC700]"
					>
						Resumo
					</h3>
					<div class="space-y-2 text-sm">
						<div class="flex justify-between">
							<span class="text-white/40">Subtotal</span
							><span class="text-white/70"
								>€{{ formatPrice(store.currentOrder.subtotal) }}</span
							>
						</div>
						<div class="flex justify-between">
							<span class="text-white/40">Envio</span
							><span class="text-white/70"
								>€{{ formatPrice(store.currentOrder.shipping_cost) }}</span
							>
						</div>
						<div class="flex justify-between border-t border-white/10 pt-2">
							<span class="font-bold text-white">Total</span
							><span class="font-bold text-[#FFC700]"
								>€{{ formatPrice(store.currentOrder.total) }}</span
							>
						</div>
					</div>
				</div>

				<!-- Morada de envio -->
				<div class="rounded-xl border border-white/10 bg-white/[0.03] p-6">
					<h3
						class="mb-4 text-[0.65rem] font-bold uppercase tracking-[0.15em] text-[#FFC700]"
					>
						Envio
					</h3>
					<div class="space-y-1 text-sm text-white/60">
						<p class="font-semibold text-white">
							{{ store.currentOrder.customer.firstname }}
							{{ store.currentOrder.customer.lastname }}
						</p>
						<p>{{ store.currentOrder.shipping_address.address_line1 }}</p>
						<p v-if="store.currentOrder.shipping_address.address_line2">
							{{ store.currentOrder.shipping_address.address_line2 }}
						</p>
						<p>
							{{ store.currentOrder.shipping_address.postal_code }}
							{{ store.currentOrder.shipping_address.city }}
						</p>
						<p>{{ store.currentOrder.shipping_address.country }}</p>
						<p
							v-if="store.currentOrder.shipping_method"
							class="mt-3 text-xs text-white/30"
						>
							{{ store.currentOrder.shipping_method.carrier }} —
							{{ store.currentOrder.shipping_method.name }}
						</p>
						<p v-if="store.currentOrder.tracking_number" class="mt-2">
							<span class="text-xs text-white/30">Tracking: </span>
							<a
								v-if="store.currentOrder.tracking_url"
								:href="store.currentOrder.tracking_url"
								target="_blank"
								rel="noopener"
								class="text-xs font-bold text-[#FFC700] hover:text-yellow-300"
							>
								{{ store.currentOrder.tracking_number }}
							</a>
							<span v-else class="text-xs font-bold text-white">{{
								store.currentOrder.tracking_number
							}}</span>
						</p>
					</div>
				</div>

				<!-- Pagamento -->
				<div
					class="col-span-1 rounded-xl border border-white/10 bg-white/[0.03] p-6 md:col-span-2 lg:col-span-1"
				>
					<h3
						class="mb-4 text-[0.65rem] font-bold uppercase tracking-[0.15em] text-[#FFC700]"
					>
						Pagamento
					</h3>
					<div v-if="payment" class="space-y-2 text-sm text-white/60">
						<div class="flex justify-between">
							<span class="text-white/40">Método</span>
							<span class="font-semibold text-white">{{
								payment.method.label
							}}</span>
						</div>
						<div class="flex justify-between">
							<span class="text-white/40">Estado</span>
							<span
								class="rounded-full px-2 py-0.5 text-[0.65rem] font-bold uppercase tracking-wider"
								:class="
									payment.status.value === 'paid'
										? 'border border-green-500/20 bg-green-500/10 text-green-400'
										: 'border border-amber-500/20 bg-amber-500/10 text-amber-400'
								"
							>
								{{ payment.status.label }}
							</span>
						</div>

						<!-- Detalhes extra para Multibanco -->
						<div
							v-if="
								payment.method.value === 'multibanco' && payment.payment_data
							"
							class="mt-4 space-y-2 border-t border-white/10 pt-3"
						>
							<p
								class="text-[0.65rem] font-bold uppercase tracking-wider text-[#FFC700]"
							>
								Dados Multibanco
							</p>
							<div class="flex justify-between">
								<span class="text-xs text-white/40">Entidade:</span>
								<span class="font-mono text-xs text-white">{{
									(payment.payment_data as any).entity
								}}</span>
							</div>
							<div class="flex justify-between">
								<span class="text-xs text-white/40">Referência:</span>
								<span class="font-mono text-xs font-bold text-[#FFC700]">{{
									formatReference((payment.payment_data as any).reference)
								}}</span>
							</div>
							<div class="flex justify-between">
								<span class="text-xs text-white/40">Montante:</span>
								<span class="text-xs text-white"
									>€{{ formatPrice(payment.amount) }}</span
								>
							</div>
						</div>

						<!-- Detalhes extra para MB Way -->
						<div
							v-if="payment.method.value === 'mbway' && payment.payment_data"
							class="mt-4 space-y-1 border-t border-white/10 pt-3 text-xs"
						>
							<p
								class="mb-2 text-[0.65rem] font-bold uppercase tracking-wider text-[#FFC700]"
							>
								Detalhes MB Way
							</p>
							<div class="flex justify-between">
								<span class="text-white/40">Telemóvel:</span>
								<span class="font-semibold text-white">{{
									(payment.payment_data as any).phone
								}}</span>
							</div>
						</div>
					</div>
					<div v-else class="text-sm italic text-white/30">
						Sem informação de pagamento disponível.
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
