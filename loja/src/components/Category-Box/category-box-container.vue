<script setup lang="ts">
import CategoryBox from './category-box.vue'

const watchMenImage = '/products/premium/watch1.png'
const watchWomenImage = '/products/premium/watch2.png'
const watchUnisexImage = '/products/categories/relogiocontainer1.png'

const sharedImgClass = ['scale-[58%]', 'object-contain', '-translate-y-8']

const categories = [
  {
    src: watchMenImage,
    category: 'homens',
    imgClass: sharedImgClass,
  },
  {
    src: watchWomenImage,
    category: 'mulheres',
    imgClass: sharedImgClass,
  },
  {
    src: watchUnisexImage,
    category: 'unisexo',
    // foto ligeiramente inclinada — rotação CSS para endireitar e escala maior
    // porque o relógio ocupa menos espaço no enquadramento
    imgClass: ['scale-[80%]', 'object-contain', '-translate-y-8', 'rotate-6'],
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
				:img-class="item.imgClass"
			/>
		</div>
	</div>
</template>
