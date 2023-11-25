<template>
    <MasterLayout title="Movies">
        <PageToolbar>
            <PageToolBarItem
                v-if="setup"
                icon="rotate"
                title="Sync Movies"
                :loading="syncLoading"
                @click="syncMovies"
            ></PageToolBarItem>
            <PageToolBarItem
                v-if="!setup"
                icon="gear"
                title="Setup Radarr"
                @click="goToSetup"
            ></PageToolBarItem>
        </PageToolbar>
        <div v-if="setup" class="flex flex-col p-4">
            <table>
                <thead>
                    <tr>
                        <th class="text-left p-2">Movie title</th>
                        <th class="text-left p-2">Year</th>
                        <th class="text-left p-2">Studio</th>
                        <th class="text-left p-2">Quality</th>
                        <th class="text-left p-2">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="movie in items" :key="movie.id">
                        <td class="p-2 border-t">{{ movie.title }}</td>
                        <td class="p-2 border-t">{{ movie.year }}</td>
                        <td class="p-2 border-t">{{ movie.studio }}</td>
                        <td class="p-2 border-t">{{ movie.quality }}p</td>
                        <td class="p-2 border-t">
                            <div
                                class="w-fit bg-gray-400 text-white text-xs px-4 rounded"
                                :class="statusColor(movie.status)"
                            >
                                {{ statusName(movie.status) }}
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
import { router, usePage } from "@inertiajs/vue3";
import axios from "axios";

const props = defineProps({
    setup: {
        type: Boolean,
        default: false,
    },
    movies: {
        type: Object,
        required: true,
    },
});

const page = usePage();

const bottom = ref(null);
const { start, items, nextPageUrl } = useInfiniteScrolling(props.movies);

const syncLoading = ref(false);

const syncMovies = async () => {
    if (syncLoading.value) {
        return;
    }

    syncLoading.value = true;
    const start = performance.now();

    try {
        const { data: { success = false } = {} } = await axios.post(
            route("movies.sync"),
        );
        if (!success) {
            return;
        }

        router.reload({
            only: ["movies"],
            onSuccess: () => {
                items.value = props.movies.data;
                nextPageUrl.value = props.movies.next_page_url;

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
    router.get(route("settings.radarr"));
};

const statusName = (status) => {
    return page.props.enums.VideoStatus[status] ?? "-";
};

const statusColor = (status) => {
    if (status === "QUEUED_ANALYSING") {
        return "bg-indigo-400";
    }
    if (status === "QUEUED_ENCODING") {
        return "bg-purple-400";
    }
    if (status === "NOT_PLAYABLE_NOT_ENCODED") {
        return "bg-amber-500";
    }
    if (status === "NOT_PLAYABLE_ENCODED") {
        return "bg-red-500";
    }
    if (status === "PLAYABLE_NOT_ENCODED") {
        return "bg-blue-400";
    }
    if (status === "PLAYABLE_ENCODED") {
        return "bg-green-400";
    }

    return "";
};

onMounted(() => {
    if (props.setup) {
        start(bottom);
    }
});
</script>
