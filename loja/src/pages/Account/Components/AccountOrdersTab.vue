<script setup lang="ts">
import { useAccountStore } from '../../../pinia/accountStore'
import { onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'

const store = useAccountStore()
const router = useRouter()

onMounted(() => {
	store.fetchOrders(1)
})

function goToPage(page: number) {
	store.fetchOrders(page)
}

function viewOrder(orderNumber: string) {
	router.push(`/conta/encomendas/${orderNumber}`)
}

function formatDate(iso: string): string {
	return new Date(iso).toLocaleDateString('pt-PT', {
		day: '2-digit',
		month: 'short',
		year: 'numeric',
	})
}

function formatPrice(value: number | string): string {
	const num = Number(value)
	if (isNaN(num)) return '0,00'
	return num.toFixed(2).replace('.', ',')
}

const statusColor = (value: string) => {
	switch (value) {
	case 'pending': return 'bg-amber-500/10 text-amber-400 border-amber-500/20'
	case 'processing': return 'bg-blue-500/10 text-blue-400 border-blue-500/20'
	case 'shipped': return 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20'
	case 'delivered': return 'bg-green-500/10 text-green-400 border-green-500/20'
	case 'cancelled': return 'bg-red-500/10 text-red-400 border-red-500/20'
	case 'refunded': return 'bg-gray-500/10 text-gray-400 border-gray-500/20'
	default: return 'bg-white/5 text-white/50 border-white/10'
	}
}

const pages = computed(() => {
	const p = store.ordersPagination
	const arr: number[] = []
	for (let i = 1; i <= p.last_page; i++) arr.push(i)
	return arr
})
</script>

<template>
	<div>
		<!-- Loading -->
		<div v-if="store.isLoading && store.orders.length === 0" class="flex items-center justify-center py-20">
			<div class="h-6 w-6 animate-spin rounded-full border-2 border-[#FFC700] border-t-transparent"></div>
		</div>

		<!-- Error -->
		<div v-else-if="store.error" class="rounded-xl border border-red-500/20 bg-red-500/5 p-6 text-center">
			<p class="text-sm text-red-400">{{ store.error }}</p>
		</div>

		<!-- Empty -->
		<div v-else-if="store.orders.length === 0 && !store.isLoading" class="flex flex-col items-center justify-center py-20">
			<svg class="mb-4 h-16 w-16 text-white/10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
			</svg>
			<p class="text-sm font-semibold uppercase tracking-wider text-white/30">Sem encomendas</p>
			<p class="mt-1 text-xs text-white/20">As tuas encomendas aparecerão aqui.</p>
			<router-link to="/homens" class="mt-6 rounded-full bg-[#FFC700] px-6 py-2 text-xs font-bold uppercase tracking-wider text-black transition-colors hover:bg-yellow-400">
				Explorar catálogo
			</router-link>
		</div>

		<!-- Orders list -->
		<div v-else class="space-y-3">
			<button
				v-for="order in store.orders"
				:key="order.order_number"
				@click="viewOrder(order.order_number)"
				class="group flex w-full flex-col gap-3 rounded-xl border border-white/10 bg-white/[0.03] p-5 text-left transition-all duration-200 hover:border-[#FFC700]/30 hover:bg-white/[0.05] md:flex-row md:items-center md:justify-between"
			>
				<div class="flex items-center gap-4">
					<div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-white/5">
						<svg class="h-5 w-5 text-white/30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
						</svg>
					</div>
					<div>
						<p class="text-sm font-bold text-white tracking-wide">
							#{{ order.order_number }}
						</p>
						<p class="text-xs text-white/30">
							{{ formatDate(order.created_at) }}
						</p>
					</div>
				</div>

				<div class="flex items-center gap-4 md:gap-6">
					<span
						class="inline-flex rounded-full border px-3 py-1 text-[0.6rem] font-bold uppercase tracking-wider"
						:class="statusColor(order.status.value)"
					>
						{{ order.status.label }}
					</span>
					<p class="text-sm font-bold text-white min-w-[5rem] text-right">
						€{{ formatPrice(order.total) }}
					</p>
					<svg class="h-4 w-4 text-white/20 transition-transform duration-200 group-hover:translate-x-1 group-hover:text-[#FFC700]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
					</svg>
				</div>
			</button>

			<!-- Pagination -->
			<div v-if="store.ordersPagination.last_page > 1" class="mt-8 flex items-center justify-center gap-2">
				<button
					@click="goToPage(store.ordersPagination.current_page - 1)"
					:disabled="store.ordersPagination.current_page === 1"
					class="flex h-9 w-9 items-center justify-center rounded-lg border border-white/15 text-white/40 transition-all hover:border-[#FFC700] hover:text-[#FFC700] disabled:cursor-not-allowed disabled:opacity-25"
				>
					<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
				</button>
				<button
					v-for="page in pages"
					:key="page"
					@click="goToPage(page)"
					class="h-9 w-9 rounded-lg text-sm font-bold transition-all"
					:class="store.ordersPagination.current_page === page ? 'bg-[#FFC700] text-black' : 'border border-white/15 text-white/40 hover:border-[#FFC700] hover:text-[#FFC700]'"
				>
					{{ page }}
				</button>
				<button
					@click="goToPage(store.ordersPagination.current_page + 1)"
					:disabled="store.ordersPagination.current_page === store.ordersPagination.last_page"
					class="flex h-9 w-9 items-center justify-center rounded-lg border border-white/15 text-white/40 transition-all hover:border-[#FFC700] hover:text-[#FFC700] disabled:cursor-not-allowed disabled:opacity-25"
				>
					<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
				</button>
			</div>
		</div>
	</div>
</template>
