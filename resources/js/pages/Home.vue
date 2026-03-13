<script setup>
import Veerpont from '@/components/Veerpont.vue';
import PageLayout from '@/components/PageLayout.vue';
import Overlay from '@/components/Overlay.vue';

defineProps({
  line: String,
  destination: String,
  timer: String,
})

</script>

<template>
  <PageLayout>
    <Veerpont class="main-svg" :line="line" :destination="destination" :timer="timer" />

    <template #overlay>
      <Overlay />
    </template>
  </PageLayout>
</template>

<style>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>