<template>
    <MasterLayout title="Events">
        <PageToolbar>
            <PageToolBarItem
                icon="rotate"
                title="Refresh"
                :loading="refreshing"
                @click="refresh"
            ></PageToolBarItem>
            <PageToolBarItem
                icon="trash-alt"
                title="Clear"
                :loading="clearing"
                @click="clear"
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
                    <tr
                        v-for="event in items"
                        :key="event.id"
                        class="cursor-pointer hover:bg-white"
                        @click="() => loadMessage(event.id)"
                    >
                        <td class="p-2 border-t">{{ event.date }}</td>
                        <td class="p-2 border-t">{{ event.time }}</td>
                        <td class="p-2 border-t">{{ event.type }}</td>
                        <td class="p-2 border-t">{{ event.firstline }}</td>
                    </tr>
                </tbody>
            </table>
            <div ref="bottom"></div>
        </div>
        <Dialog :open="isOpen" class="relative z-50" @close="setIsOpen">
            <div class="fixed inset-0 bg-black/60" aria-hidden="true" />
            <div class="fixed inset-0 w-screen overflow-y-auto">
                <div class="flex justify-center p-4">
                    <DialogPanel class="bg-white w-full max-w-screen-lg">
                        <DialogTitle
                            class="pl-8 pr-5 py-4 text-lg flex justify-between border-b border-solid border-neural-200"
                        >
                            Details
                            <div
                                class="cursor-pointer hover:text-green-400 outline-none"
                                @click.stop="setIsOpen(false)"
                            >
                                <FontAwesomeIcon icon="xmark" class="w-5 h-5" />
                            </div>
                        </DialogTitle>

                        <DialogDescription class="p-8 pt-3">
                            <p class="xs">Message</p>
                            <div
                                class="border border-solid border-neural-200 rounded p-3 font-mono"
                            >
                                <div v-if="message" v-html="message"></div>
                                <FontAwesomeIcon
                                    v-else
                                    icon="gear"
                                    class="w-5 h-5"
                                    spin
                                />
                            </div>
                        </DialogDescription>
                    </DialogPanel>
                </div>
            </div>
        </Dialog>
    </MasterLayout>
</template>

<script setup>
import MasterLayout from "@/Layouts/MasterLayout.vue";
import PageToolbar from "@/Components/PageToolbar.vue";
import PageToolBarItem from "@/Components/PageToolBarItem.vue";
import { onMounted, ref } from "vue";
import { useInfiniteScrolling } from "../../Composables/infinite";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";

import {
    Dialog,
    DialogPanel,
    DialogTitle,
    DialogDescription,
} from "@headlessui/vue";
import axios from "axios";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    events: {
        type: Object,
        required: true,
    },
});

const isOpen = ref(false);
const message = ref("");
const bottom = ref(null);
const refreshing = ref(false);
const clearing = ref(false);

const { start, items, nextPageUrl } = useInfiniteScrolling(props.events);

function setIsOpen(value) {
    isOpen.value = value;
}

const loadMessage = async (id) => {
    message.value = "";
    isOpen.value = true;
    try {
        const { data: event } = await axios.get(route("events.show", id));
        message.value = event.html;
    } catch (error) {
        return;
    }
};

const refresh = async () => {
    if (refreshing.value) {
        return;
    }
    refreshing.value = true;

    try {
        router.reload({
            only: ["events"],
            onSuccess: () => {
                refreshing.value = false;
                items.value = props.events.data;
                nextPageUrl.value = props.events.next_page_url;
            },
        });
    } catch (error) {
        return;
    }
};

const clear = async () => {
    if (clearing.value) {
        return;
    }

    clearing.value = true;

    try {
        const { data: { success = false } = {} } = await axios.post(
            route("events.clear"),
        );
        if (!success) {
            return;
        }

        clearing.value = false;
        refresh();
    } catch (error) {
        return;
    }
};

onMounted(() => {
    start(bottom);
});
</script>
