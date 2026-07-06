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
			<RouterLink
				to="/"
				class="absolute h-screen w-full bg-black opacity-40"
				@click="handleClose()"
			></RouterLink>
			<div
				class="relative z-10 mx-4 mt-6 flex max-h-[90vh] w-full max-w-xl flex-col overflow-y-auto rounded-md bg-white p-10 md:mt-20 md:p-12"
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
				<p class="mt-2 text-sm font-bold uppercase text-k-main">
					Order #{{ order.order_number }}
				</p>
				<p
					class="text-md mt-4 font-semibold text-black opacity-60 lg:mt-4 lg:text-lg"
				>
					You will receive an email confirmation shortly.
				</p>

				<!-- MB Way payment info -->
				<div
					v-if="payment?.method?.value === 'mbway'"
					class="mt-4 rounded-md border border-dashed border-k-main bg-k-main/10 p-4 text-sm text-black"
				>
					<p class="font-bold">Pagamento MB Way pendente</p>
					<p class="mt-1">
						Por favor autorize o pagamento de
						<span class="font-bold">€{{ order.total }}</span> na aplicação MB
						Way associada ao telemóvel
						<span class="font-bold">{{
							(payment.payment_data as any)?.phone
						}}</span
						>.
					</p>
				</div>

				<!-- Multibanco payment info -->
				<div
					v-if="payment?.method?.value === 'multibanco' && payment.payment_data"
					class="mt-4 rounded-md border border-black/10 bg-k-grey p-4 text-sm text-black"
				>
					<p class="mb-2 text-xs font-bold uppercase tracking-wider text-black">
						Dados de Pagamento Multibanco
					</p>
					<div class="border-black/15 space-y-1 rounded border bg-white p-3">
						<div class="flex justify-between border-b border-black/5 pb-1">
							<span class="text-xs opacity-60">Entidade:</span>
							<span class="font-mono text-xs font-bold">{{
								(payment.payment_data as any).entity
							}}</span>
						</div>
						<div class="flex justify-between border-b border-black/5 pb-1">
							<span class="text-xs opacity-60">Referência:</span>
							<span class="font-mono text-xs font-bold">{{
								formatReference((payment.payment_data as any).reference)
							}}</span>
						</div>
						<div class="flex justify-between border-b border-black/5 pb-1">
							<span class="text-xs opacity-60">Montante:</span>
							<span class="text-xs font-bold">€{{ order.total }}</span>
						</div>
						<div class="flex justify-between pt-1">
							<span class="text-[10px] opacity-40">Expira a:</span>
							<span class="text-[10px] opacity-60">{{
								formatDate((payment.payment_data as any).expires_at)
							}}</span>
						</div>
					</div>
				</div>
				<div
					class="mb-4 mt-6 flex h-full w-full flex-col overflow-hidden rounded-lg lg:mb-6 lg:mt-8 lg:flex-row lg:items-center"
				>
					<div
						class="flex h-full w-full flex-col justify-center bg-k-grey px-4 py-6 lg:basis-4/6"
					>
						<div
							v-if="firstItem"
							class="flex h-full w-full flex-row items-center"
						>
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
								class="text-md font-semibold uppercase tracking-wide text-white opacity-90"
							>
								Grand Total
							</p>
							<p class="text-md font-semibold text-white lg:text-lg">
								$ {{ order.total }}
							</p>
						</div>
					</div>
				</div>
				<ButtonSolid
					to="/"
					color="light"
					content="back to home"
					class="mt-6 self-center font-bold"
					data-test="checkout-success-modal-button"
					@click="handleClose()"
				/>
			</div>
		</div>
	</Transition>
</template>
