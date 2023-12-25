<template>
    <MasterLayout title="Welcome">
        <PageToolbar v-if="type !== 'queue'">
            <PageToolBarItem
                icon="rotate"
                title="Refresh"
                :loading="refreshing"
                @click="refresh"
            ></PageToolBarItem>
        </PageToolbar>
        <div class="flex flex-col p-4">
            <table>
                <thead>
                    <tr>
                        <th class="text-left p-2">Status</th>
                        <th v-if="type === 'queue'" class="text-left p-2">
                            Progress
                        </th>
                        <th class="text-left p-2">Runtime</th>
                        <th class="text-left p-2">Movie / Episode</th>
                        <th class="text-left p-2">File</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="encode in items" :key="encode.id">
                        <td class="p-2 border-t">
                            <div
                                class="w-fit text-white text-xs px-4 rounded"
                                :class="statusColor(encode.status)"
                            >
                                {{ statusName(encode.status) }}
                            </div>
                        </td>
                        <td v-if="type === 'queue'" class="p-2 border-t">
                            {{ encode.progress / 100 }}%
                        </td>
                        <td class="p-2 border-t">
                            {{ encode.runtime || "-" }}
                        </td>
                        <td class="p-2 border-t">
                            <template v-if="encode?.videofile?.movie">
                                {{ encode.videofile.movie.title }}
                                ({{ encode.videofile.movie.year }})
                            </template>
                            <template v-else-if="encode?.videofile?.episode">
                                {{ encode.videofile.episode.serie.title }}
                                - {{ encode.videofile.episode.title }}
                            </template>
                            <template v-else> - </template>
                        </td>
                        <td class="p-2 border-t">
                            {{ encode.videofile.path }}
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
import { onBeforeUnmount, onMounted, ref } from "vue";
import { useInfiniteScrolling } from "@/Composables/infinite";
import { router, usePage } from "@inertiajs/vue3";

const props = defineProps({
    type: {
        type: String,
        required: true,
    },
    encodes: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const bottom = ref(null);
const refreshing = ref(false);

const { start, items, nextPageUrl } = useInfiniteScrolling(props.encodes);

const refresh = async () => {
    if (refreshing.value) {
        return;
    }
    refreshing.value = true;
    const start = performance.now();
    await reload();
    const time = Math.round(performance.now() - start);

    if (time < 200) {
        setTimeout(() => {
            refreshing.value = false;
        }, 200 - time);
    } else {
        refreshing.value = false;
    }
};

const reload = () => {
    return new Promise((resolve) => {
        try {
            router.reload({
                only: ["encodes"],
                onSuccess: () => {
                    items.value = props.encodes.data;
                    nextPageUrl.value = props.encodes.next_page_url;
                    resolve();
                },
            });
        } catch (error) {
            resolve();
            return;
        }
    });
};

const statusName = (status) => {
    return page.props.enums.EncodeStatus[status] ?? "-";
};

const statusColor = (status) => {
    if (status === "WAITING") {
        return "bg-purple-400";
    }
    if (status === "TRANSCODING") {
        return "bg-amber-500";
    }
    if (status === "FINISHED") {
        return "bg-green-400";
    }
    if (status === "FAILED") {
        return "bg-red-500";
    }

    return "bg-gray-400";
};

let interval = null;
onMounted(() => {
    start(bottom);
    if (props.type === "queue") {
        interval = setInterval(() => {
            reload();
        }, 1000);
    }
});
onBeforeUnmount(() => {
    if (interval) {
        clearInterval(interval);
    }
});
</script>
