<script setup>
import {
    Command, CommandInput, CommandList,
    CommandItem, CommandEmpty, CommandGroup, CommandSeparator,
} from '@/components/ui/command'
import { ref, computed, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

const page = usePage()
const query = ref('')
const selectedLine = ref(null)
const selectedStop = ref(null)
const selectedDirection = ref(null)

const lines = computed(() => page.props.results?.filter(r => r.type === 'line') ?? [])
const stops = computed(() => page.props.results?.filter(r => r.type === 'stop') ?? [])
const lineStops = computed(() => page.props.lineStops ?? [])
const stopDepartures = computed(() => page.props.stopDepartures ?? [])

// unieke richtingen van de geselecteerde halte
const directions = computed(() => {
    return [...new Set(stopDepartures.value.map(d => d.destination))]
})

// gefilterde lijnen op basis van gekozen richting
const departuresForDirection = computed(() => {
    if (!selectedDirection.value) return []
    return stopDepartures.value.filter(d => d.destination === selectedDirection.value)
})

function onInput(val) {
    query.value = val
    clearTimeout(window._searchTimeout)

    // Annuleer eventuele lopende Inertia request
    if (window._searchVisit) {
        window._searchVisit.cancel()
    }

    window._searchVisit = router.get(route('search'), { q: val }, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => { window._searchVisit = null }
    })
}
function select(result) {
    selectedStop.value = result
    router.get(route('search'), { stopCode: result.code }, {
        preserveState: true,
        preserveScroll: true,
    })
}

function selectLine(line) {
    selectedLine.value = line
    router.get(route('search'), { lineCode: line.code }, {
        preserveState: true,
        preserveScroll: true,
    })
}

function selectLineStop(stop) {
    const line = selectedLine.value
    selectedLine.value = null
    router.get('/', {
        stopCode: stop.code,
        lineName: line?.name
    })
}

function selectDirection(direction) {
    selectedDirection.value = direction
}

function selectDeparture(departure) {
    const stop = selectedStop.value
    selectedStop.value = null
    selectedDirection.value = null
    router.get('/', {
        stopCode: stop.code,
        lineName: departure.line,
        destination: departure.destination
    })
}

function back() {
    if (selectedDirection.value) {
        selectedDirection.value = null
        return
    }
    selectedLine.value = null
    selectedStop.value = null
    router.get(route('search'), { q: query.value }, {
        preserveState: true,
        preserveScroll: true,
    })
}
</script>

<template>
    <div class="position-fixed top-0 start-0 pt-5 w-100 h-100">
        <div class="row d-flex justify-content-center flex-column">
            <div class="col text-center text-secondary">
                <h1 style="font-family: 'AlfaSlabOne'; letter-spacing:2px;">OP HET NIPPERTJE!</h1>
            </div>
            <div class="col-12 d-flex justify-content-center">
                <div class="col-10 col-sm-5 col-md-6 col-lg-4 col-xl-4">
                    <Command>

                        <!-- Stap 2a: halte kiezen voor een lijn -->
                        <template v-if="selectedLine">
                            <div class="flex items-center gap-2 px-3 py-2 text-sm text-muted-foreground border-b cursor-pointer hover:text-foreground"
                                @click="back">
                                ← {{ selectedLine.name }} — kies een halte
                            </div>
                            <CommandList>
                                <CommandEmpty>Geen haltes gevonden.</CommandEmpty>
                                <CommandGroup heading="Haltes">
                                    <CommandItem v-for="stop in lineStops" :key="stop.code" :value="stop.code"
                                        @select="selectLineStop(stop)">
                                    </CommandItem>
                                </CommandGroup>
                            </CommandList>
                        </template>

                        <!-- Stap 2b: richting kiezen voor een halte -->
                        <template v-else-if="selectedStop && !selectedDirection">
                            <div class="flex items-center gap-2 px-3 py-2 text-sm text-muted-foreground border-b cursor-pointer hover:text-foreground"
                                @click="back">
                                ← {{ selectedStop.name }} — kies een richting
                            </div>
                            <CommandList>
                                <CommandEmpty>Geen richtingen gevonden.</CommandEmpty>
                                <CommandGroup heading="Richting">
                                    <CommandItem v-for="direction in directions" :key="direction" :value="direction"
                                        @select="selectDirection(direction)">
                                        <span class="font-medium">{{ direction }}</span>
                                    </CommandItem>
                                </CommandGroup>
                            </CommandList>
                        </template>

                        <!-- Stap 3: lijn kiezen voor richting -->
                        <template v-else-if="selectedStop && selectedDirection">
                            <div class="flex items-center gap-2 px-3 py-2 text-sm text-muted-foreground border-b cursor-pointer hover:text-foreground"
                                @click="back">
                                ← {{ selectedDirection }} — kies een lijn
                            </div>
                            <CommandList>
                                <CommandEmpty>Geen lijnen gevonden.</CommandEmpty>
                                <CommandGroup heading="Lijnen">
                                    <CommandItem v-for="departure in departuresForDirection" :key="departure.line"
                                        :value="departure.line" @select="selectDeparture(departure)">
                                        <span class="text-xs text-muted-foreground w-10">{{ departure.line }}</span>
                                        <span class="font-medium">{{ departure.destination }}</span>
                                        <span class="ml-auto text-xs text-muted-foreground">{{ departure.expected
                                            }}</span>
                                    </CommandItem>
                                </CommandGroup>
                            </CommandList>
                        </template>

                        <!-- Stap 1: zoeken -->
                        <template v-else>
                            <CommandInput :model-value="query" @update:model-value="onInput"
                                placeholder="Zoek op lijnnummer of halte" />
                            <CommandList>
                                <CommandEmpty>Geen resultaten.</CommandEmpty>

                                <CommandGroup v-if="lines.length" heading="Lijnen">
                                    <CommandItem v-for="result in lines" :key="result.code" :value="result.code"
                                        @select="selectLine(result)">
                                        <span class="text-xs text-muted-foreground w-10">{{ result.category }}</span>
                                        <span class="font-medium">{{ result.name }}</span>
                                        <span class="ml-auto text-xs text-muted-foreground">{{ result.place }}</span>
                                    </CommandItem>
                                </CommandGroup>

                                <CommandSeparator v-if="lines.length && stops.length" />

                                <CommandGroup v-if="stops.length" heading="Haltes">
                                    <CommandItem v-for="result in stops" :key="result.code" :value="result.code"
                                        @select="select(result)">
                                        <span class="text-xs text-muted-foreground w-10">{{ result.category }}</span>
                                        <span class="font-medium">{{ result.name }}</span>
                                        <span class="ml-auto text-xs text-muted-foreground">{{ result.place }}</span>
                                    </CommandItem>
                                </CommandGroup>
                            </CommandList>
                        </template>

                    </Command>
                </div>
            </div>
        </div>
    </div>
</template>