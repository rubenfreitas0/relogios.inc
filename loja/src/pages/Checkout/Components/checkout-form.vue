<script setup lang="ts">
import { onMounted } from 'vue'
import TextInputField from './text-input-field.vue'
import { useFormStore } from '../../../pinia/formStore.ts'

const formStore = useFormStore()

onMounted(() => {
	formStore.fetchShippingMethods()
})
</script>
<template>
	<form
		class="col-span-2 h-full w-full rounded bg-white px-6 py-12 lg:px-10"
		id="checkoutForm"
	>
		<h1 class="text-3xl font-bold uppercase text-black">Checkout</h1>
		
		<div v-if="formStore.apiError" class="mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded relative" role="alert">
			<strong class="font-bold">Error: </strong>
			<span class="block sm:inline">{{ formStore.apiError }}</span>
		</div>

		<div class="mt-10">
			<p class="mb-2 font-bold uppercase tracking-wider text-k-main">
				Billing details
			</p>
			<div
				class="flex w-full flex-col items-center gap-4 lg:grid lg:grid-cols-2"
			>
				<TextInputField
					type="text"
					:validator="formStore.isValidName"
					id="name"
					label="Name"
					placeholder="Alex Keebs"
					error-message="Characters only."
					autocomplete="off"
					:required="true"
				/>

				<TextInputField
					type="email"
					:validator="formStore.isValidEmail"
					id="email"
					label="Email Address"
					placeholder="alex@mail.com"
					error-message="Must be a valid email address."
					autocomplete="off"
					:required="true"
				/>

				<TextInputField
					type="tel"
					:validator="formStore.isValidPhone"
					id="phone"
					label="Phone Number"
					placeholder="+1000-555-0136"
					error-message="Numbers and '+-' only."
					autocomplete="off"
				/>
			</div>
		</div>

		<div class="mt-10">
			<p class="mb-2 font-bold uppercase tracking-wider text-k-main">
				Shipping Info
			</p>
			<div
				class="flex w-full flex-col items-center gap-4 lg:grid lg:grid-cols-2"
			>
				<TextInputField
					type="text"
					:validator="formStore.isValidAddress"
					id="address"
					label="Address"
					container-class="col-span-2"
					placeholder="1134 Willams Avenue"
					error-message="Only characters and ',-/. allowed."
					autocomplete="off"
					:required="true"
				/>

				<TextInputField
					type="text"
					:validator="formStore.isValidZip"
					id="zip"
					label="Zip Code"
					placeholder="10001"
					error-message="Only 5 digit numbers allowed."
					autocomplete="off"
					max-length="5"
					:required="true"
				/>

				<TextInputField
					type="text"
					:validator="formStore.isValidCity"
					id="city"
					label="City"
					placeholder="New York"
					error-message="Must contain non-special characters."
					autocomplete="off"
					:required="true"
				/>

				<TextInputField
					type="text"
					:validator="formStore.isValidCountry"
					id="country"
					label="Country (ISO 2-chars, e.g. PT)"
					placeholder="PT"
					error-message="Must be a 2-character country code."
					autocomplete="off"
					max-length="2"
					:required="true"
				/>
			</div>
		</div>

		<div class="mt-10">
			<p class="mb-2 font-bold uppercase tracking-wider text-k-main">
				Shipping Method
			</p>
			<div class="flex w-full flex-col gap-4 lg:grid lg:grid-cols-2">
				<div v-if="formStore.shippingMethods.length === 0" class="col-span-2 text-gray-500 italic">
					Loading shipping methods...
				</div>
				<button
					v-for="method in formStore.shippingMethods"
					:key="method.id"
					type="button"
					class="group flex w-full cursor-pointer flex-row items-center gap-4 rounded border border-black border-opacity-60 p-3 transition-all active:translate-y-0.5"
					:class="{ 'bg-k-main border-opacity-100': formStore.shipping_method_id === method.id }"
					@click="formStore.shipping_method_id = method.id"
				>
					<div
						class="aspect-square h-3 rounded-full border border-black border-opacity-60"
						:class="{ 'bg-black': formStore.shipping_method_id === method.id }"
					></div>
					<div class="flex flex-col items-start">
						<span class="font-semibold text-black">{{ method.name }}</span>
						<span class="text-xs text-gray-600">{{ method.carrier }} - ${{ method.price }}</span>
					</div>
				</button>
			</div>
		</div>

		<div class="mt-10">
			<p class="mb-2 font-bold uppercase tracking-wider text-k-main">
				Payment Details
			</p>
			<p class="mb-1 font-bold text-black">Payment Method</p>
			<div class="flex w-full flex-col gap-4 lg:grid lg:grid-cols-2">
				<button
					type="button"
					class="group flex w-full cursor-pointer flex-row items-center gap-4 rounded border border-black border-opacity-60 p-3 transition-all active:translate-y-0.5"
					:class="{ 'bg-k-main': !formStore.choseCash }"
					@click="formStore.setElectronic($event)"
					data-test="form-button-emoney"
				>
					<div
						class="aspect-square h-3 rounded-full border border-black border-opacity-60"
						:class="{ 'bg-black': !formStore.choseCash }"
					></div>
					<span class="font-semibold text-black"> e-Money </span>
				</button>
				<button
					type="button"
					class="group flex w-full cursor-pointer flex-row items-center gap-4 rounded border border-black border-opacity-60 p-3 transition-all active:translate-y-0.5"
					:class="{ 'bg-k-main': formStore.choseCash }"
					@click="formStore.setCash($event)"
					data-test="form-button-cash"
				>
					<div
						class="aspect-square h-3 rounded-full border border-black border-opacity-60"
						:class="{ 'bg-black': formStore.choseCash }"
					></div>
					<span class="font-semibold text-black"> Cash on Delivery </span>
				</button>

				<div class="col-span-2 flex h-40 flex-col">
					<label class="mb-1 mt-4 font-bold text-black" for="comment"
						>Add a comment</label
					>
					<textarea
						class="h-full rounded border border-black border-opacity-60 bg-white p-3 font-Manrope font-semibold text-black outline-none hover:border-k-main"
						id="comment"
						placeholder="Your request"
						v-model="formStore.comment"
						data-test="form-text-area"
					/>
				</div>
			</div>
		</div>
	</form>
</template>

