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
        <div v-if="setup" class="flex flex-col p-4">
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
                        <td class="p-2 border-t">{{ movie.quality }}</td>
                        <td class="p-2 border-t">{{ movie.status }}</td>
                    </tr>
                </tbody>
            </table>
            <div ref="bottom"></div>
        </div>
        <!-- <div class="flex bg-black h-16">
        </div> -->
    </MasterLayout>
</template>

<script setup>
import MasterLayout from "@/Layouts/MasterLayout.vue";
import PageToolbar from "@/Components/PageToolbar.vue";
import PageToolBarItem from "@/Components/PageToolBarItem.vue";
import { onMounted, ref } from "vue";
import axios from "axios";

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

const bottom = ref(null)
const items = ref(props.movies.data)
const nextPageUrl = ref(props.movies.next_page_url)

const loadNextPage = async () => {
    if (! props.movies.next_page_url) {
        return;
    }

    try {
        const { data: movies } = await axios.get(props.movies.next_page_url)
        items.value = [...items.value, ...movies.data]
        nextPageUrl.value = movies.next_page_url;
    } catch (error) {
        return;
    }
}


onMounted(() => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                loadNextPage();
            }
        })
    }, {
        root: document.querySelector('main'),
        rootMargin: '0px 0px 300px 0px'
    })

    observer.observe(bottom.value)
});
</script>
