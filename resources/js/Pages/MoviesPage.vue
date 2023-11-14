<template>
    <MasterLayout title="Movies">
        <PageToolbar>
            <PageToolBarItem
                v-if="setup"
                icon="rotate"
                title="Sync Movies"
            ></PageToolBarItem>
            <PageToolBarItem
                v-if="!setup"
                icon="gear"
                title="Setup Radarr"
            ></PageToolBarItem>
        </PageToolbar>
        <div v-if="setup" class="flex flex-col p-4 ">
            <!-- <div class="flex flex-1 justify-around">
                <div class="flex-1 p-2">Movie title</div>
                <div class="flex-1 p-2">Year</div>
                <div class="flex-1 p-2">Studio</div>
                <div class="flex-1 p-2">Quality</div>
                <div class="flex-1 p-2">Status</div>
            </div>
            <div v-for="movie in movies" :key="movie.id" class="flex flex-1 justify-around">
                <div class="flex-1 p-2 border-t">{{ movie.title }}</div>
                <div class="flex-1 p-2 border-t">{{ movie.year }}</div>
                <div class="flex-1 p-2 border-t">{{ movie.studio }}</div>
                <div class="flex-1 p-2 border-t">{{ movie.quality }}</div>
                <div class="flex-1 p-2 border-t">{{ movie.status }}</div>
            </div> -->
            <table class="h-full">
                <thead class="block">
                    <tr>
                        <th class="text-left p-2">Movie title</th>
                        <th class="text-left p-2">Year</th>
                        <th class="text-left p-2">Studio</th>
                        <th class="text-left p-2">Quality</th>
                        <th class="text-left p-2">Status</th>
                    </tr>
                </thead>
                <tbody class="block overflow-auto">
                    <tr v-for="movie in movies" :key="movie.id">
                        <td class="p-2 border-t">{{ movie.title }}</td>
                        <td class="p-2 border-t">{{ movie.year }}</td>
                        <td class="p-2 border-t">{{ movie.studio }}</td>
                        <td class="p-2 border-t">{{ movie.quality }}</td>
                        <td class="p-2 border-t">{{ movie.status }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- <div class="flex bg-black h-16">
        </div> -->
    </MasterLayout>
</template>

<script setup>
import MasterLayout from "@/Layouts/MasterLayout.vue";
import PageToolbar from "@/Components/PageToolbar.vue";
import PageToolBarItem from "@/Components/PageToolBarItem.vue";

const props = defineProps({
    setup: {
        type: Boolean,
        default:  false
    },
    movies: {
        type: Object,
        required: true
    },
})

console.log(props.movies)
</script>
