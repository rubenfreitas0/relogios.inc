<script setup lang="ts">
const cartIcon = '/icons/cart-icon.svg'
</script>

<template>
	<button
		class="flex aspect-square h-full cursor-pointer flex-row items-center justify-center transition hover:translate-y-0.5"
		data-test="button-quick-add"
		@click="$emit('addToCart')"
	>
		<img
			loading="lazy"
			class="aspect-square h-6 invert active:translate-y-0.5"
			:src="cartIcon"
			alt=""
		/>
		<p class="font-bold text-black">+</p>
	</button>
</template>
