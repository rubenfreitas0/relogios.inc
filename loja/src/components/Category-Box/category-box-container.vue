<script setup lang="ts">
import CategoryBox from './category-box.vue'

import watchMenImage from '/products/categories/relogiocontainer1.png'
import watchWomenImage from '/products/categories/relogiocontainer2.png'
import watchUnisexImage from '/products/categories/relogiocontainer3.png'

const categories = [
	{
		src: watchMenImage,
		category: 'homens',
		imgClass: ['scale-75', 'object-contain', '-mb-4'],
	},
	{
		src: watchWomenImage,
		category: 'mulheres',
		imgClass: ['scale-55', 'object-contain', 'object-center', '-mt-4'],
	},
	{
		src: watchUnisexImage,
		category: 'unisexo',
		imgClass: ['scale-50', 'object-contain', 'object-center', '-mt-4'],
	},
]
</script>

<template>
	<div class="mt-16 flex h-full w-full flex-col items-center bg-white lg:mt-32">
		<div
			class="flex w-4/5 flex-col items-center gap-4 md:h-[17rem] md:w-11/12 md:max-w-6xl md:flex-row md:justify-center md:gap-6 lg:w-4/5"
		>
			<CategoryBox
				v-for="(item, index) in categories"
				:key="index"
				:src="item.src"
				:category="item.category"
				:imgClass="item.imgClass"
			/>
		</div>
	</div>
</template>