<template>
    <Menu>
        <MenuButton
            class="pr-5 py-5 pl-2 cursor-pointer text-white hover:text-green-500 outline-none"
        >
            <FontAwesomeIcon icon="user" class="w-4 h-4" />
        </MenuButton>
        <MenuItems
            class="absolute top-14 flex flex-col bg-gray-500 rounded-b outline-none w-full left-0 sm:left-auto sm:w-52"
        >
            <MenuItem v-slot="{ active }">
                <a
                    href="javascript:alert('todo');"
                    class="flex flex-1 items-center px-4 py-2 text-white cursor-pointer rounded-b"
                    :class="{ 'bg-gray-600': active }"
                >
                    <FontAwesomeIcon icon="rotate" class="w-4 h-4 mr-2" />
                    Restart
                </a>
            </MenuItem>
            <MenuItem v-slot="{ active }">
                <a
                    href="javascript:alert('todo');"
                    class="flex flex-1 items-center px-4 py-2 text-white cursor-pointer rounded-b"
                    :class="{ 'bg-gray-600': active }"
                >
                    <FontAwesomeIcon
                        icon="power-off"
                        class="w-4 h-4 mr-2 text-red-500"
                    />
                    Shutdown
                </a>
            </MenuItem>
            <MenuItem v-slot="{ active }">
                <a
                    href="javascript:alert('todo');"
                    class="flex flex-1 items-center px-4 py-2 text-white cursor-pointer rounded-b"
                    :class="{ 'bg-gray-600': active }"
                >
                    <FontAwesomeIcon
                        icon="right-from-bracket"
                        class="w-4 h-4 mr-2"
                    />
                    Logout
                </a>
            </MenuItem>
        </MenuItems>
    </Menu>
</template>

<script setup>
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { Menu, MenuButton, MenuItems, MenuItem } from "@headlessui/vue";
</script>
