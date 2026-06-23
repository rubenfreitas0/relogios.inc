<template>
  <div class="min-h-screen w-full flex items-center justify-center bg-white p-4 font-sans">
    <div class="w-full max-w-[360px] flex flex-col items-center">
      <div class="flex flex-col items-center mb-8">
        <RouterLink to="/" aria-label="Ir para o início" class="flex flex-col items-center">
          <span class="text-3xl font-black tracking-tight text-black select-none">
            RELOGIOS<span class="text-[#FFC700]">.inc</span>
          </span>
          <span class="text-[10px] tracking-widest uppercase text-gray-500 mt-1.5 select-none font-semibold">
            Painel de Administração
          </span>
        </RouterLink>
      </div>
      <RouterView class="w-full" />
    </div>
  </div>
</template>

<script lang="ts" setup>
// Layout centralizado minimalista com fundo branco.
</script>
