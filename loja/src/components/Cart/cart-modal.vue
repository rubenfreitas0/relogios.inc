<script setup lang="ts">
import ButtonSolid from '../Buttons/button-solid.vue'
import CartItem from './cart-item.vue'
import { useCartStore } from '../../pinia/cartStore.ts'

const cartStore = useCartStore()
</script>

<template>
	<Transition name="cart-drawer">
		<div
			class="fixed inset-0 z-50 flex justify-end bg-black/50 backdrop-blur-sm"
			data-test="cart-modal"
		>
			<!-- Clickable background area to close the cart -->
			<div
				class="absolute inset-0 cursor-pointer"
				data-test="cart-background"
				@click="cartStore.cartOff()"
			></div>

			<!-- Drawer panel -->
			<div
				class="drawer-content relative flex h-full w-full max-w-md flex-col bg-white text-black shadow-2xl"
			>
				<!-- Close button and Header -->
				<div class="flex items-center justify-between px-6 pt-8 lg:px-10">
					<p
						class="font-Manrope text-2xl font-bold text-k-black lg:text-3xl"
						data-test="cart-header"
					>
						CART ({{ cartStore.cartLength }})
					</p>
					<button
						class="cursor-pointer font-semibold text-black opacity-60 transition hover:opacity-100"
						data-test="cart-close-button"
						@click="cartStore.cartOff()"
					>
						✕ Close
					</button>
				</div>

				<div class="mb-4 mt-2 px-6 lg:px-10">
					<p
						class="inline-block cursor-pointer text-sm text-k-dark-grey underline opacity-70 transition hover:opacity-100 active:translate-y-0.5"
						data-test="cart-delete-all"
						@click="cartStore.clearCart()"
					>
						Delete All
					</p>
				</div>

				<!-- Cart items list (Scrollable) -->
				<div
					class="flex-1 overflow-y-auto px-6 py-4 lg:px-10"
					data-test="cart-item-container"
				>
					<div
						v-if="cartStore.cartLength === 0"
						class="flex h-full flex-col items-center justify-center text-lg text-black opacity-60 lg:text-xl"
						data-test="cart-empty-message"
					>
						No items in cart.
					</div>
					<div v-else class="flex flex-col gap-5">
						<CartItem
							v-for="(value, key) in cartStore.cart"
							:key="key"
							:cart-item="value.product as any"
							:item-count="value.amount"
						/>
					</div>
				</div>

				<!-- Fixed footer with total and checkout button -->
				<div class="border-t border-zinc-100 bg-zinc-50/50 p-6 lg:p-10">
					<div
						class="mb-6 flex w-full flex-row justify-between"
						data-test="cart-total-section"
					>
						<p class="text-lg font-semibold text-black opacity-50 lg:text-xl">
							TOTAL
						</p>
						<p class="text-2xl font-bold text-black">
							€{{ cartStore.cartValue.toFixed(2) }}
						</p>
					</div>
					<ButtonSolid
						v-if="!(cartStore.cartLength === 0)"
						to="/checkout"
						class="flex w-full justify-center"
						add="font-bold w-full py-4"
						color="light"
						content="Checkout"
						data-test="cart-checkout-button"
						@click="cartStore.cartOff"
					/>
				</div>
			</div>
		</div>
	</Transition>
</template>

<style scoped>
/* Transição da Drawer (deslizar da direita) */
.cart-drawer-enter-active,
.cart-drawer-leave-active {
	transition: opacity 0.3s ease;
}

.cart-drawer-enter-active .drawer-content,
.cart-drawer-leave-active .drawer-content {
	transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.cart-drawer-enter-from {
	opacity: 0;
}

.cart-drawer-enter-from .drawer-content {
	transform: translateX(100%);
}

.cart-drawer-leave-to {
	opacity: 0;
}

.cart-drawer-leave-to .drawer-content {
	transform: translateX(100%);
}
</style>
