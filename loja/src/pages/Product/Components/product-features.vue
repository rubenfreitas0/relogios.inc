<script setup lang="ts">
interface BoxItem {
	count?: number
	content: string
}

const props = defineProps<{
	features: string
	inthebox: (string | BoxItem)[]
}>()
</script>
<template>
	<section
		class="mt-16 flex w-4/5 max-w-6xl flex-col lg:mt-32 lg:grid lg:grid-cols-2 lg:grid-rows-1 text-black"
	>
		<div class="flex h-full w-full flex-col">
			<h2 class="mb-8 text-2xl font-bold tracking-wide uppercase">Características</h2>
			<p class="tracking-wide text-black/60 whitespace-pre-line text-justify leading-relaxed">
				{{ props.features }}
			</p>
		</div>
		<div class="mt-16 flex h-full w-full flex-col lg:ml-24 lg:mt-0">
			<h2 class="mb-8 text-2xl font-bold tracking-wide uppercase">
				Na Caixa
			</h2>
			<div
				class="flex flex-row items-baseline gap-4 mb-3 border-b border-black/5 pb-2"
				v-for="(item, index) of props.inthebox"
				:key="index"
			>
				<template v-if="typeof item === 'object' && item !== null">
					<p class="font-bold tracking-wide text-k-main">{{ item.count }}x</p>
					<p class="tracking-wide text-black/70">
						{{ item.content }}
					</p>
				</template>
				<template v-else>
					<p class="font-bold tracking-wide text-k-main">1x</p>
					<p class="tracking-wide text-black/70">
						{{ item }}
					</p>
				</template>
			</div>
		</div>
	</section>
</template>
