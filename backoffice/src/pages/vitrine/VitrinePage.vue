<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useToast } from 'vuestic-ui'
import { siteSettingsApi } from '../../services/api'

const { init: initToast } = useToast()

const settings = ref({
  hero_subtitle: '',
  hero_title: '',
  hero_description: '',
  hero_image: '',
  hero_link: '',
  hero_button_text: '',
  grid_hero_title: '',
  grid_hero_description: '',
  grid_hero_image: '',
  grid_hero_link: '',
})

const loading = ref(false)
const saving = ref(false)

const fileInputHero = ref<HTMLInputElement | null>(null)
const fileInputGrid = ref<HTMLInputElement | null>(null)

const previewHeroImage = ref<string | null>(null)
const previewGridImage = ref<string | null>(null)

const heroFile = ref<File | null>(null)
const gridFile = ref<File | null>(null)

onMounted(async () => {
  await fetchSettings()
})

const fetchSettings = async () => {
  loading.value = true
  try {
    const response = await siteSettingsApi.get()
    const data = response.data
    settings.value = {
      hero_subtitle: data.hero_subtitle || '',
      hero_title: data.hero_title || '',
      hero_description: data.hero_description || '',
      hero_image: data.hero_image || '',
      hero_link: data.hero_link || '',
      hero_button_text: data.hero_button_text || '',
      grid_hero_title: data.grid_hero_title || '',
      grid_hero_description: data.grid_hero_description || '',
      grid_hero_image: data.grid_hero_image || '',
      grid_hero_link: data.grid_hero_link || '',
    }

    if (data.hero_image) {
      previewHeroImage.value = formatImageUrl(data.hero_image)
    }
    if (data.grid_hero_image) {
      previewGridImage.value = formatImageUrl(data.grid_hero_image)
    }
  } catch (error) {
    console.error('Erro ao carregar as definições:', error)
    initToast({
      message: 'Erro ao carregar as definições da Vitrine.',
      color: 'danger',
    })
  } finally {
    loading.value = false
  }
}

const formatImageUrl = (url: string) => {
  if (url.startsWith('http') || url.startsWith('/')) {
    return url
  }
  // Se for caminho do storage do laravel
  const baseUrl = import.meta.env.VITE_API_BASE_URL || ''
  return `${baseUrl}/storage/${url}`
}

const triggerHeroUpload = () => {
  fileInputHero.value?.click()
}

const triggerGridUpload = () => {
  fileInputGrid.value?.click()
}

const onHeroFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0]) {
    const file = target.files[0]
    heroFile.value = file
    previewHeroImage.value = URL.createObjectURL(file)
  }
}

const onGridFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0]) {
    const file = target.files[0]
    gridFile.value = file
    previewGridImage.value = URL.createObjectURL(file)
  }
}

const saveSettings = async () => {
  saving.value = true
  try {
    // 1. Fazer upload dos ficheiros caso existam
    if (heroFile.value) {
      const heroUploadRes = await siteSettingsApi.uploadImage(heroFile.value, 'hero_image')
      settings.value.hero_image = heroUploadRes.data.path
      heroFile.value = null
    }

    if (gridFile.value) {
      const gridUploadRes = await siteSettingsApi.uploadImage(gridFile.value, 'grid_hero_image')
      settings.value.grid_hero_image = gridUploadRes.data.path
      gridFile.value = null
    }

    // 2. Gravar restantes definições em bulk
    await siteSettingsApi.update(settings.value)

    initToast({
      message: 'Vitrine atualizada com sucesso!',
      color: 'success',
    })

    await fetchSettings()
  } catch (error) {
    console.error('Erro ao salvar as definições:', error)
    initToast({
      message: 'Erro ao guardar as alterações da Vitrine.',
      color: 'danger',
    })
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div class="flex flex-col space-y-6 md:space-y-4">
    <div class="flex flex-row justify-between items-center">
      <h1 class="page-title">Configurar Vitrine</h1>
      <VaButton :loading="saving" icon="save" @click="saveSettings"> Guardar Alterações </VaButton>
    </div>

    <div v-if="loading" class="flex justify-center p-12">
      <VaInnerLoading loading />
    </div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Secção Hero Banner -->
      <VaCard>
        <VaCardTitle class="font-semibold text-lg">Banner Principal (Hero)</VaCardTitle>
        <VaCardContent class="flex flex-col gap-4">
          <VaInput
            v-model="settings.hero_subtitle"
            label="Subtítulo / Etiqueta"
            placeholder="Ex: nova coleção"
            class="w-full"
          />

          <VaInput
            v-model="settings.hero_title"
            label="Título Principal (usa \n para quebra de linha)"
            placeholder="Ex: CASIO MTP-1274 \n COLECÇÃO PREMIUM"
            class="w-full"
          />

          <VaTextarea
            v-model="settings.hero_description"
            label="Descrição Curta"
            placeholder="Descrição detalhada sobre a coleção..."
            class="w-full"
            :min-rows="3"
          />

          <div class="grid grid-cols-2 gap-4">
            <VaInput
              v-model="settings.hero_button_text"
              label="Texto do Botão"
              placeholder="Ex: ver relógio"
              class="w-full"
            />
            <VaInput
              v-model="settings.hero_link"
              label="Link de Destino"
              placeholder="Ex: /homens ou slug de produto"
              class="w-full"
            />
          </div>

          <!-- Imagem Hero -->
          <div class="flex flex-col gap-2 mt-2">
            <label class="text-xs font-semibold text-[var(--va-secondary)]">Imagem do Relógio (Hero)</label>
            <div
              class="flex items-center gap-4 border border-dashed border-gray-300 rounded p-4 bg-gray-50 dark:bg-gray-800"
            >
              <div
                v-if="previewHeroImage"
                class="relative w-24 h-24 flex items-center justify-center bg-black rounded p-1"
              >
                <img :src="previewHeroImage" class="max-h-full max-w-full object-contain" />
              </div>
              <div
                v-else
                class="w-24 h-24 bg-gray-200 dark:bg-gray-700 flex items-center justify-center rounded text-xs text-gray-400"
              >
                Sem imagem
              </div>
              <div>
                <input ref="fileInputHero" type="file" accept="image/*" class="hidden" @change="onHeroFileChange" />
                <VaButton size="small" preset="secondary" @click="triggerHeroUpload"> Escolher Imagem </VaButton>
                <p class="text-[10px] text-gray-400 mt-1">PNG ou JPG recomendado (fundo transparente).</p>
              </div>
            </div>
          </div>
        </VaCardContent>
      </VaCard>

      <!-- Secção Grid Destaque -->
      <VaCard>
        <VaCardTitle class="font-semibold text-lg">Destaque Secundário (Bloco Dourado)</VaCardTitle>
        <VaCardContent class="flex flex-col gap-4">
          <VaInput
            v-model="settings.grid_hero_title"
            label="Título do Bloco (usa \n para quebra de linha)"
            placeholder="Ex: Casio \nMTP-1274"
            class="w-full"
          />

          <VaTextarea
            v-model="settings.grid_hero_description"
            label="Descrição Curta"
            placeholder="Ex: Precisão japonesa num design atemporal..."
            class="w-full"
            :min-rows="3"
          />

          <VaInput
            v-model="settings.grid_hero_link"
            label="Link de Destino"
            placeholder="Ex: /homens ou slug de produto"
            class="w-full"
          />

          <!-- Imagem Grid -->
          <div class="flex flex-col gap-2 mt-2">
            <label class="text-xs font-semibold text-[var(--va-secondary)]">Imagem do Relógio (Destaque)</label>
            <div
              class="flex items-center gap-4 border border-dashed border-gray-300 rounded p-4 bg-gray-50 dark:bg-gray-800"
            >
              <div
                v-if="previewGridImage"
                class="relative w-24 h-24 flex items-center justify-center bg-black rounded p-1"
              >
                <img :src="previewGridImage" class="max-h-full max-w-full object-contain" />
              </div>
              <div
                v-else
                class="w-24 h-24 bg-gray-200 dark:bg-gray-700 flex items-center justify-center rounded text-xs text-gray-400"
              >
                Sem imagem
              </div>
              <div>
                <input ref="fileInputGrid" type="file" accept="image/*" class="hidden" @change="onGridFileChange" />
                <VaButton size="small" preset="secondary" @click="triggerGridUpload"> Escolher Imagem </VaButton>
                <p class="text-[10px] text-gray-400 mt-1">PNG ou JPG recomendado.</p>
              </div>
            </div>
          </div>
        </VaCardContent>
      </VaCard>
    </div>
  </div>
</template>
