<template>
    <MasterLayout title="Events">
        <PageToolbar>
            <PageToolBarItem
                icon="rotate"
                title="Refresh"
            ></PageToolBarItem>
            <PageToolBarItem
                icon="trash-alt"
                title="Clear"
            ></PageToolBarItem>
        </PageToolbar>
        <div class="flex flex-col p-4">
            <table>
                <thead>
                    <tr>
                        <th class="text-left p-2">Date</th>
                        <th class="text-left p-2">Time</th>
                        <th class="text-left p-2">Component</th>
                        <th class="text-left p-2">Message</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="event in items" :key="event.id">
                        <td class="p-2 border-t">{{ event.date }}</td>
                        <td class="p-2 border-t">{{ event.time }}</td>
                        <td class="p-2 border-t">{{ event.type }}</td>
                        <td class="p-2 border-t">{{ event.firstline }}</td>
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
import { onMounted, ref } from "vue";
import { useInfiniteScrolling } from "../../Composables/infinite";

const props = defineProps({
    events: {
        type: Object,
        required: true
    },
})

const bottom = ref(null)
const { start, items } = useInfiniteScrolling(props.events);

onMounted(() => {
    start(bottom);
});
</script>
