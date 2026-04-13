<script setup lang="ts">
import { useCartStore } from './pinia/cartStore.ts'
const cartStore = useCartStore()
</script>

<template>
	<div class="select-none font-Manrope relative">
		<router-view></router-view>
        
        <!-- Global Toast Notification -->
        <Transition name="toast">
            <div 
                v-if="cartStore.showToast && cartStore.lastAddedItem" 
                class="fixed bottom-6 right-6 z-[100] flex items-center gap-4 rounded-xl bg-white p-4 shadow-[0_10px_40px_-10px_rgba(0,0,0,0.2)] border border-gray-100"
            >
                <div class="h-12 w-12 flex-shrink-0 overflow-hidden rounded-md bg-gray-50">
                    <img 
                        :src="cartStore.lastAddedItem.src" 
                        :alt="cartStore.lastAddedItem.header"
                        class="h-full w-full object-cover"
                    />
                </div>
                <div class="flex flex-col pr-4">
                    <p class="text-xs font-bold uppercase tracking-widest text-green-500">Adicionado</p>
                    <p class="text-sm font-semibold text-black">{{ cartStore.lastAddedItem.header }} {{ cartStore.lastAddedItem.subheader }}</p>
                </div>
                <button @click="cartStore.showToast = false" class="absolute top-2 right-2 text-gray-400 hover:text-black">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </Transition>
	</div>
</template>

<style>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.toast-enter-from {
    opacity: 0;
    transform: translateY(30px) scale(0.9);
}
.toast-leave-to {
    opacity: 0;
    transform: translateY(10px) scale(0.95);
}

@keyframes bump {
    0% { transform: scale(1); }
    50% { transform: scale(1.3); }
    100% { transform: scale(1); }
}
.animate-bump {
    animation: bump 0.3s ease-in-out;
}
</style>
