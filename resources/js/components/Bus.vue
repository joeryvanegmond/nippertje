<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
const props = defineProps({
    line: String,
    destination: String,
    timer: String,
    linetextcolor: String,
    linecolor: String,
    stopName: String,
    disruption: String,
})

const svgContainer = ref(null);
const timerDisplay = ref('');
let interval = null;
let refreshInterval = null;

function updateSvg() {
    // Elementen op ID aanpassen
    const lineEl = svgContainer.value.querySelector('#line-text tspan')
    const destinationEl = svgContainer.value.querySelector('#destination-text tspan')
    const timerEl = svgContainer.value.querySelector('#timer-text tspan')
    const stopEl = svgContainer.value.querySelector('#stop-text tspan')
    const lineColor = svgContainer.value.querySelector('#linecolor')
    const linetextcolor1 = svgContainer.value.querySelector('#linetextcolor-1')
    const linetextcolor2 = svgContainer.value.querySelector('#linetextcolor-2')
    const linetextcolor3 = svgContainer.value.querySelector('#linetextcolor-3')
    const messagetext = svgContainer.value.querySelector('#message-text tspan')
    if (linetextcolor1) linetextcolor1.style.fill = "#" + props.linetextcolor;
    if (linetextcolor2) linetextcolor2.style.fill = "#" + props.linetextcolor;
    if (linetextcolor3) linetextcolor3.style.fill = "#" + props.linetextcolor;
    if (lineColor) lineColor.style.fill = "#" + props.linecolor;
    if (stopEl) stopEl.textContent = props.stopName;
    if (lineEl) lineEl.textContent = props.line
    if (destinationEl) destinationEl.textContent = props.destination
    if (timerEl) timerEl.textContent = timerDisplay.value
    if (messagetext) {
        messagetext.textContent = props.disruption

        messagetext.style.transform = 'translateX(0px)';
    }



}

function calculateTimer() {
    if (props.timer) {
        const now = new Date()
        const today = now.toISOString().split('T')[0]
        const departure = new Date(`${today}T${props.timer}:00`)

        if (departure <= now) {
            departure.setDate(departure.getDate() + 1)
        }

        const diffMs = departure - now;

        if (diffMs <= 0) {
            timerDisplay.value = '00:00'
            router.reload({ only: ['line', 'destination', 'timer'] })
            clearInterval(interval)
            return
        }

        const minutes = Math.floor(diffMs / 1000 / 60)
        const seconds = Math.floor((diffMs / 1000) % 60)

        timerDisplay.value = `${minutes}:${String(seconds).padStart(2, '0')}`
    }
}

onMounted(async () => {
    const response = await fetch('/images/bus.svg')
    const svgText = await response.text()
    svgContainer.value.innerHTML = svgText

    const svgEl = svgContainer.value.querySelector('svg')
    if (svgEl) {
        svgEl.removeAttribute('width')
        svgEl.removeAttribute('height')
    }

    updateSvg()
    calculateTimer();
    interval = setInterval(calculateTimer, 1000);

    refreshInterval = setInterval(() => {
        router.reload({ only: ['line', 'destination', 'timer'] })
    }, 30000)
});

onUnmounted(() => {
    clearInterval(interval)
});

watch([props, timerDisplay], updateSvg)
</script>

<template>
    <div ref="svgContainer"></div>
</template>