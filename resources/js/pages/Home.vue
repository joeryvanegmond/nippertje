<script setup>
import Veerpont from '@/components/Veerpont.vue';
import Bus from '@/components/Bus.vue';
import PageLayout from '@/components/PageLayout.vue';
import Overlay from '@/components/Overlay.vue';

defineProps({
  line: String,
  destination: String,
  timer: String,
  linetextcolor: String,
  linecolor: String,
  stopName: String,
  disruption: String,
  component: String,
})

</script>

<template>
  <PageLayout>
    <!-- <Veerpont class="main-svg" :line="line" :destination="destination" :timer="timer" /> -->
    <!-- <Bus class="main-svg" :line="line" :destination="destination" :timer="timer" :linetextcolor="linetextcolor" :linecolor="linecolor" :stopName="stopName" :disruption="disruption"/> -->
    <Veerpont v-if="component === 'ferry'" class="main-svg" :line="line" :destination="destination" :timer="timer" />
    <Bus v-else class="main-svg" :line="line" :destination="destination" :timer="timer" :linetextcolor="linetextcolor" :linecolor="linecolor" :stopName="stopName" :disruption="disruption" />
    
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