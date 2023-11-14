<template>
    <Combobox v-model="selectedPerson" as="div" class="flex flex-col" nullable>
        <div class="flex items-center">
            <ComboboxLabel class="mr-2">
                <FontAwesomeIcon
                    icon="magnifying-glass"
                    class="text-white w-4 h-4"
                />
            </ComboboxLabel>
            <ComboboxInput
                placeholder="Search"
                class="w-40 sm:w-52 transition duration-300 ease-out bg-transparent text-sm p-0 text-white placeholder-white caret-white outline-none border-0 border-b border-white focus:ring-0 focus:placeholder-transparent focus:border-b-transparent"
                :display-value="(person) => person?.name"
                @change="query = $event.target.value"
            />
        </div>
        <ComboboxOptions
            v-if="filteredPeople.length > 0"
            class="absolute top-14 bg-gray-500 rounded-b left-0 z-10 sm:left-auto sm:w-52 sm:ml-6 "
        >
            <ComboboxOption
                v-for="person in filteredPeople"
                :key="person.id"
                v-slot="{ active }"
                :value="person"
                as="template"
            >
                <li
                    class="text-white px-4 py-2 rounded-b cursor-pointer"
                    :class="{ 'bg-gray-700': active }"
                >
                    {{ person.name }}
                </li>
            </ComboboxOption>
        </ComboboxOptions>
    </Combobox>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import {
    Combobox,
    ComboboxInput,
    ComboboxOptions,
    ComboboxOption,
    ComboboxLabel,
} from "@headlessui/vue";

const people = [
    { id: 1, name: "Lorem" },
    { id: 2, name: "Impsum" },
    { id: 3, name: "Dolar" },
    { id: 4, name: "Sit" },
    { id: 5, name: "Amit" },
];

const selectedPerson = ref();
const query = ref();

const filteredPeople = computed(() => {
    if (!query.value) {
        return people;
    }

    return people.filter((person) => {
        return person.name.toLowerCase().includes(query.value.toLowerCase());
    });
});

watch(selectedPerson, async (newValue) => {
    if (newValue) {
        window.location = "/";
    }
});
</script>
