<template>
  <!-- Nome -->
  <div class="flex flex-col md:flex-row md:items-center space-y-2 md:space-y-0 md:space-x-6 min-h-[36px] leading-5">
    <p class="font-bold w-[200px]">Nome</p>
    <div class="flex-1">
      <div class="max-w-[748px]">
        {{ authStore.fullName }}
      </div>
    </div>
    <VaButton :style="buttonStyles" class="w-fit h-fit" preset="primary" @click="emits('openNameModal')">
      Editar
    </VaButton>
  </div>

  <VaDivider />

  <!-- Email -->
  <div class="flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-6 min-h-[36px] leading-5">
    <p class="font-bold w-[200px]">Email</p>
    <div class="flex-1">
      <div class="max-w-[748px]">
        {{ authStore.user?.email }}
      </div>
    </div>
  </div>

  <!-- Telefone -->
  <div class="flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-6 min-h-[36px] leading-5">
    <p class="font-bold w-[200px]">Telefone</p>
    <div class="flex-1">
      <div class="max-w-[748px]">
        {{ authStore.user?.phone || '—' }}
      </div>
    </div>
  </div>

  <VaDivider />

  <!-- Password -->
  <div class="flex flex-col md:flex-row md:items-center space-y-2 md:space-y-0 md:space-x-6 min-h-[36px] leading-5">
    <p class="font-bold w-[200px]">Palavra-passe</p>
    <div class="flex-1">
      <div class="max-w-[748px]">•••••••••••••</div>
    </div>
    <VaButton :style="buttonStyles" class="w-fit h-fit" preset="primary" @click="emits('openResetPasswordModal')">
      Alterar
    </VaButton>
  </div>
</template>

<script lang="ts" setup>
import { useAuthStore } from '../../../stores/auth-store'
import { buttonStyles } from '../styles'

const authStore = useAuthStore()

const emits = defineEmits(['openNameModal', 'openResetPasswordModal'])
</script>
