<template>
  <div class="brand-logo" :style="{ fontSize: height + 'px' }">
    <span class="brand-logo__name">RELOGIOS<span class="brand-logo__dot">.</span>INC</span>
    <span class="brand-logo__tag">ADMIN</span>
  </div>
</template>

<script lang="ts" setup>
withDefaults(
  defineProps<{
    height?: number
  }>(),
  {
    height: 18,
  },
)
</script>

<style lang="scss" scoped>
.brand-logo {
  display: flex;
  align-items: baseline;
  gap: 6px;
  user-select: none;
  white-space: nowrap;

  &__name {
    font-weight: 800;
    letter-spacing: 0.12em;
    color: var(--va-text-primary);
    font-size: 1em;
  }

  &__dot {
    color: var(--va-primary);
  }

  &__tag {
    font-size: 0.55em;
    font-weight: 700;
    letter-spacing: 0.15em;
    color: var(--va-primary);
  }
}
</style>
