<script setup lang="ts">
import ButtonSolid from '../../../components/Buttons/button-solid.vue'
import { useFormStore } from '../../../pinia/formStore'
import { onBeforeMount, onBeforeUnmount, computed } from 'vue'
import { resolveProductImageUrl } from '../../../utils/utilities'

const formStore = useFormStore()

const handleClose = () => {
	formStore.bannerOff()
}

const order = computed(() => formStore.lastOrder)
const firstItem = computed(() => order.value?.items?.[0])
const payment = computed(() => order.value?.payments?.[0])

function formatReference(ref: string | number): string {
	const str = String(ref)
	return `${str.substring(0, 3)} ${str.substring(3, 6)} ${str.substring(6, 9)}`
}

function formatDate(iso: string): string {
	return new Date(iso).toLocaleDateString('pt-PT', {
		day: '2-digit',
		month: '2-digit',
		year: 'numeric',
		hour: '2-digit',
		minute: '2-digit',
	})
}

onBeforeMount(() => {
	if (formStore.showBanner) {
		document.body.classList.add('overflow-y-hidden')
	}
})

onBeforeUnmount(() => {
	document.body.classList.remove('overflow-y-hidden')
})
</script>
<template>
	<Transition>
		<div
			v-if="order"
			class="fixed z-40 flex h-full w-full flex-col items-center backdrop-blur-sm"
			data-test="checkout-success-modal"
		>
			<router-link
				to="/"
				@click="handleClose()"
				class="absolute h-screen w-full bg-black opacity-40"
			></router-link>
			<div
				class="relative z-10 mx-4 mt-6 flex flex-col rounded-md bg-white p-10 md:mt-20 md:p-12 w-full max-w-xl overflow-y-auto max-h-[90vh]"
			>
				<div
					class="absolute right-10 flex h-10 w-10 flex-shrink-0 flex-row items-center justify-center rounded-full bg-k-main md:static lg:h-20 lg:w-20"
				>
					<svg
						xmlns="http://www.w3.org/2000/svg"
						fill="none"
						viewBox="0 0 24 24"
						stroke-width="1.5"
						stroke="black"
						class="h-8 w-8 lg:h-14 lg:w-14"
					>
						<path
							stroke-linecap="round"
							stroke-linejoin="round"
							d="M4.5 12.75l6 6 9-13.5"
						/>
					</svg>
				</div>
				<h2
					class="text-2xl font-bold uppercase text-black md:mt-6 lg:mt-8 lg:text-3xl"
				>
					thank you <br />
					for your order
				</h2>
				<p class="mt-2 text-sm font-bold text-k-main uppercase">Order #{{ order.order_number }}</p>
				<p
					class="text-md mt-4 font-semibold text-black opacity-60 lg:mt-4 lg:text-lg"
				>
					You will receive an email confirmation shortly.
				</p>

				<!-- MB Way payment info -->
				<div v-if="payment?.method?.value === 'mbway'" class="mt-4 p-4 bg-k-main/10 border border-dashed border-k-main rounded-md text-sm text-black">
					<p class="font-bold">Pagamento MB Way pendente</p>
					<p class="mt-1">Por favor autorize o pagamento de <span class="font-bold">€{{ order.total }}</span> na aplicação MB Way associada ao telemóvel <span class="font-bold">{{ (payment.payment_data as any)?.phone }}</span>.</p>
				</div>

				<!-- Multibanco payment info -->
				<div v-if="payment?.method?.value === 'multibanco' && payment.payment_data" class="mt-4 p-4 bg-k-grey border border-black/10 rounded-md text-sm text-black">
					<p class="font-bold uppercase tracking-wider text-black mb-2 text-xs">Dados de Pagamento Multibanco</p>
					<div class="space-y-1 bg-white p-3 rounded border border-black/15">
						<div class="flex justify-between border-b border-black/5 pb-1"><span class="opacity-60 text-xs">Entidade:</span> <span class="font-mono font-bold text-xs">{{ (payment.payment_data as any).entity }}</span></div>
						<div class="flex justify-between border-b border-black/5 pb-1"><span class="opacity-60 text-xs">Referência:</span> <span class="font-mono font-bold text-xs">{{ formatReference((payment.payment_data as any).reference) }}</span></div>
						<div class="flex justify-between border-b border-black/5 pb-1"><span class="opacity-60 text-xs">Montante:</span> <span class="font-bold text-xs">€{{ order.total }}</span></div>
						<div class="flex justify-between pt-1"><span class="opacity-40 text-[10px]">Expira a:</span> <span class="text-[10px] opacity-60">{{ formatDate((payment.payment_data as any).expires_at) }}</span></div>
					</div>
				</div>
				<div
					class="mb-4 mt-6 flex h-full w-full flex-col overflow-hidden rounded-lg lg:mb-6 lg:mt-8 lg:flex-row lg:items-center"
				>
					<div
						class="flex h-full w-full flex-col justify-center bg-k-grey px-4 py-6 lg:basis-4/6"
					>
						<div v-if="firstItem" class="flex h-full w-full flex-row items-center">
							<img
								class="aspect-square h-24"
								:src="resolveProductImageUrl(firstItem.product_image)"
								alt=""
								loading="lazy"
							/>
							<div class="ml-3 flex flex-col items-start justify-center">
								<p class="text-lg font-bold text-black">
									{{ firstItem.product_name }}
								</p>
								<p class="text-lg font-bold text-black opacity-60">
									${{ firstItem.unit_price }}
								</p>
							</div>
							<p
								class="ml-auto place-self-center text-lg font-bold text-black opacity-60"
							>
								x{{ firstItem.quantity }}
							</p>
						</div>
						<hr v-if="order.items.length > 1" />
						<p
							v-if="order.items.length > 1"
							class="mt-2 text-center font-semibold text-black opacity-70"
						>
							and {{ order.items.length - 1 }} other item<span
								v-show="order.items.length > 2"
								>s</span
							>
						</p>
					</div>
					<div
						class="flex flex-col justify-center bg-black px-6 py-6 lg:h-full lg:basis-2/6"
					>
						<div>
							<p
								class="text-md font-semibold uppercase tracking-wide opacity-90 text-white"
							>
								Grand Total
							</p>
							<p class="text-md font-semibold lg:text-lg text-white">
								$ {{ order.total }}
							</p>
						</div>
					</div>
				</div>
				<ButtonSolid
					to="/"
					@click="handleClose()"
					color="light"
					content="back to home"
					class="mt-6 self-center font-bold"
					data-test="checkout-success-modal-button"
				/>
			</div>
		</div>
	</Transition>
</template>

