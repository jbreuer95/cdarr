<template>
    <Menu>
        <MenuButton
            class="pr-5 py-5 pl-2 cursor-pointer text-white hover:text-green-600 outline-none"
        >
            <UserIcon class="w-4 h-4 fill-current" />
        </MenuButton>
        <MenuItems
            class="absolute top-14 flex flex-col bg-gray-500 rounded-b outline-none w-full left-0 sm:left-auto sm:w-52"
        >
            <MenuItem v-slot="{ active }">
                <a
                    href="javascript:alert('todo');"
                    class="flex flex-1 items-center px-4 py-2 text-white cursor-pointer rounded-b"
                    :class="{ 'bg-gray-400': active }"
                >
                    <ArrowPathIcon
                        class="w-4 h-4 mr-2 fill-white stroke-white"
                    />
                    Restart
                </a>
            </MenuItem>
            <MenuItem v-slot="{ active }">
                <a
                    href="javascript:alert('todo');"
                    class="flex flex-1 items-center px-4 py-2 text-white cursor-pointer rounded-b"
                    :class="{ 'bg-gray-400': active }"
                >
                    <PowerIcon
                        class="w-4 h-4 mr-2 fill-red-600 stroke-red-600"
                    />

                    Shutdown
                </a>
            </MenuItem>
            <MenuItem v-slot="{ active }">
                <a
                    href="javascript:alert('todo');"
                    class="flex flex-1 items-center px-4 py-2 text-white cursor-pointer rounded-b"
                    :class="{ 'bg-gray-400': active }"
                >
                    <ArrowLeftOnRectangleIcon
                        class="w-4 h-4 mr-2 fill-white stroke-white"
                    />

                    Logout
                </a>
            </MenuItem>
        </MenuItems>
    </Menu>
</template>

<script setup>
import { Menu, MenuButton, MenuItems, MenuItem } from "@headlessui/vue";
import {
    UserIcon,
    PowerIcon,
    ArrowLeftOnRectangleIcon,
    ArrowPathIcon,
} from "@heroicons/vue/24/solid";
</script>
