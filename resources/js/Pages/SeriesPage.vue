<template>
    <MasterLayout title="Series">
        <PageToolbar>
            <PageToolBarItem
                v-if="setup"
                icon="rotate"
                title="Sync Series"
                :loading="syncLoading"
                @click="syncSeries"
            ></PageToolBarItem>
            <PageToolBarItem
                v-if="!setup"
                icon="gear"
                title="Setup Sonarr"
                @click="goToSetup"
            ></PageToolBarItem>
        </PageToolbar>
        <div v-if="setup" class="flex flex-col p-4">
            <table>
                <thead>
                    <tr>
                        <th class="text-left p-2">Serie title</th>
                        <th class="text-left p-2">Year</th>
                        <th class="text-left p-2">Network</th>
                        <th class="text-left p-2">Episodes playable</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="serie in items" :key="serie.id">
                        <td class="p-2 border-t">{{ serie.title }}</td>
                        <td class="p-2 border-t">{{ serie.year }}</td>
                        <td class="p-2 border-t">{{ serie.network }}</td>
                        <td class="p-2 border-t">
                            <div
                                class="min-w-[125px] w-fit text-center text-white text-xs px-4 rounded"
                                :class="statusColor(serie)"
                            >
                                {{ serie.episodePlayableCount }} /
                                {{ serie.episodeCount }}
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div ref="bottom"></div>
        </div>
    </MasterLayout>
</template>

<script setup>
import MasterLayout from "@/Layouts/MasterLayout.vue";
import PageToolbar from "@/Components/PageToolbar.vue";
import PageToolBarItem from "@/Components/PageToolBarItem.vue";
import { useInfiniteScrolling } from "@/Composables/infinite";
import { onMounted, ref } from "vue";
import { router } from "@inertiajs/vue3";
import axios from "axios";

const props = defineProps({
    setup: {
        type: Boolean,
        default: false,
    },
    series: {
        type: Object,
        required: true,
    },
});

const bottom = ref(null);
const { start, items, nextPageUrl } = useInfiniteScrolling(props.series);

const syncLoading = ref(false);

const syncSeries = async () => {
    if (syncLoading.value) {
        return;
    }

    syncLoading.value = true;
    const start = performance.now();

    try {
        const { data: { success = false } = {} } = await axios.post(
            route("series.sync"),
        );
        if (!success) {
            return;
        }

        router.reload({
            only: ["series"],
            onSuccess: () => {
                items.value = props.series.data;
                nextPageUrl.value = props.series.next_page_url;

                const time = Math.round(performance.now() - start);
                if (time < 200) {
                    setTimeout(() => {
                        syncLoading.value = false;
                    }, 200 - time);
                } else {
                    syncLoading.value = false;
                }
            },
        });
    } catch (error) {
        return;
    }
};

const goToSetup = () => {
    router.get(route("settings.sonarr"));
};

const statusColor = (serie) => {
    if (serie.episodePlayableCount === serie.episodeCount) {
        return "bg-green-400";
    }

    return "bg-amber-500";
};

onMounted(() => {
    if (props.setup) {
        start(bottom);
    }
});
</script>
