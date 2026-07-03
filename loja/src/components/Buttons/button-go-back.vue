<script setup lang="ts"></script>
<template>
	<div class="mb-4 mt-6 w-4/5 max-w-6xl lg:mb-6 lg:mt-10">
		<a
			@click="$router.go(-1)"
			class="cursor-pointer text-start font-semibold text-current opacity-60 hover:opacity-100 active:translate-y-0.5"
			>Go Back</a
		>
	</div>
</template>
